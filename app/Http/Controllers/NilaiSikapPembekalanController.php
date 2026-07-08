<?php

namespace App\Http\Controllers;

use App\Models\NilaiSikapPembekalan;
use App\Models\KelompokBimbingan;
use App\Models\Materi;
use App\Models\Pembimbing;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;

class NilaiSikapPembekalanController extends Controller
{
    public function pageIndex(Request $request)
    {
        return redirect()->route('pembekalan.sikap.riwayat');
    }

    public function pageInput(Request $request)
    {
        return $this->renderPage($request, 'input');
    }

    public function pageRiwayat(Request $request)
    {
        return $this->renderPage($request, 'riwayat');
    }

    public function pageInputStudents(Request $request)
    {
        $pembimbingAuthId = $this->getAuthorizedPembimbingForSikapPage();

        $validated = $request->validate([
            'kelompok_id' => ['required', 'exists:kelompok_bimbingan,id'],
            'tanggal_penilaian' => ['required', 'date'],
        ]);

        $selectedKelompokQuery = KelompokBimbingan::with(['pembimbing', 'siswa.kelas'])
            ->whereKey($validated['kelompok_id']);

        if (!empty($pembimbingAuthId)) {
            $selectedKelompokQuery->where('pembimbing_id', $pembimbingAuthId);
        }

        $selectedKelompok = $selectedKelompokQuery->first();

        if (!$selectedKelompok) {
            return response()->json([
                'message' => 'Kelompok tidak ditemukan atau tidak dapat diakses.',
            ], 404);
        }

        $studentIds = $selectedKelompok->siswa->pluck('id')->all();

        $existingSikap = NilaiSikapPembekalan::query()
            ->whereDate('tanggal_penilaian', $validated['tanggal_penilaian'])
            ->whereIn('siswa_id', $studentIds)
            ->get()
            ->keyBy('siswa_id');

        $students = $selectedKelompok->siswa
            ->sortBy('nama_siswa')
            ->values()
            ->map(function ($siswa) use ($existingSikap) {
                $sikap = $existingSikap->get($siswa->id);

                return [
                    'siswa_id' => (int) $siswa->id,
                    'nama_siswa' => (string) $siswa->nama_siswa,
                    'kelas' => $siswa->kelas ? (string) $siswa->kelas->nama_kelas : null,
                    'nilai_sikap' => (string) ($sikap->nilai_sikap ?? 'sangat_baik'),
                    'catatan' => (string) ($sikap->catatan ?? ''),
                ];
            })
            ->values();

        return response()->json([
            'kelompok' => [
                'id' => (int) $selectedKelompok->id,
                'nama_kelompok' => (string) $selectedKelompok->nama_kelompok,
                'pembimbing' => $selectedKelompok->pembimbing
                    ? (string) $selectedKelompok->pembimbing->nama_pembimbing
                    : null,
            ],
            'tanggal_penilaian' => (string) $validated['tanggal_penilaian'],
            'students' => $students,
        ]);
    }

