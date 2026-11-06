<?php

namespace App\Http\Controllers;

use App\Models\AbsensiSelfiePkl;
use App\Models\AbsensiSelfieSetting;
use App\Models\Pembimbing;
use App\Models\ResumePklBulanan;
use App\Models\Siswa;
use App\Models\TempatPkl;
use App\Models\UploadAbsensiJurnalPkl;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AbsensiSelfiePklController extends Controller
{
    // Jarak (meter) yang dianggap 100% berada di lokasi perusahaan.
    private const RADIUS_PENUH = 100;
    // Jarak (meter) ketika persentase mencapai 0%.
    private const RADIUS_NOL = 1000;

    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'siswa') {
            $siswa = $this->currentSiswa();
            $perusahaan = $this->perusahaanSiswa($siswa->id);
            $today = Carbon::today()->toDateString();
            $absenHariIni = AbsensiSelfiePkl::where('siswa_id', $siswa->id)
                ->whereDate('tanggal', $today)
                ->get()
                ->keyBy('jenis');

            return view('absensi_selfie.index', [
                'title' => 'Absen Datang & Pulang PKL',
                'siswa' => $siswa,
                'perusahaan' => $perusahaan,
                'absenDatang' => $absenHariIni->get('datang'),
                'absenPulang' => $absenHariIni->get('pulang'),
                'setting' => AbsensiSelfieSetting::current(),
            ]);
        }

        abort_unless(in_array($user->role, ['panitia', 'pembimbing'], true), 403);

        return view('absensi_selfie.rekap', [
            'title' => 'Data Absen Selfie PKL Siswa',
            'isPanitia' => $user->role === 'panitia',
            'pembimbingOptions' => $user->role === 'panitia'
                ? Pembimbing::orderBy('nama_pembimbing')->get()
                : collect(),
        ]);
    }

    public function data(Request $request)
    {
        $user = auth()->user();
        $query = AbsensiSelfiePkl::with(['siswa.kelas', 'perusahaan'])
            ->orderByDesc('tanggal')
            ->orderBy('jenis');

        if ($user->role === 'siswa') {
            $query->where('siswa_id', $this->currentSiswa()->id);
        } elseif ($user->role === 'pembimbing') {
            $pembimbingId = $this->pembimbingId();
            $query->whereHas('siswa.kelompokBimbingan', function (Builder $builder) use ($pembimbingId) {
                $builder->where(function (Builder $kelompokQuery) use ($pembimbingId) {
                    $kelompokQuery->where('kelompok_bimbingan.pembimbing_id', $pembimbingId)
                        ->orWhereHas('pembimbings', fn (Builder $mentorQuery) => $mentorQuery->where('pembimbings.id', $pembimbingId));
                });
            });
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->input('tanggal'));
        }

        return response()->json($query->get());
    }

    public function monitoring(Request $request)
    {
        abort_unless(in_array(auth()->user()->role, ['panitia', 'pembimbing'], true), 403);

        $tanggal = ($request->filled('tanggal')
            ? Carbon::parse($request->input('tanggal'))
            : Carbon::today())->locale('id');
        $awalBulan = $tanggal->copy()->startOfMonth();
        $akhirBulan = $tanggal->copy()->endOfMonth();

        $siswaIds = $this->scopedSiswaIds();
        $totalSiswa = $siswaIds->count();

        $hadir = AbsensiSelfiePkl::whereIn('siswa_id', $siswaIds)
            ->whereDate('tanggal', $tanggal->toDateString())
            ->where('jenis', 'datang')
            ->distinct()
            ->count('siswa_id');

        $uploadAbsensi = UploadAbsensiJurnalPkl::whereIn('siswa_id', $siswaIds)
            ->whereBetween('tanggal', [$awalBulan->toDateString(), $akhirBulan->toDateString()])
            ->distinct()
            ->count('siswa_id');

        $uploadResume = ResumePklBulanan::whereIn('siswa_id', $siswaIds)
            ->whereYear('bulan', $tanggal->year)
            ->whereMonth('bulan', $tanggal->month)
            ->distinct()
            ->count('siswa_id');

        $persen = fn (int $jumlah) => $totalSiswa > 0
            ? (int) round($jumlah / $totalSiswa * 100)
            : 0;

        return response()->json([
            'tanggal' => $tanggal->toDateString(),
            'bulan' => $tanggal->translatedFormat('F Y'),
            'total_siswa' => $totalSiswa,
            'kehadiran' => [
                'jumlah' => $hadir,
                'persentase' => $persen($hadir),
            ],
            'upload_absensi' => [
                'jumlah' => $uploadAbsensi,
                'persentase' => $persen($uploadAbsensi),
            ],
            'upload_resume' => [
                'jumlah' => $uploadResume,
                'persentase' => $persen($uploadResume),
            ],
        ]);
    }

    public function monitoringDetail(Request $request)
    {
        abort_unless(in_array(auth()->user()->role, ['panitia', 'pembimbing'], true), 403);

        $data = $request->validate([
            'kategori' => ['required', Rule::in(['kehadiran', 'absensi', 'resume'])],
            'tanggal' => ['nullable', 'date'],
        ]);

        $tanggal = (!empty($data['tanggal']) ? Carbon::parse($data['tanggal']) : Carbon::today())->locale('id');
        $awalBulan = $tanggal->copy()->startOfMonth()->toDateString();
        $akhirBulan = $tanggal->copy()->endOfMonth()->toDateString();

        $siswaList = $this->scopedSiswaQuery()->with('kelas')->orderBy('nama_siswa')->get();
        $siswaIds = $siswaList->pluck('id');

        [$judul, $keteranganMap] = match ($data['kategori']) {
            'kehadiran' => [
                'Detail Kehadiran Siswa — ' . $tanggal->translatedFormat('d F Y'),
                AbsensiSelfiePkl::whereIn('siswa_id', $siswaIds)
                    ->whereDate('tanggal', $tanggal->toDateString())
                    ->orderBy('waktu')
                    ->get()
                    ->groupBy('siswa_id')
                    ->map(fn ($items) => $items->map(
                        fn ($item) => ucfirst($item->jenis) . ' ' . substr($item->waktu, 0, 5)
                            . ($item->persentase_lokasi !== null ? ' (lokasi ' . $item->persentase_lokasi . '%)' : '')
                    )->implode(', ')),
            ],
            'absensi' => [
                'Detail Upload Absensi Jurnal — ' . $tanggal->translatedFormat('F Y'),
                UploadAbsensiJurnalPkl::whereIn('siswa_id', $siswaIds)
                    ->whereBetween('tanggal', [$awalBulan, $akhirBulan])
                    ->get()
                    ->groupBy('siswa_id')
                    ->map(fn ($items) => $items->count() . ' berkas, terakhir '
                        . $items->max('tanggal')->format('d/m/Y')),
            ],
            'resume' => [
                'Detail Upload Resume Bulanan — ' . $tanggal->translatedFormat('F Y'),
                ResumePklBulanan::whereIn('siswa_id', $siswaIds)
                    ->whereYear('bulan', $tanggal->year)
                    ->whereMonth('bulan', $tanggal->month)
                    ->get()
                    ->keyBy('siswa_id')
                    ->map(fn ($item) => 'Judul: ' . $item->judul . ' (' . ($item->status ?? 'dikirim') . ')'),
            ],
        };

        $sudah = [];
        $belum = [];
        foreach ($siswaList as $siswa) {
            $row = [
                'nama_siswa' => $siswa->nama_siswa,
                'kelas' => optional($siswa->kelas)->nama_kelas,
                'keterangan' => $keteranganMap->get($siswa->id),
            ];
            if ($keteranganMap->has($siswa->id)) {
                $sudah[] = $row;
            } else {
                $belum[] = $row;
            }
        }

        return response()->json([
            'judul' => $judul,
            'jumlah_sudah' => count($sudah),
            'jumlah_belum' => count($belum),
            'sudah' => $sudah,
            'belum' => $belum,
        ]);
    }

    private function scopedSiswaQuery(): Builder
    {
        $query = Siswa::query();

        if (auth()->user()->role === 'pembimbing') {
            $pembimbingId = $this->pembimbingId();
            $query->whereHas('kelompokBimbingan', function (Builder $builder) use ($pembimbingId) {
                $builder->where(function (Builder $kelompokQuery) use ($pembimbingId) {
                    $kelompokQuery->where('kelompok_bimbingan.pembimbing_id', $pembimbingId)
                        ->orWhereHas('pembimbings', fn (Builder $mentorQuery) => $mentorQuery->where('pembimbings.id', $pembimbingId));
                });
            });
        }

        return $query;
    }

    private function scopedSiswaIds()
    {
        return $this->scopedSiswaQuery()->pluck('id');
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->role === 'siswa', 403);
        $siswa = $this->currentSiswa();

        $data = $request->validate([
            'jenis' => ['required', Rule::in(['datang', 'pulang'])],
            'foto' => ['required', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'akurasi_gps' => ['nullable', 'numeric', 'min:0'],
        ]);

        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        $setting = AbsensiSelfieSetting::current();
        $gagalWaktu = $this->validasiJendelaWaktu($setting, $data['jenis'], $now);
        if ($gagalWaktu !== null) {
            return response()->json(['message' => $gagalWaktu], 422);
        }

        $sudahAbsen = AbsensiSelfiePkl::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', $today)
            ->where('jenis', $data['jenis'])
            ->exists();
        if ($sudahAbsen) {
            return response()->json([
                'message' => 'Anda sudah melakukan absen ' . $data['jenis'] . ' hari ini.',
            ], 422);
        }

        if ($data['jenis'] === 'pulang') {
            $sudahDatang = AbsensiSelfiePkl::where('siswa_id', $siswa->id)
                ->whereDate('tanggal', $today)
                ->where('jenis', 'datang')
                ->exists();
            if (!$sudahDatang) {
                return response()->json([
                    'message' => 'Silakan lakukan absen datang terlebih dahulu.',
                ], 422);
            }
        }

        $fotoPath = $this->simpanFotoSelfie($data['foto']);

        $perusahaan = $this->perusahaanSiswa($siswa->id);
        $jarak = null;
        $persentase = null;
        if ($perusahaan && $perusahaan->latitude !== null && $perusahaan->longitude !== null) {
            $jarak = (int) round($this->hitungJarakMeter(
                (float) $data['latitude'],
                (float) $data['longitude'],
                (float) $perusahaan->latitude,
                (float) $perusahaan->longitude
            ));
            $persentase = $this->hitungPersentase($jarak);
        }

        $statusWaktu = $this->tentukanStatusWaktu($setting, $data['jenis'], $now);

        $record = AbsensiSelfiePkl::create([
            'siswa_id' => $siswa->id,
            'perusahaan_id' => $perusahaan?->id,
            'tanggal' => $today,
            'jenis' => $data['jenis'],
            'waktu' => $now->format('H:i:s'),
            'foto_path' => $fotoPath,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'akurasi_gps' => isset($data['akurasi_gps']) ? (int) round($data['akurasi_gps']) : null,
            'jarak_meter' => $jarak,
            'persentase_lokasi' => $persentase,
            'status_waktu' => $statusWaktu,
        ]);

        $pesanStatus = $statusWaktu === 'terlambat'
            ? ' Anda tercatat TERLAMBAT.'
            : ($statusWaktu === 'tepat_waktu' ? ' Anda tercatat tepat waktu.' : '');

        return response()->json([
            'message' => 'Absen ' . $data['jenis'] . ' berhasil disimpan.' . $pesanStatus,
            'data' => $record,
            'keterangan_lokasi' => trim($this->keteranganLokasi($jarak, $persentase, $perusahaan?->nama_perusahaan) . $pesanStatus),
        ], 201);
    }

    private function validasiJendelaWaktu(AbsensiSelfieSetting $setting, string $jenis, Carbon $now): ?string
    {
        $jam = fn (?string $time) => $time ? Carbon::parse($now->toDateString() . ' ' . $time) : null;

        if ($jenis === 'datang') {
            $mulai = $jam($setting->jam_datang_mulai);
            $selesai = $jam($setting->jam_datang_selesai);

            if ($mulai && $now->lt($mulai)) {
                return 'Absen datang belum dibuka. Absen dapat dilakukan mulai pukul ' . $mulai->format('H:i') . '.';
            }
            if ($selesai && $now->gt($selesai)) {
                return 'Absen datang sudah ditutup pada pukul ' . $selesai->format('H:i') . '.';
            }

            return null;
        }

        $mulai = $jam($setting->jam_pulang_mulai);
        $selesai = $jam($setting->jam_pulang_selesai);

        if ($mulai && $now->lt($mulai)) {
            return 'Absen pulang belum dibuka. Absen dapat dilakukan mulai pukul ' . $mulai->format('H:i') . '.';
        }
        if ($selesai && $now->gt($selesai)) {
            return 'Absen pulang sudah ditutup pada pukul ' . $selesai->format('H:i') . '.';
        }

        return null;
    }

    private function tentukanStatusWaktu(AbsensiSelfieSetting $setting, string $jenis, Carbon $now): ?string
    {
        if ($jenis !== 'datang' || !$setting->jam_datang_tepat_waktu) {
            return null;
        }

        $batas = Carbon::parse($now->toDateString() . ' ' . $setting->jam_datang_tepat_waktu);

        return $now->lte($batas) ? 'tepat_waktu' : 'terlambat';
    }

    private function simpanFotoSelfie(string $dataUrl): string
    {
        if (!preg_match('/^data:image\/(jpeg|jpg|png);base64,/', $dataUrl, $matches)) {
            abort(422, 'Foto selfie tidak valid. Gunakan kamera untuk mengambil foto.');
        }

        $contents = base64_decode(substr($dataUrl, strpos($dataUrl, ',') + 1), true);
        if ($contents === false || strlen($contents) < 1024) {
            abort(422, 'Foto selfie tidak valid.');
        }
        if (strlen($contents) > 5 * 1024 * 1024) {
            abort(422, 'Ukuran foto terlalu besar (maksimal 5 MB).');
        }

        $extension = $matches[1] === 'png' ? 'png' : 'jpg';
        $path = 'absensi-selfie/' . uniqid('selfie_', true) . '.' . $extension;
        Storage::disk('public')->put($path, $contents);

        return $path;
    }

    private function hitungJarakMeter(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function hitungPersentase(int $jarakMeter): int
    {
        if ($jarakMeter <= self::RADIUS_PENUH) {
            return 100;
        }
        if ($jarakMeter >= self::RADIUS_NOL) {
            return 0;
        }

        $rentang = self::RADIUS_NOL - self::RADIUS_PENUH;

        return (int) round((1 - ($jarakMeter - self::RADIUS_PENUH) / $rentang) * 100);
    }

    private function keteranganLokasi(?int $jarak, ?int $persentase, ?string $namaPerusahaan): string
    {
        if ($jarak === null || $persentase === null) {
            return 'Lokasi perusahaan belum memiliki titik koordinat, akurasi lokasi tidak dapat dihitung.';
        }

        return sprintf(
            'Presentase %d%% mendekati %s (jarak ± %s meter).',
            $persentase,
            $namaPerusahaan ?? 'perusahaan',
            number_format($jarak, 0, ',', '.')
        );
    }

    private function perusahaanSiswa(int $siswaId)
    {
        return TempatPkl::with('perusahaan')
            ->where('siswa_id', $siswaId)
            ->latest('id')
            ->first()
            ?->perusahaan;
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
