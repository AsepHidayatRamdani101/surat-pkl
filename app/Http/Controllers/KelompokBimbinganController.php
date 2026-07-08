<?php

namespace App\Http\Controllers;

use App\Models\KelompokBimbingan;
use App\Models\Pembimbing;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class KelompokBimbinganController extends Controller
{
    public function index()
    {
        $authUser = auth()->user();
        $isPembimbing = $authUser && (Gate::forUser($authUser)->allows('pembimbing') || $authUser->role === 'pembimbing');
        $canManageKelompok = !$isPembimbing;
        $pembimbingAuthId = null;

        if ($isPembimbing) {
            $pembimbingAuthId = Pembimbing::query()
                ->where('nip_pembimbing', (string) $authUser->username)
                ->value('id');
        }

        $pembimbingQuery = Pembimbing::with('jurusan')
            ->orderByRaw("CASE WHEN jenis_guru = 'guru_produktif' THEN 0 ELSE 1 END")
            ->orderBy('nama_pembimbing');
        if ($isPembimbing) {
            if (empty($pembimbingAuthId)) {
                $pembimbingQuery->whereRaw('1 = 0');
            } else {
                $pembimbingQuery->whereKey($pembimbingAuthId);
            }
        }
        $pembimbing = $pembimbingQuery->get();

        $siswaQuery = Siswa::with('kelas.jurusan')
            ->orderBy('nama_siswa');
        if ($isPembimbing) {
            if (empty($pembimbingAuthId)) {
                $siswaQuery->whereRaw('1 = 0');
            } else {
                $siswaQuery->whereHas('kelompokBimbingan', function ($query) use ($pembimbingAuthId) {
                    $query->where('kelompok_bimbingan.pembimbing_id', $pembimbingAuthId);
                });
            }
        }
        $siswa = $siswaQuery->get();

        $kelompokQuery = KelompokBimbingan::with(['pembimbing.jurusan', 'siswa.kelas.jurusan', 'siswa.suratIzin.perusahaan'])
            ->withCount('siswa')
            ->latest();
        if ($isPembimbing) {
            if (empty($pembimbingAuthId)) {
                $kelompokQuery->whereRaw('1 = 0');
            } else {
                $kelompokQuery->where('pembimbing_id', $pembimbingAuthId);
            }
        }
        $filters = [
            'kelompok_id' => request('kelompok_id'),
            'pembimbing_id' => request('pembimbing_id'),
            'keyword' => request('keyword'),
        ];

        if (!empty($filters['kelompok_id'])) {
            $kelompokQuery->whereKey($filters['kelompok_id']);
        }

        if (!empty($filters['pembimbing_id'])) {
            $kelompokQuery->where('pembimbing_id', $filters['pembimbing_id']);
        }

        if (!empty($filters['keyword'])) {
            $keyword = trim((string) $filters['keyword']);
            $kelompokQuery->where(function ($query) use ($keyword) {
                $query->where('nama_kelompok', 'like', '%' . $keyword . '%')
                    ->orWhereHas('pembimbing', function ($pembimbingQuery) use ($keyword) {
                        $pembimbingQuery->where('nama_pembimbing', 'like', '%' . $keyword . '%');
                    })
                    ->orWhereHas('siswa', function ($siswaQuery) use ($keyword) {
                        $siswaQuery->where('nama_siswa', 'like', '%' . $keyword . '%')
                            ->orWhereHas('kelas', function ($kelasQuery) use ($keyword) {
                                $kelasQuery->where('nama_kelas', 'like', '%' . $keyword . '%');
                            });
                    });
            });
        }
        $kelompok = $kelompokQuery->get();

        return view('kelompok_bimbingan.index', compact('pembimbing', 'siswa', 'kelompok', 'canManageKelompok', 'filters'));
    }

    public function storeManual(Request $request)
    {
        $validated = $request->validate([
            'nama_kelompok' => 'required|string|max:255',
            'pembimbing_id' => 'required|exists:pembimbings,id',
            'siswa_ids' => 'required|array|min:8|max:12',
            'siswa_ids.*' => 'required|exists:siswa,id',
        ]);

        $alreadyAssigned = DB::table('kelompok_bimbingan_siswa')
            ->whereIn('siswa_id', $validated['siswa_ids'])
            ->pluck('siswa_id')
            ->all();

        if (!empty($alreadyAssigned)) {
            return back()->withInput()->with('error', 'Sebagian siswa sudah masuk kelompok lain.');
        }

        DB::transaction(function () use ($validated) {
            $kelompok = KelompokBimbingan::create([
                'nama_kelompok' => $validated['nama_kelompok'],
                'pembimbing_id' => $validated['pembimbing_id'],
                'metode' => 'manual',
                'created_by' => auth()->id(),
            ]);

            $kelompok->siswa()->sync($validated['siswa_ids']);
        });

        return redirect()->route('kelompok-bimbingan.index')->with('success', 'Kelompok manual berhasil dibuat.');
    }

    public function generateAutomatic()
    {
        $allSiswa = Siswa::with('kelas')
            ->orderBy('kelas_id')
            ->orderBy('nama_siswa')
            ->get();

        if ($allSiswa->isEmpty()) {
            return back()->with('error', 'Data siswa tidak ditemukan.');
        }

        $produktifByJurusan = Pembimbing::where('jenis_guru', 'guru_produktif')
            ->whereNotNull('jurusan_id')
            ->orderBy('nama_pembimbing')
            ->get()
            ->groupBy('jurusan_id')
            ->map(fn($items) => $items->values())
            ->all();

        $adaptif = Pembimbing::where('jenis_guru', 'adaptif_normatif')
            ->orderBy('nama_pembimbing')
            ->get()
            ->values();

        if (empty($produktifByJurusan) && $adaptif->isEmpty()) {
            return back()->with('error', 'Data pembimbing belum tersedia.');
        }

        $kuotaPembimbing = [];
        foreach ($produktifByJurusan as $items) {
            foreach ($items as $pembimbing) {
                $kuota = $this->calculatePembimbingQuota((int) $pembimbing->jumlah_jam);
                if ($kuota > 0) {
                    $kuotaPembimbing[$pembimbing->id] = $kuota;
                }
            }
        }

        foreach ($adaptif as $pembimbing) {
            $kuota = $this->calculatePembimbingQuota((int) $pembimbing->jumlah_jam);
            if ($kuota > 0) {
                $kuotaPembimbing[$pembimbing->id] = $kuota;
            }
        }

        if (empty($kuotaPembimbing)) {
            return back()->with('error', 'Kuota siswa pembimbing tidak tersedia. Pastikan jumlah jam pembimbing sudah diisi.');
        }

        $studentsByMentor = [];
        $pointerByJurusan = [];
        $unassignedStudents = [];

        $adaptifIds = $adaptif->pluck('id')->all();

        foreach ($allSiswa as $item) {
            $jurusanId = $item->kelas->jurusan_id ?? null;
            $produktifList = $jurusanId
                ? collect($produktifByJurusan[$jurusanId] ?? [])->pluck('id')->all()
                : [];

            $mentorId = $this->pickMentorWithQuota(
                $produktifList,
                'jurusan_' . (string) $jurusanId,
                $pointerByJurusan,
                $kuotaPembimbing,
                $studentsByMentor
            );

            if (!$mentorId) {
                $mentorId = $this->pickMentorWithQuota(
                    $adaptifIds,
                    'adaptif',
                    $pointerByJurusan,
                    $kuotaPembimbing,
                    $studentsByMentor
                );
            }

            if (!$mentorId) {
                $unassignedStudents[] = $item->id;
                continue;
            }

            $studentsByMentor[$mentorId][] = $item->id;
        }

        if (empty($studentsByMentor)) {
            return back()->with('error', 'Tidak ada siswa yang dapat dipetakan ke pembimbing berdasarkan kuota saat ini.');
        }

        $warningGroups = [];

        DB::transaction(function () use ($studentsByMentor, &$warningGroups) {
            KelompokBimbingan::query()->delete();

            $nomorKelompok = 1;

            foreach ($studentsByMentor as $mentorId => $siswaIds) {
                $result = $this->buildGroupChunks($siswaIds, 8, 12);

                foreach ($result['chunks'] as $chunk) {
                    $kelompok = KelompokBimbingan::create([
                        'nama_kelompok' => 'Kelompok ' . $nomorKelompok,
                        'pembimbing_id' => $mentorId,
                        'metode' => 'otomatis',
                        'created_by' => auth()->id(),
                    ]);

                    $kelompok->siswa()->sync($chunk);
                    $nomorKelompok++;
                }

                if ($result['invalid']) {
                    $warningGroups[] = $mentorId;
                }
            }
        });

        if (!empty($unassignedStudents)) {
            return redirect()->route('kelompok-bimbingan.index')->with('warning', 'Pembagian otomatis dibuat dari kuota data pembimbing, namun masih ada ' . count($unassignedStudents) . ' siswa belum teralokasi karena kuota pembimbing penuh.');
        }

        if (!empty($warningGroups)) {
            return redirect()->route('kelompok-bimbingan.index')->with('warning', 'Pembagian otomatis dibuat, namun ada kelompok yang tidak memenuhi batas 8-12 karena distribusi data tidak memungkinkan sepenuhnya.');
        }

        return redirect()->route('kelompok-bimbingan.index')->with('success', 'Pembagian kelompok otomatis berhasil dibuat dari kuota data pembimbing. Guru produktif diprioritaskan.');
    }

    public function destroy($id)
    {
        $kelompok = KelompokBimbingan::findOrFail($id);
        $kelompok->delete();

        return redirect()->route('kelompok-bimbingan.index')->with('success', 'Kelompok berhasil dihapus.');
    }

    public function reset()
    {
        KelompokBimbingan::query()->delete();

        return redirect()->route('kelompok-bimbingan.index')->with('success', 'Semua kelompok berhasil direset.');
    }

    private function buildGroupChunks(array $siswaIds, int $minSize, int $maxSize): array
    {
        $total = count($siswaIds);

        if ($total === 0) {
            return ['chunks' => [], 'invalid' => false];
        }

        $minGroups = (int) ceil($total / $maxSize);
        $maxGroups = (int) floor($total / $minSize);

        if ($maxGroups >= 1 && $minGroups <= $maxGroups) {
            $groupCount = $minGroups;
            $baseSize = (int) floor($total / $groupCount);
            $remainder = $total % $groupCount;
            $offset = 0;
            $chunks = [];

            for ($i = 0; $i < $groupCount; $i++) {
                $currentSize = $baseSize + ($i < $remainder ? 1 : 0);
                $chunks[] = array_slice($siswaIds, $offset, $currentSize);
                $offset += $currentSize;
            }

            return ['chunks' => $chunks, 'invalid' => false];
        }

        $chunks = array_chunk($siswaIds, $maxSize);

        for ($i = count($chunks) - 1; $i > 0; $i--) {
            while (count($chunks[$i]) < $minSize && count($chunks[$i - 1]) > $minSize) {
                $chunks[$i][] = array_pop($chunks[$i - 1]);
            }
        }

        $invalid = false;
        foreach ($chunks as $chunk) {
            if (count($chunk) < $minSize || count($chunk) > $maxSize) {
                $invalid = true;
                break;
            }
        }

        return ['chunks' => $chunks, 'invalid' => $invalid];
    }

    private function calculatePembimbingQuota(int $jumlahJam): int
    {
        return (int) round((36 / 44) * $jumlahJam);
    }

    private function pickMentorWithQuota(
        array $candidateMentorIds,
        string $bucketKey,
        array &$pointers,
        array $kuotaPembimbing,
        array $studentsByMentor
    ): ?int {
        if (empty($candidateMentorIds)) {
            return null;
        }

        $count = count($candidateMentorIds);
        $start = $pointers[$bucketKey] ?? 0;

        for ($step = 0; $step < $count; $step++) {
            $index = ($start + $step) % $count;
            $mentorId = $candidateMentorIds[$index];
            $kuota = (int) ($kuotaPembimbing[$mentorId] ?? 0);
            $terisi = count($studentsByMentor[$mentorId] ?? []);

            if ($kuota > $terisi) {
                $pointers[$bucketKey] = ($index + 1) % $count;
                return $mentorId;
            }
        }

        return null;
    }
}
