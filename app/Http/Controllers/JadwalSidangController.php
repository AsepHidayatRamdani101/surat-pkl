<?php

namespace App\Http\Controllers;

use App\Models\JadwalSidang;
use App\Models\Pembimbing;
use App\Models\RuanganSidang;
use App\Models\Siswa;
use App\Support\RoleRegistry;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class JadwalSidangController extends Controller
{
    public function index(Request $request)
    {
        $role = auth()->user()->role;
        abort_unless(in_array($role, ['panitia', 'pembimbing', 'siswa'], true) || RoleRegistry::has($role), 403);

        $ruangan = RuanganSidang::orderBy('nama_ruangan')->get();
        $pembimbing = $role === 'panitia'
            ? Pembimbing::orderBy('nama_pembimbing')->get(['id', 'nama_pembimbing'])
            : collect();
        $siswaList = $role === 'panitia'
            ? Siswa::with('kelas')->whereIn('id', $this->allBimbinganSiswaIds())->orderBy('nama_siswa')->get(['id', 'nama_siswa', 'kelas_id'])
            : collect();

        return view('sidang.jadwal.index', [
            'role' => $role,
            'ruangan' => $ruangan,
            'pembimbingList' => $pembimbing,
            'siswaList' => $siswaList,
            'initialJenis' => $request->input('jenis', ''),
        ]);
    }

    public function data(Request $request)
    {
        $user = auth()->user();
        abort_unless(in_array($user->role, ['panitia', 'pembimbing', 'siswa'], true) || RoleRegistry::has($user->role), 403);

        $allowedIds = $user->role === 'siswa'
            ? collect([$this->currentSiswa()->id])
            : ($user->role === 'pembimbing' ? $this->siswaBimbinganIds($this->pembimbingId()) : $this->allBimbinganSiswaIds());
        $jenisList = $request->filled('jenis') ? [$request->input('jenis')] : ['bimbingan', 'sidang'];
        $students = Siswa::with(['kelas', 'pembimbingBimbingan', 'kelompokBimbingan.pembimbing', 'kelompokBimbingan.pembimbings'])
            ->whereIn('id', $allowedIds)
            ->orderBy('kelas_id')->orderBy('nama_siswa')->get();
        // Bimbingan bisa lebih dari satu kali per siswa: setiap jadwal tampil
        // sebagai baris sendiri. Siswa tanpa jadwal tetap tampil satu baris kosong.
        $schedules = JadwalSidang::with(['pembimbing', 'ruangan'])
            ->whereIn('siswa_id', $allowedIds)
            ->whereIn('jenis', $jenisList)
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get()
            ->groupBy(fn ($item) => $item->siswa_id . ':' . $item->jenis);
        $rows = $students->flatMap(function ($siswa) use ($jenisList, $schedules) {
            return collect($jenisList)->flatMap(function ($jenis) use ($siswa, $schedules) {
                $defaultPembimbing = $siswa->kelompokBimbingan->first()?->pembimbing
                    ?? $siswa->kelompokBimbingan->first()?->pembimbings?->first()
                    ?? $siswa->pembimbingBimbingan->first();
                $list = $schedules->get($siswa->id . ':' . $jenis, collect());
                if ($list->isEmpty()) {
                    $list = collect([null]);
                }

                return $list->map(function ($jadwal) use ($siswa, $jenis, $defaultPembimbing) {
                    $pembimbing = $jadwal?->pembimbing ?? $defaultPembimbing;

                    return (object) [
                        'id' => $jadwal?->id,
                        'siswa_id' => $siswa->id,
                        'siswa' => $siswa,
                        'pembimbing' => $pembimbing,
                        'pembimbing_id' => $jadwal?->pembimbing_id ?? $pembimbing?->id,
                        'ruangan' => $jadwal?->ruangan,
                        'ruangan_sidang_id' => $jadwal?->ruangan_sidang_id,
                        'jenis' => $jenis,
                        'tanggal' => $jadwal?->tanggal,
                        'jam_mulai' => $jadwal?->jam_mulai,
                        'jam_selesai' => $jadwal?->jam_selesai,
                        'catatan' => $jadwal?->catatan,
                        'terkunci' => (bool) ($jadwal?->terkunci),
                    ];
                });
            });
        })->values();

        return DataTables::of($rows)
            ->addIndexColumn()
            ->addColumn('pilih', fn ($item) => '<input type="checkbox" class="row-check" data-id="' . ($item->id ?? '') . '" data-siswa_id="' . $item->siswa_id . '" data-terkunci="' . ($item->terkunci ? 1 : 0) . '"' . ($item->terkunci ? ' disabled' : '') . '>')
            ->addColumn('nama_siswa', fn ($item) => optional($item->siswa)->nama_siswa ?? '-')
            ->addColumn('kelas', fn ($item) => optional(optional($item->siswa)->kelas)->nama_kelas ?? '-')
            ->addColumn('nama_pembimbing', fn ($item) => optional($item->pembimbing)->nama_pembimbing ?? '-')
            ->addColumn('nama_ruangan', fn ($item) => optional($item->ruangan)->nama_ruangan ?? '-')
            ->editColumn('tanggal', fn ($item) => $item->tanggal
                ? $item->tanggal->locale('id')->translatedFormat('d F Y')
                : '-')
            ->addColumn('jam', fn ($item) => $item->jam_mulai ? substr($item->jam_mulai, 0, 5) . ' - ' . substr($item->jam_selesai, 0, 5) : '-')
            ->addColumn('status_jadwal', fn ($item) => $item->terkunci
                ? '<span class="badge badge-success">Terkunci</span>'
                : '<span class="badge badge-secondary">Terbuka</span>')
            ->addColumn('aksi', function ($item) use ($user) {
                if (!in_array($user->role, ['panitia', 'pembimbing'], true)) {
                    return '';
                }

                if ($item->terkunci) {
                    return '<span class="badge badge-success"><i class="fas fa-lock mr-1"></i>Terkunci</span>';
                }

                $hapus = $item->id
                    ? '<button type="button" class="btn btn-outline-danger btn-sm btnHapus" title="Hapus"
                            data-id="' . $item->id . '">
                            <i class="fas fa-trash"></i>
                        </button>'
                    : '';

                // Bimbingan boleh lebih dari satu kali: beri tombol tambah jadwal baru.
                $tambah = ($item->id && $item->jenis === 'bimbingan')
                    ? '<button type="button" class="btn btn-outline-success btn-sm btnAturPerOrang" title="Tambah Jadwal Bimbingan Baru"
                            data-id=""
                            data-siswa_id="' . $item->siswa_id . '"
                            data-pembimbing_id="' . ($item->pembimbing_id ?? '') . '"
                            data-jenis="bimbingan">
                            <i class="fas fa-plus"></i>
                        </button>'
                    : '';

                return '
                    <div class="btn-group btn-aksi">
                        <button type="button" class="btn btn-outline-primary btn-sm btnAturPerOrang" title="Atur Jadwal"
                            data-id="' . $item->id . '"
                            data-siswa_id="' . $item->siswa_id . '"
                            data-pembimbing_id="' . ($item->pembimbing_id ?? '') . '"
                            data-ruangan_sidang_id="' . ($item->ruangan_sidang_id ?? '') . '"
                            data-jenis="' . $item->jenis . '"
                            data-tanggal="' . optional($item->tanggal)->format('Y-m-d') . '"
                            data-jam_mulai="' . substr($item->jam_mulai, 0, 5) . '"
                            data-jam_selesai="' . substr($item->jam_selesai, 0, 5) . '"
                            data-catatan="' . htmlentities($item->catatan ?? '') . '">
                            <i class="fas fa-pen"></i>
                        </button>
                        ' . $tambah . $hapus . '
                    </div>';
            })
            ->rawColumns(['aksi', 'status_jadwal', 'pilih'])
            ->make(true);
    }

    public function store(Request $request)
    {
        if (auth()->user()->role === 'pembimbing') {
            return $this->storeByPembimbing($request);
        }

        abort_unless(auth()->user()->role === 'panitia', 403);
        $data = $this->validateJadwal($request);
        abort_unless($this->allBimbinganSiswaIds()->contains((int) $data['siswa_id']), 422, 'Siswa belum terdaftar dalam kelompok bimbingan pembekalan PKL.');
        $data['pembimbing_id'] = $this->pembimbingUntukSiswa(Siswa::with(['kelompokBimbingan.pembimbing', 'kelompokBimbingan.pembimbings', 'pembimbingBimbingan'])->findOrFail($data['siswa_id']));
        if ($data['jenis'] === 'sidang') {
            $data['terkunci'] = true;
            $data['jeda_menit'] = (int) ($request->input('jeda_menit', 0));
            $data['jam_selesai'] = Carbon::parse($data['tanggal'] . ' ' . $data['jam_mulai'])->addMinutes(10)->format('H:i');
        }
        JadwalSidang::updateOrCreate(['siswa_id' => $data['siswa_id'], 'jenis' => $data['jenis']], $data);
        return response()->json(['success' => true]);
    }

    public function update(Request $request, $id)
    {
        abort_unless(auth()->user()->role === 'panitia', 403);

        $jadwal = JadwalSidang::findOrFail($id);
        abort_if($jadwal->terkunci, 422, 'Jadwal yang sudah terkunci tidak dapat diubah.');
        $data = $this->validateJadwal($request);
        $data['siswa_id'] = $jadwal->siswa_id;
        $data['pembimbing_id'] = $jadwal->pembimbing_id;
        if ($data['jenis'] === 'sidang') {
            $data['terkunci'] = true;
            $data['jeda_menit'] = (int) ($request->input('jeda_menit', 0));
            $data['jam_selesai'] = Carbon::parse($data['tanggal'] . ' ' . $data['jam_mulai'])
                ->addMinutes(10)
                ->format('H:i');
        }
        $jadwal->update($data);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $role = auth()->user()->role;
        abort_unless(in_array($role, ['panitia', 'pembimbing'], true), 403);

        $jadwal = JadwalSidang::findOrFail($id);
        abort_if($jadwal->terkunci, 422, 'Jadwal yang sudah terkunci tidak dapat dihapus.');

        if ($role === 'pembimbing') {
            abort_unless(
                $jadwal->jenis === 'bimbingan'
                    && $this->siswaBimbinganIds($this->pembimbingId())->contains((int) $jadwal->siswa_id),
                403,
                'Anda hanya dapat menghapus jadwal bimbingan siswa bimbingan Anda.'
            );
        }

        $jadwal->delete();

        return response()->json(['success' => true]);
    }

    public function bulkDestroy(Request $request)
    {
        $role = auth()->user()->role;
        abort_unless(in_array($role, ['panitia', 'pembimbing'], true), 403);

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $query = JadwalSidang::whereIn('id', $data['ids'])
            ->where(fn ($q) => $q->where('terkunci', false)->orWhereNull('terkunci'));

        if ($role === 'pembimbing') {
            $query->where('jenis', 'bimbingan')
                ->whereIn('siswa_id', $this->siswaBimbinganIds($this->pembimbingId()));
        }

        $dihapus = $query->delete();
        $dilewati = count($data['ids']) - $dihapus;

        return response()->json([
            'success' => true,
            'message' => "{$dihapus} jadwal berhasil dihapus."
                . ($dilewati > 0 ? " {$dilewati} jadwal dilewati (terkunci atau bukan milik Anda)." : ''),
        ]);
    }

    public function generate(Request $request)
    {
        abort_unless(auth()->user()->role === 'panitia', 403);

        $data = $request->validate([
            'jenis' => ['required', Rule::in(['bimbingan', 'sidang'])],
            'tanggal_mulai' => 'required|date',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'durasi_menit' => 'required|integer|min:10|max:240',
            'jeda_menit' => 'nullable|integer|min:0|max:120',
            'ruangan_ids' => 'nullable|required_if:jenis,sidang|array|min:1',
            'ruangan_ids.*' => 'nullable|exists:ruangan_sidangs,id',
            'lewati_akhir_pekan' => 'nullable|boolean',
            'timpa_jadwal' => 'nullable|boolean',
        ]);

        $jenis = $data['jenis'];
        $lewatiAkhirPekan = (bool) ($data['lewati_akhir_pekan'] ?? true);

        if (!empty($data['timpa_jadwal'])) {
            JadwalSidang::where('jenis', $jenis)->delete();
        }

        $sudahTerjadwal = JadwalSidang::where('jenis', $jenis)->pluck('siswa_id');
        $siswaList = Siswa::with(['kelompokBimbingan.pembimbing', 'kelompokBimbingan.pembimbings', 'pembimbingBimbingan'])
            ->whereIn('id', $this->allBimbinganSiswaIds())
            ->whereNotIn('id', $sudahTerjadwal)
            ->orderBy('kelas_id')
            ->orderBy('nama_siswa')
            ->get();

        if ($siswaList->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Semua siswa sudah memiliki jadwal ' . $jenis . '.',
            ], 422);
        }

        $ruanganIds = array_values($data['ruangan_ids'] ?? [null]);
        $tanggal = Carbon::parse($data['tanggal_mulai']);
        $jamMulaiHari = $data['jam_mulai'];
        $jamAkhirHari = $data['jam_selesai'];
        $durasi = $jenis === 'sidang' ? 10 : (int) $data['durasi_menit'];
        $jeda = (int) ($data['jeda_menit'] ?? 0);

        if ($lewatiAkhirPekan) {
            while ($tanggal->isWeekend()) {
                $tanggal->addDay();
            }
        }

        $slotWaktu = Carbon::parse($tanggal->toDateString() . ' ' . $jamMulaiHari);
        $ruanganIndex = 0;
        $dibuat = 0;

        foreach ($siswaList as $siswa) {
            $selesai = $slotWaktu->copy()->addMinutes($durasi);

            // Slot melewati jam kerja: pindah ruangan berikutnya, atau hari berikutnya.
            if ($selesai->format('H:i') > $jamAkhirHari) {
                $ruanganIndex++;
                if ($ruanganIndex >= count($ruanganIds)) {
                    $ruanganIndex = 0;
                    $tanggal->addDay();
                    if ($lewatiAkhirPekan) {
                        while ($tanggal->isWeekend()) {
                            $tanggal->addDay();
                        }
                    }
                }
                $slotWaktu = Carbon::parse($tanggal->toDateString() . ' ' . $jamMulaiHari);
                $selesai = $slotWaktu->copy()->addMinutes($durasi);
            }

            JadwalSidang::create([
                'siswa_id' => $siswa->id,
                'pembimbing_id' => $this->pembimbingUntukSiswa($siswa),
                'ruangan_sidang_id' => $ruanganIds[$ruanganIndex],
                'jenis' => $jenis,
                'terkunci' => $jenis === 'sidang',
                'jeda_menit' => $jeda,
                'tanggal' => $slotWaktu->toDateString(),
                'jam_mulai' => $slotWaktu->format('H:i'),
                'jam_selesai' => $selesai->format('H:i'),
            ]);

            $dibuat++;
            $slotWaktu = $selesai;
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil membuat {$dibuat} jadwal {$jenis} secara otomatis.",
        ]);
    }

    private function validateJadwal(Request $request): array
    {
        return $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'pembimbing_id' => 'nullable|exists:pembimbings,id',
            'ruangan_sidang_id' => 'nullable|exists:ruangan_sidangs,id',
            'jenis' => ['required', Rule::in(['bimbingan', 'sidang'])],
            'tanggal' => 'required|date',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'catatan' => 'nullable|string',
        ]);
    }

    private function storeByPembimbing(Request $request)
    {
        if ($request->boolean('bulk')) {
            return $this->storeBulkBimbingan($request);
        }

        $jenis = $request->input('jenis');
        abort_unless(in_array($jenis, ['bimbingan', 'sidang'], true), 422, 'Jenis jadwal tidak valid.');

        $data = $request->validate([
            'jenis' => ['required', Rule::in(['bimbingan', 'sidang'])],
            'jadwal_id' => ['nullable', 'exists:jadwal_sidangs,id'],
            'siswa_id' => ['required', 'exists:siswa,id'],
            'ruangan_sidang_id' => [$jenis === 'sidang' ? 'required' : 'nullable', 'exists:ruangan_sidangs,id'],
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'durasi_menit' => [$jenis === 'bimbingan' ? 'required' : 'nullable', 'integer', 'min:1', 'max:480'],
            'jeda_menit' => ['nullable', 'integer', 'min:0', 'max:120'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $pembimbingId = $this->pembimbingId();
        $siswaIds = $this->siswaBimbinganIds($pembimbingId);
        abort_unless($siswaIds->contains((int) $data['siswa_id']), 422, 'Siswa bukan bagian dari bimbingan pembekalan Anda.');

        $tanggal = Carbon::parse($data['tanggal']);
        $mulai = Carbon::parse($data['tanggal'] . ' ' . $data['jam_mulai']);
        $durasi = $jenis === 'sidang' ? 10 : (int) $data['durasi_menit'];
        $jeda = (int) ($data['jeda_menit'] ?? 0);
        $ruanganId = $data['ruangan_sidang_id'] ?? null;
        $selesai = $mulai->copy()->addMinutes($durasi);
        $jadwalId = $data['jadwal_id'] ?? null;

        // Jadwal lama yang diedit harus milik siswa & jenis yang sama.
        $jadwalLama = $jadwalId
            ? JadwalSidang::where('id', $jadwalId)
                ->where('siswa_id', $data['siswa_id'])
                ->where('jenis', $jenis)
                ->first()
            : null;
        abort_if($jadwalLama?->terkunci, 422, 'Jadwal yang sudah terkunci tidak dapat diubah.');

        if ($jenis === 'sidang' && !$jadwalLama && JadwalSidang::where('jenis', 'sidang')
            ->where('siswa_id', $data['siswa_id'])
            ->exists()) {
            abort(422, 'Siswa sudah memiliki jadwal sidang yang terkunci.');
        }

        abort_if(
            $this->hasScheduleConflict($tanggal->toDateString(), $mulai, $selesai, $pembimbingId, $ruanganId, $jadwalLama?->id),
            422,
            'Jadwal bertabrakan dengan jadwal lain.'
        );

        $values = [
            'siswa_id' => (int) $data['siswa_id'],
            'pembimbing_id' => $pembimbingId,
            'ruangan_sidang_id' => $ruanganId,
            'jenis' => $jenis,
            'terkunci' => $jenis === 'sidang',
            'jeda_menit' => $jeda,
            'tanggal' => $tanggal->toDateString(),
            'jam_mulai' => $mulai->format('H:i'),
            'jam_selesai' => $selesai->format('H:i'),
            'catatan' => $data['catatan'] ?? null,
        ];

        if ($jadwalLama) {
            $jadwalLama->update($values);
        } elseif ($jenis === 'sidang') {
            JadwalSidang::updateOrCreate(['siswa_id' => (int) $data['siswa_id'], 'jenis' => 'sidang'], $values);
        } else {
            // Bimbingan boleh lebih dari satu jadwal, termasuk beberapa sesi di hari yang sama.
            JadwalSidang::updateOrCreate(
                [
                    'siswa_id' => (int) $data['siswa_id'],
                    'jenis' => 'bimbingan',
                    'tanggal' => $tanggal->toDateString(),
                    'jam_mulai' => $mulai->format('H:i'),
                ],
                $values
            );
        }

        return response()->json([
            'success' => true,
            'message' => $jenis === 'sidang'
                ? 'Jadwal sidang berhasil dibuat dan dikunci (10 menit).'
                : 'Jadwal bimbingan berhasil disimpan.',
        ]);
    }

    /**
     * Jadwal bimbingan massal oleh pembimbing: berlaku untuk seluruh siswa
     * bimbingannya, bisa satu hari atau rentang beberapa hari.
     */
    private function storeBulkBimbingan(Request $request)
    {
        $data = $request->validate([
            'jenis' => ['required', Rule::in(['bimbingan'])],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'ruangan_sidang_id' => ['nullable', 'exists:ruangan_sidangs,id'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'siswa_ids' => ['nullable', 'array'],
            'siswa_ids.*' => ['integer'],
        ]);

        $pembimbingId = $this->pembimbingId();
        $siswaIds = $this->siswaBimbinganIds($pembimbingId);
        abort_if($siswaIds->isEmpty(), 422, 'Belum ada siswa yang ditugaskan kepada Anda.');

        // Jika ada siswa terpilih, jadwal hanya berlaku untuk mereka; kosong = semua siswa bimbingan.
        $terpilih = collect($data['siswa_ids'] ?? [])->map(fn ($id) => (int) $id)->unique();
        if ($terpilih->isNotEmpty()) {
            $siswaIds = $siswaIds->map(fn ($id) => (int) $id)->intersect($terpilih)->values();
            abort_if($siswaIds->isEmpty(), 422, 'Siswa terpilih bukan bagian dari bimbingan Anda.');
        }

        $tanggalMulai = Carbon::parse($data['tanggal_mulai']);
        $tanggalSelesai = Carbon::parse($data['tanggal_selesai']);
        $jumlahHari = $tanggalMulai->diffInDays($tanggalSelesai) + 1;
        $dibuat = 0;

        DB::transaction(function () use ($data, $siswaIds, $pembimbingId, $tanggalMulai, $tanggalSelesai, &$dibuat) {
            for ($hari = $tanggalMulai->copy(); $hari->lte($tanggalSelesai); $hari->addDay()) {
                foreach ($siswaIds as $siswaId) {
                    JadwalSidang::updateOrCreate(
                        [
                            'siswa_id' => $siswaId,
                            'jenis' => 'bimbingan',
                            'tanggal' => $hari->toDateString(),
                            'jam_mulai' => $data['jam_mulai'],
                        ],
                        [
                            'pembimbing_id' => $pembimbingId,
                            'ruangan_sidang_id' => $data['ruangan_sidang_id'] ?? null,
                            'terkunci' => false,
                            'jeda_menit' => 0,
                            'jam_mulai' => $data['jam_mulai'],
                            'jam_selesai' => $data['jam_selesai'],
                            'catatan' => $data['catatan'] ?? null,
                        ]
                    );
                    $dibuat++;
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Jadwal bimbingan diterapkan ke {$siswaIds->count()} siswa selama {$jumlahHari} hari ({$dibuat} jadwal).",
        ]);
    }

    private function siswaBimbinganIds(int $pembimbingId)
    {
        $fromGroups = DB::table('kelompok_bimbingan_siswa as kbs')
            ->join('kelompok_bimbingan as kb', 'kb.id', '=', 'kbs.kelompok_bimbingan_id')
            ->where(function ($query) use ($pembimbingId) {
                $query->where('kb.pembimbing_id', $pembimbingId)
                    ->orWhereExists(function ($subQuery) use ($pembimbingId) {
                        $subQuery->select(DB::raw(1))
                            ->from('kelompok_bimbingan_pembimbing as kbp')
                            ->whereColumn('kbp.kelompok_bimbingan_id', 'kb.id')
                            ->where('kbp.pembimbing_id', $pembimbingId);
                    });
            })
            ->distinct()
            ->pluck('kbs.siswa_id');

        $direct = Pembimbing::find($pembimbingId)?->siswaBimbingan()->pluck('siswa.id') ?? collect();

        return $fromGroups->merge($direct)->unique()->values();
    }

    /**
     * Siswa yang sudah mempunyai penugasan bimbingan pada pembekalan PKL.
     * Jadwal tidak dibuat untuk siswa di luar penugasan tersebut.
     */
    private function allBimbinganSiswaIds()
    {
        return Siswa::query()
            ->whereHas('kelompokBimbingan')
            ->orWhereHas('pembimbingBimbingan')
            ->pluck('id');
    }

    private function hasScheduleConflict(string $tanggal, Carbon $mulai, Carbon $selesai, int $pembimbingId, ?int $ruanganId, ?int $kecualiId = null): bool
    {
        return JadwalSidang::whereDate('tanggal', $tanggal)
            ->when($kecualiId, fn ($query) => $query->where('id', '!=', $kecualiId))
            ->where(function ($query) use ($pembimbingId, $ruanganId) {
                $query->where('pembimbing_id', $pembimbingId);
                if ($ruanganId) {
                    $query->orWhere('ruangan_sidang_id', $ruanganId);
                }
            })
            ->where(function ($query) use ($mulai, $selesai) {
                $query->where('jam_mulai', '<', $selesai->format('H:i:s'))
                    ->where('jam_selesai', '>', $mulai->format('H:i:s'));
            })
            ->exists();
    }

    private function pembimbingUntukSiswa(Siswa $siswa): ?int
    {
        $kelompok = $siswa->kelompokBimbingan->first();
        if (!$kelompok) {
            return $siswa->pembimbingBimbingan->first()?->id;
        }

        return $kelompok->pembimbing_id
            ?? optional($kelompok->pembimbings->first())->id;
    }

    private function currentSiswa(): Siswa
    {
        $siswa = Siswa::where('nis', (string) auth()->user()->username)->first();
        abort_if(!$siswa, 403, 'Data siswa untuk akun ini tidak ditemukan.');

        return $siswa;
    }

    private function pembimbingId(): int
    {
        $id = Pembimbing::where('nip_pembimbing', (string) auth()->user()->username)->value('id');
        abort_if(!$id, 403, 'Data pembimbing untuk akun ini tidak ditemukan.');

        return (int) $id;
    }
}