    private function renderPage(Request $request, string $pageMode)
    {
        $authUser = auth()->user();
        $isPanitia = $authUser && Gate::forUser($authUser)->allows('panitia');
        $isPembimbing = $authUser && (Gate::forUser($authUser)->allows('pembimbing') || $authUser->role === 'pembimbing');
        $canManageSikap = $isPanitia || $isPembimbing;
        $showInputSection = $pageMode === 'input';
        $showRiwayatSection = $pageMode === 'riwayat';
        $pembimbingAuthId = null;

        if ($isPembimbing) {
            $pembimbingAuthId = Pembimbing::query()
                ->where('nip_pembimbing', (string) $authUser->username)
                ->value('id');
        }

        $filters = [
            'tanggal_awal' => $request->get('tanggal_awal'),
            'tanggal_akhir' => $request->get('tanggal_akhir'),
            'pembimbing_id' => $request->get('pembimbing_id'),
            'kelompok_id' => $request->get('kelompok_id'),
            'materi_id' => $request->get('materi_id'),
            'nilai_sikap' => $request->get('nilai_sikap'),
            'keyword' => $request->get('keyword'),
        ];

        $bulkInput = [
            'kelompok_id' => $request->get('kelompok_id_input'),
            'tanggal_penilaian' => $request->get('tanggal_penilaian_input', now()->toDateString()),
            'materi_id' => $request->get('materi_id_input'),
        ];

        if ($isPembimbing && !empty($pembimbingAuthId)) {
            $filters['pembimbing_id'] = (string) $pembimbingAuthId;
        }

        $query = NilaiSikapPembekalan::with(['pembimbing', 'materi', 'siswa.kelas', 'siswa.kelompokBimbingan'])
            ->latest('tanggal_penilaian')
            ->latest('id');

        if ($isPembimbing) {
            if (empty($pembimbingAuthId)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('pembimbing_id', $pembimbingAuthId);
            }
        }

        if (!empty($filters['tanggal_awal'])) {
            $query->whereDate('tanggal_penilaian', '>=', $filters['tanggal_awal']);
        }

        if (!empty($filters['tanggal_akhir'])) {
            $query->whereDate('tanggal_penilaian', '<=', $filters['tanggal_akhir']);
        }

        if (!empty($filters['pembimbing_id'])) {
            $query->where('pembimbing_id', $filters['pembimbing_id']);
        }

        if (!empty($filters['kelompok_id'])) {
            $query->whereHas('siswa.kelompokBimbingan', function ($q) use ($filters) {
                $q->where('kelompok_bimbingan.id', $filters['kelompok_id']);
            });
        }

        if (!empty($filters['materi_id'])) {
            $query->where('materi_id', $filters['materi_id']);
        }

        if (!empty($filters['nilai_sikap'])) {
            $query->where('nilai_sikap', $filters['nilai_sikap']);
        }

        if (!empty($filters['keyword'])) {
            $keyword = trim((string) $filters['keyword']);
            $query->where(function ($q) use ($keyword) {
                $q->where('catatan', 'like', '%' . $keyword . '%')
                    ->orWhereHas('siswa', function ($sq) use ($keyword) {
                        $sq->where('nama_siswa', 'like', '%' . $keyword . '%');
                    })
                    ->orWhereHas('pembimbing', function ($pq) use ($keyword) {
                        $pq->where('nama_pembimbing', 'like', '%' . $keyword . '%');
                    })
                    ->orWhereHas('materi', function ($mq) use ($keyword) {
                        $mq->where('topik', 'like', '%' . $keyword . '%');
                    })
                    ->orWhereHas('siswa.kelompokBimbingan', function ($kq) use ($keyword) {
                        $kq->where('nama_kelompok', 'like', '%' . $keyword . '%');
                    });
            });
        }

        $nilaiSikap = $query->get();

        $pembimbingOptionsQuery = Pembimbing::orderBy('nama_pembimbing');
        if ($isPembimbing) {
            if (empty($pembimbingAuthId)) {
                $pembimbingOptionsQuery->whereRaw('1 = 0');
            } else {
                $pembimbingOptionsQuery->whereKey($pembimbingAuthId);
            }
        }
        $pembimbingOptions = $pembimbingOptionsQuery->get(['id', 'nama_pembimbing']);

        $siswaOptionsQuery = Siswa::with(['kelas', 'kelompokBimbingan.pembimbing'])
            ->orderBy('nama_siswa');
        if ($isPembimbing) {
            if (empty($pembimbingAuthId)) {
                $siswaOptionsQuery->whereRaw('1 = 0');
            } else {
                $siswaOptionsQuery->whereHas('kelompokBimbingan', function ($q) use ($pembimbingAuthId) {
                    $q->where('kelompok_bimbingan.pembimbing_id', $pembimbingAuthId);
                });
            }
        }
        $siswaOptions = $siswaOptionsQuery->get(['id', 'nama_siswa', 'kelas_id']);

        $kelompokOptionsQuery = KelompokBimbingan::with('pembimbing')
            ->withCount('siswa')
            ->orderBy('nama_kelompok');
        if ($isPembimbing) {
            if (empty($pembimbingAuthId)) {
                $kelompokOptionsQuery->whereRaw('1 = 0');
            } else {
                $kelompokOptionsQuery->where('pembimbing_id', $pembimbingAuthId);
            }
        }
        $kelompokOptions = $kelompokOptionsQuery->get();

        $materis = Materi::orderBy('tanggal_materi', 'desc')->orderBy('id', 'desc')->get(['id', 'topik', 'tanggal_materi']);

        return view('pembekalan.sikap', compact(
            'nilaiSikap',
            'filters',
            'pembimbingOptions',
            'siswaOptions',
            'kelompokOptions',
            'materis',
            'bulkInput',
            'canManageSikap',
            'showInputSection',
            'showRiwayatSection'
        ));
    }

