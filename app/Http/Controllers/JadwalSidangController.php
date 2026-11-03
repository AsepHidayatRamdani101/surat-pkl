<?php

namespace App\Http\Controllers;

use App\Models\JadwalSidang;
use App\Models\Pembimbing;
use App\Models\RuanganSidang;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class JadwalSidangController extends Controller
{
    public function index()
    {
        $role = auth()->user()->role;
        abort_unless(in_array($role, ['panitia', 'pembimbing', 'siswa'], true), 403);

        $ruangan = RuanganSidang::orderBy('nama_ruangan')->get();
        $pembimbing = $role === 'panitia'
            ? Pembimbing::orderBy('nama_pembimbing')->get(['id', 'nama_pembimbing'])
            : collect();
        $siswaList = $role === 'panitia'
            ? Siswa::with('kelas')->orderBy('nama_siswa')->get(['id', 'nama_siswa', 'kelas_id'])
            : collect();

        return view('sidang.jadwal.index', [
            'role' => $role,
            'ruangan' => $ruangan,
            'pembimbingList' => $pembimbing,
            'siswaList' => $siswaList,
        ]);
    }

    public function data(Request $request)
    {
        $user = auth()->user();
        abort_unless(in_array($user->role, ['panitia', 'pembimbing', 'siswa'], true), 403);

        $query = JadwalSidang::with(['siswa.kelas', 'pembimbing', 'ruangan'])
            ->orderBy('tanggal')
            ->orderBy('jam_mulai');

        if ($user->role === 'siswa') {
            $query->where('siswa_id', $this->currentSiswa()->id);
        } elseif ($user->role === 'pembimbing') {
            $query->where('pembimbing_id', $this->pembimbingId());
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->input('jenis'));
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('nama_siswa', fn ($item) => optional($item->siswa)->nama_siswa ?? '-')
            ->addColumn('kelas', fn ($item) => optional(optional($item->siswa)->kelas)->nama_kelas ?? '-')
            ->addColumn('nama_pembimbing', fn ($item) => optional($item->pembimbing)->nama_pembimbing ?? '-')
            ->addColumn('nama_ruangan', fn ($item) => optional($item->ruangan)->nama_ruangan ?? '-')
            ->editColumn('tanggal', fn ($item) => $item->tanggal
                ? $item->tanggal->locale('id')->translatedFormat('d F Y')
                : '-')
            ->addColumn('jam', fn ($item) => substr($item->jam_mulai, 0, 5) . ' - ' . substr($item->jam_selesai, 0, 5))
            ->addColumn('aksi', function ($item) use ($user) {
                if ($user->role !== 'panitia') {
                    return '';
                }

                return '
                    <div class="btn-group btn-aksi">
                        <button type="button" class="btn btn-outline-primary btn-sm btnEdit" title="Edit"
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
                        <button type="button" class="btn btn-outline-danger btn-sm btnHapus" title="Hapus"
                            data-id="' . $item->id . '">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->role === 'panitia', 403);

        $data = $this->validateJadwal($request);
        JadwalSidang::create($data);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, $id)
    {
        abort_unless(auth()->user()->role === 'panitia', 403);

        $data = $this->validateJadwal($request);
        JadwalSidang::findOrFail($id)->update($data);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->role === 'panitia', 403);

        JadwalSidang::findOrFail($id)->delete();

        return response()->json(['success' => true]);
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
            'ruangan_ids' => 'required|array|min:1',
            'ruangan_ids.*' => 'exists:ruangan_sidangs,id',
            'lewati_akhir_pekan' => 'nullable|boolean',
            'timpa_jadwal' => 'nullable|boolean',
        ]);

        $jenis = $data['jenis'];
        $lewatiAkhirPekan = (bool) ($data['lewati_akhir_pekan'] ?? true);

        if (!empty($data['timpa_jadwal'])) {
            JadwalSidang::where('jenis', $jenis)->delete();
        }

        $sudahTerjadwal = JadwalSidang::where('jenis', $jenis)->pluck('siswa_id');
        $siswaList = Siswa::with(['kelompokBimbingan.pembimbing', 'kelompokBimbingan.pembimbings'])
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

        $ruanganIds = array_values($data['ruangan_ids']);
        $tanggal = Carbon::parse($data['tanggal_mulai']);
        $jamMulaiHari = $data['jam_mulai'];
        $jamAkhirHari = $data['jam_selesai'];
        $durasi = (int) $data['durasi_menit'];

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

    private function pembimbingUntukSiswa(Siswa $siswa): ?int
    {
        $kelompok = $siswa->kelompokBimbingan->first();
        if (!$kelompok) {
            return null;
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