    public function pageBulkStore(Request $request)
    {
        $pembimbingAuthId = $this->getAuthorizedPembimbingForSikapPage();

        $validated = $request->validate([
            'kelompok_id' => ['required', 'exists:kelompok_bimbingan,id'],
            'tanggal_penilaian' => ['required', 'date'],
            'materi_id' => ['nullable', 'exists:materis,id'],
            'siswa_ids' => ['required', 'array', 'min:1'],
            'siswa_ids.*' => ['required', 'exists:siswa,id'],
            'nilai_sikap_values' => ['required', 'array'],
            'nilai_sikap_values.*' => ['required', 'in:sangat_baik,baik,cukup,perlu_bimbingan'],
            'catatans' => ['nullable', 'array'],
            'catatans.*' => ['nullable', 'string', 'max:1000'],
        ]);

        $kelompok = KelompokBimbingan::with('siswa:id')->findOrFail($validated['kelompok_id']);
        if (!empty($pembimbingAuthId) && (int) $kelompok->pembimbing_id !== (int) $pembimbingAuthId) {
            throw new UnauthorizedException('Anda tidak berwenang mengisi catatan sikap untuk kelompok ini.');
        }

        $allowedSiswaIds = $kelompok->siswa->pluck('id')->map(fn($id) => (int) $id)->all();
        $submittedSiswaIds = collect($validated['siswa_ids'])->map(fn($id) => (int) $id)->all();
        $invalidSiswaIds = array_diff($submittedSiswaIds, $allowedSiswaIds);

        if (!empty($invalidSiswaIds)) {
            throw ValidationException::withMessages([
                'siswa_ids' => 'Terdapat siswa yang bukan anggota kelompok terpilih.',
            ]);
        }

        $createdCount = 0;
        $updatedCount = 0;
        $pembimbingIdToSave = !empty($pembimbingAuthId) ? $pembimbingAuthId : (int) $kelompok->pembimbing_id;

        foreach ($submittedSiswaIds as $siswaId) {
            $nilaiSikap = $validated['nilai_sikap_values'][$siswaId] ?? null;
            if (empty($nilaiSikap)) {
                throw ValidationException::withMessages([
                    'nilai_sikap_values' => 'Nilai sikap untuk semua siswa yang dipilih wajib diisi.',
                ]);
            }

            $this->validatePembimbingForSiswa((int) $siswaId, (int) $pembimbingIdToSave);

            $nilai = NilaiSikapPembekalan::updateOrCreate(
                [
                    'siswa_id' => $siswaId,
                    'tanggal_penilaian' => $validated['tanggal_penilaian'],
                ],
                [
                    'pembimbing_id' => $pembimbingIdToSave,
                    'materi_id' => $validated['materi_id'] ?? null,
                    'nilai_sikap' => $nilaiSikap,
                    'catatan' => $validated['catatans'][$siswaId] ?? null,
                ]
            );

            if ($nilai->wasRecentlyCreated) {
                $createdCount++;
            } else {
                $updatedCount++;
            }
        }

        $message = "Catatan sikap kelompok berhasil disimpan. {$createdCount} data ditambahkan, {$updatedCount} data diperbarui.";

        return redirect()->route('pembekalan.sikap.input', [
            'kelompok_id_input' => $validated['kelompok_id'],
            'tanggal_penilaian_input' => $validated['tanggal_penilaian'],
            'materi_id_input' => $validated['materi_id'] ?? null,
        ])->with('success', $message);
    }

    public function pageStore(Request $request)
    {
        $pembimbingAuthId = $this->getAuthorizedPembimbingForSikapPage();

        $validated = $request->validate([
            'pembimbing_id' => ['required', 'exists:pembimbings,id'],
            'siswa_id' => ['required', 'exists:siswa,id'],
            'tanggal_penilaian' => ['required', 'date'],
            'nilai_sikap' => ['required', 'in:sangat_baik,baik,cukup,perlu_bimbingan'],
            'catatan' => ['nullable', 'string'],
        ]);

        if (!empty($pembimbingAuthId)) {
            $validated['pembimbing_id'] = $pembimbingAuthId;
        }

        $this->validatePembimbingForSiswa((int) $validated['siswa_id'], (int) $validated['pembimbing_id']);

        $nilai = NilaiSikapPembekalan::updateOrCreate(
            [
                'siswa_id' => $validated['siswa_id'],
                'tanggal_penilaian' => $validated['tanggal_penilaian'],
            ],
            $validated
        );

        $message = $nilai->wasRecentlyCreated
            ? 'Catatan sikap berhasil ditambahkan.'
            : 'Catatan sikap pada tanggal tersebut sudah ada, data diperbarui.';

        return redirect()->route('pembekalan.sikap.input')->with('success', $message);
    }

    public function pageUpdate(Request $request, NilaiSikapPembekalan $nilaiSikapPembekalan)
    {
        $pembimbingAuthId = $this->getAuthorizedPembimbingForSikapPage();

        $validated = $request->validate([
            'pembimbing_id' => ['required', 'exists:pembimbings,id'],
            'siswa_id' => ['required', 'exists:siswa,id'],
            'tanggal_penilaian' => ['required', 'date'],
            'nilai_sikap' => ['required', 'in:sangat_baik,baik,cukup,perlu_bimbingan'],
            'catatan' => ['nullable', 'string'],
        ]);

        if (!empty($pembimbingAuthId)) {
            if ((int) $nilaiSikapPembekalan->pembimbing_id !== (int) $pembimbingAuthId) {
                throw new UnauthorizedException('Anda tidak berwenang mengubah catatan sikap ini.');
            }
            $validated['pembimbing_id'] = $pembimbingAuthId;
        }

        $this->validatePembimbingForSiswa((int) $validated['siswa_id'], (int) $validated['pembimbing_id']);

        $existsConflict = NilaiSikapPembekalan::query()
            ->where('siswa_id', $validated['siswa_id'])
            ->whereDate('tanggal_penilaian', $validated['tanggal_penilaian'])
            ->whereKeyNot($nilaiSikapPembekalan->id)
            ->exists();

        if ($existsConflict) {
            return back()->withErrors([
                'tanggal_penilaian' => 'Siswa sudah memiliki catatan sikap pada tanggal tersebut.',
            ])->withInput();
        }

        $nilaiSikapPembekalan->update($validated);

        return redirect()->route('pembekalan.sikap.riwayat')->with('success', 'Catatan sikap berhasil diperbarui.');
    }

    public function pageDestroy(NilaiSikapPembekalan $nilaiSikapPembekalan)
    {
        $pembimbingAuthId = $this->getAuthorizedPembimbingForSikapPage();
        if (!empty($pembimbingAuthId) && (int) $nilaiSikapPembekalan->pembimbing_id !== (int) $pembimbingAuthId) {
            throw new UnauthorizedException('Anda tidak berwenang menghapus catatan sikap ini.');
        }

        $nilaiSikapPembekalan->delete();

        return redirect()->route('pembekalan.sikap.riwayat')->with('success', 'Catatan sikap berhasil dihapus.');
    }

    public function index(Request $request)
    {
        $query = NilaiSikapPembekalan::with(['pembimbing', 'siswa'])->latest('tanggal_penilaian');

        if ($request->filled('pembimbing_id')) {
            $query->where('pembimbing_id', $request->pembimbing_id);
        }

        if ($request->filled('siswa_id')) {
            $query->where('siswa_id', $request->siswa_id);
        }

        return response()->json($query->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pembimbing_id' => ['required', 'exists:pembimbings,id'],
            'siswa_id' => ['required', 'exists:siswa,id'],
            'tanggal_penilaian' => ['required', 'date'],
            'nilai_sikap' => ['required', 'in:sangat_baik,baik,cukup,perlu_bimbingan'],
            'catatan' => ['nullable', 'string'],
        ]);

        $this->validatePembimbingForSiswa((int) $validated['siswa_id'], (int) $validated['pembimbing_id']);

        $nilai = NilaiSikapPembekalan::updateOrCreate(
            [
                'siswa_id' => $validated['siswa_id'],
                'tanggal_penilaian' => $validated['tanggal_penilaian'],
            ],
            $validated
        );

        return response()->json($nilai, $nilai->wasRecentlyCreated ? 201 : 200);
    }

    public function show(NilaiSikapPembekalan $nilaiSikapPembekalan)
    {
        return response()->json($nilaiSikapPembekalan->load(['pembimbing', 'siswa']));
    }

    public function update(Request $request, NilaiSikapPembekalan $nilaiSikapPembekalan)
    {
        $validated = $request->validate([
            'pembimbing_id' => ['required', 'exists:pembimbings,id'],
            'siswa_id' => ['required', 'exists:siswa,id'],
            'tanggal_penilaian' => ['required', 'date'],
            'nilai_sikap' => ['required', 'in:sangat_baik,baik,cukup,perlu_bimbingan'],
            'catatan' => ['nullable', 'string'],
        ]);

        $this->validatePembimbingForSiswa((int) $validated['siswa_id'], (int) $validated['pembimbing_id']);

        $existsConflict = NilaiSikapPembekalan::query()
            ->where('siswa_id', $validated['siswa_id'])
            ->whereDate('tanggal_penilaian', $validated['tanggal_penilaian'])
            ->whereKeyNot($nilaiSikapPembekalan->id)
            ->exists();

        if ($existsConflict) {
            throw ValidationException::withMessages([
                'tanggal_penilaian' => 'Siswa sudah memiliki catatan sikap pada tanggal tersebut.',
            ]);
        }

        $nilaiSikapPembekalan->update($validated);

        return response()->json($nilaiSikapPembekalan);
    }

    public function destroy(NilaiSikapPembekalan $nilaiSikapPembekalan)
    {
        $nilaiSikapPembekalan->delete();

        return response()->json(['message' => 'Nilai sikap deleted']);
    }

    private function validatePembimbingForSiswa(int $siswaId, int $pembimbingId): void
    {
        $hasKelompok = Siswa::query()
            ->whereKey($siswaId)
            ->whereHas('kelompokBimbingan')
            ->exists();

        if (!$hasKelompok) {
            throw ValidationException::withMessages([
                'siswa_id' => 'Siswa belum memiliki kelompok bimbingan. Silakan atur kelompok bimbingan terlebih dahulu.',
            ]);
        }

        $isMatch = Siswa::query()
            ->whereKey($siswaId)
            ->whereHas('kelompokBimbingan', function ($query) use ($pembimbingId) {
                $query->where('kelompok_bimbingan.pembimbing_id', $pembimbingId);
            })
            ->exists();

        if (!$isMatch) {
            throw ValidationException::withMessages([
                'pembimbing_id' => 'Pembimbing yang dipilih tidak sesuai dengan kelompok bimbingan siswa.',
            ]);
        }
    }

    private function getAuthorizedPembimbingForSikapPage(): ?int
    {
        $authUser = auth()->user();
        if (!$authUser) {
            throw new UnauthorizedException('Silakan login terlebih dahulu.');
        }

        if (Gate::forUser($authUser)->allows('panitia')) {
            return null;
        }

        if (Gate::forUser($authUser)->allows('pembimbing') || $authUser->role === 'pembimbing') {
            $pembimbingAuthId = Pembimbing::query()
                ->where('nip_pembimbing', (string) $authUser->username)
                ->value('id');

            if (empty($pembimbingAuthId)) {
                throw new UnauthorizedException('Data pembimbing untuk akun ini tidak ditemukan.');
            }

            return (int) $pembimbingAuthId;
        }

        throw new UnauthorizedException('Anda tidak memiliki akses ke modul catatan sikap ini.');
    }
}
