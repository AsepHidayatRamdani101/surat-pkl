<?php

namespace App\Http\Controllers\Concerns;

use App\Models\AbsensiPembekalan;
use App\Models\NilaiSikapPembekalan;
use App\Models\Pembimbing_perusahaan;
use App\Models\Sekolah;
use App\Models\SertifikatSidang;
use App\Models\Siswa;
use App\Models\SertifikatSetting;
use App\Models\SertifikatFont;
use App\Models\SertifikatAspek;
use App\Models\SertifikatPenilaian;
use App\Models\TempatPkl;
use App\Models\TugasPembekalan;
use App\Services\WilayahIndonesiaService;
use Carbon\Carbon;

trait BuildsSertifikatData
{
    private ?SertifikatSetting $sertifikatSettingCache = null;

    protected function buildSertifikatPembekalanDataForSiswa(Siswa $siswa, bool $embedAssets = false): array
    {
        $siswa->loadMissing('kelas.jurusan');

        // Identitas pembimbing DUDIKA untuk lembar daftar nilai.
        $tempatPkl = TempatPkl::with(['perusahaan', 'pembimbingPerusahaan'])
            ->where('siswa_id', $siswa->id)
            ->latest('id')
            ->first();
        $pembimbingDudika = $tempatPkl?->pembimbingPerusahaan
            ?: ($tempatPkl
                ? Pembimbing_perusahaan::where('perusahaan_id', $tempatPkl->perusahaan_id)->latest('id')->first()
                : null);
        $namaPemilik = $tempatPkl?->nama_pemilik_instansi
            ?: $tempatPkl?->perusahaan?->nama_pemilik_perusahaan;
        $nipPemilik = $tempatPkl?->nip_pemilik_instansi;
        $namaPembimbing = $tempatPkl?->pemilik_sama_pembimbing
            ? $namaPemilik
            : ($tempatPkl?->nama_pembimbing_instansi
                ?: ($pembimbingDudika?->nama_pembimbing
                    ?: $tempatPkl?->nama_pembimbing));
        $nipPembimbing = $tempatPkl?->pemilik_sama_pembimbing
            ? $nipPemilik
            : ($tempatPkl?->nip_pembimbing_instansi
                ?: ($pembimbingDudika?->NIP
                    ?: $tempatPkl?->nip_pembimbing));

        // Kehadiran pembekalan: sumber sama dengan dashboard siswa (absensi_pembekalans).
        $dailyAbsensi = AbsensiPembekalan::where('siswa_id', $siswa->id)
            ->get()
            ->groupBy(fn ($entry) => (string) $entry->tanggal_absensi)
            ->sortKeys();

        $totalSesi = $dailyAbsensi->count();
        $hadir = 0;
        foreach ($dailyAbsensi as $entries) {
            $statusDatang = optional($entries->firstWhere('sesi_absensi', 'datang'))->status;
            $statusPulang = optional($entries->firstWhere('sesi_absensi', 'pulang'))->status;
            if ($statusDatang === 'hadir' && $statusPulang === 'hadir') {
                $hadir++;
            }
        }
        $progres = $totalSesi > 0 ? (int) round(($hadir / $totalSesi) * 100) : 0;

        // Nilai tugas pembekalan.
        $tugasPembekalan = TugasPembekalan::with([
            'materi',
            'jawabanSiswa' => fn ($q) => $q->where('siswa_id', $siswa->id),
            'jawabanSiswa.nilaiTugas',
        ])->get();

        $totalTugas = $tugasPembekalan->count();
        $tugasSelesai = $tugasPembekalan
            ->filter(fn ($item) => !empty($item->jawabanSiswa->first()?->submitted_at))
            ->count();
        $avgNilai = $tugasPembekalan
            ->map(fn ($item) => $item->jawabanSiswa->first()?->nilaiTugas?->nilai)
            ->filter(fn ($nilai) => $nilai !== null)
            ->avg();
        $avgNilai = $avgNilai !== null ? round((float) $avgNilai, 2) : null;

        // Catatan sikap pembekalan (modus dari seluruh penilaian, fallback penilaian terakhir).
        $nilaiSikap = NilaiSikapPembekalan::where('siswa_id', $siswa->id)
            ->orderByDesc('tanggal_penilaian')
            ->orderByDesc('id')
            ->get();
        $sikapTerbanyak = $nilaiSikap->isNotEmpty()
            ? $nilaiSikap->countBy('nilai_sikap')->sortDesc()->keys()->first()
            : null;
        $sikapLabel = [
            'sangat_baik' => 'Sangat Baik',
            'baik' => 'Baik',
            'cukup' => 'Cukup',
            'kurang' => 'Kurang',
            'perlu_bimbingan' => 'Perlu Bimbingan',
        ][$sikapTerbanyak] ?? null;
        $sikapAngka = [
            'sangat_baik' => 95,
            'baik' => 85,
            'cukup' => 75,
            'kurang' => 60,
            'perlu_bimbingan' => 50,
        ][$sikapTerbanyak] ?? null;
        $pembekalanRows = collect([
            [
                'aspek' => 'Kehadiran Kegiatan Pembekalan',
                'nilai' => $progres,
                'predikat' => $this->predikatNilaiPembekalan($progres),
            ],
        ])->concat($tugasPembekalan->map(function ($item) {
            $nilai = $item->jawabanSiswa->first()?->nilaiTugas?->nilai;

            return [
                'aspek' => $item->materi?->topik ?: $item->judul_tugas,
                'nilai' => $nilai,
                'predikat' => $this->predikatNilaiPembekalan($nilai !== null ? (float) $nilai : null),
            ];
        }))->push([
            'aspek' => 'Sikap Selama Pembekalan',
            'nilai' => $sikapAngka,
            'predikat' => $sikapLabel,
        ]);

        return array_merge([
            'siswa' => $siswa,
            'totalSesi' => $totalSesi,
            'hadir' => $hadir,
            'progres' => $progres,
            'totalTugas' => $totalTugas,
            'tugasSelesai' => $tugasSelesai,
            'avgNilai' => $avgNilai,
            'predikatNilai' => $this->predikatNilaiPembekalan($avgNilai),
            'sikap' => $sikapLabel,
            'namaPembimbing' => $namaPembimbing,
            'nipPembimbing' => $nipPembimbing,
            'alamatPerusahaanLengkap' => app(WilayahIndonesiaService::class)
                ->formatAlamatPerusahaan($tempatPkl?->perusahaan),
        ], $this->sertifikatSekolahData($embedAssets), $this->sertifikatDesignData($siswa, 'pembekalan', $pembekalanRows));
    }

    protected function buildSertifikatPelaksanaanDataForSiswa(Siswa $siswa, bool $embedAssets = false): ?array
    {
        $siswa->loadMissing('kelas.jurusan');

        $tempatPkl = TempatPkl::with(['perusahaan', 'pembimbingPerusahaan'])
            ->where('siswa_id', $siswa->id)
            ->latest('id')
            ->first();

        if (!$tempatPkl || !$tempatPkl->perusahaan) {
            return null;
        }

        $perusahaan = $tempatPkl->perusahaan;
        $pembimbingDudika = $tempatPkl->pembimbingPerusahaan
            ?: Pembimbing_perusahaan::where('perusahaan_id', $tempatPkl->perusahaan_id)->latest('id')->first();

        // Prioritas: input siswa, lalu data sistem (menu Perusahaan & Pembimbing Perusahaan).
        $namaPemilik = $tempatPkl->nama_pemilik_instansi
            ?: ($perusahaan->nama_pemilik_perusahaan ?: null);
        $nipPemilik = $tempatPkl->nip_pemilik_instansi ?: null;
        $namaPembimbing = $tempatPkl->pemilik_sama_pembimbing
            ? $namaPemilik
            : ($tempatPkl->nama_pembimbing_instansi
                ?: ($pembimbingDudika?->nama_pembimbing
                    ?: ($tempatPkl->nama_pembimbing ?: null)));
        $nipPembimbing = $tempatPkl->pemilik_sama_pembimbing
            ? $nipPemilik
            : ($tempatPkl->nip_pembimbing_instansi
                ?: ($pembimbingDudika?->NIP
                    ?: ($tempatPkl->nip_pembimbing ?: null)));

        $tanggalMulai = $tempatPkl->tanggal_mulai ? Carbon::parse($tempatPkl->tanggal_mulai) : null;
        $tanggalSelesai = $tempatPkl->tanggal_selesai ? Carbon::parse($tempatPkl->tanggal_selesai) : null;

        $durasi = null;
        if ($tanggalMulai && $tanggalSelesai) {
            $bulan = $tanggalMulai->diffInMonths($tanggalSelesai);
            $sisaHari = $tanggalMulai->copy()->addMonths($bulan)->diffInDays($tanggalSelesai);
            $durasi = trim(($bulan > 0 ? $bulan . ' bulan ' : '') . ($sisaHari > 0 ? $sisaHari . ' hari' : ''));
            $durasi = $durasi !== '' ? $durasi : '1 hari';
        }

        $sertifikatSidang = SertifikatSidang::where('siswa_id', $siswa->id)->first();

        return array_merge([
            'siswa' => $siswa,
            'tempatPkl' => $tempatPkl,
            'perusahaan' => $perusahaan,
            'namaPemilik' => $namaPemilik,
            'nipPemilik' => $nipPemilik,
            'namaPembimbing' => $namaPembimbing,
            'nipPembimbing' => $nipPembimbing,
            'pemilikSamaPembimbing' => (bool) $tempatPkl->pemilik_sama_pembimbing,
            'tanggalMulai' => $tanggalMulai,
            'tanggalSelesai' => $tanggalSelesai,
            'durasi' => $durasi,
            'sertifikatSidang' => $sertifikatSidang,
            'alamatPerusahaanLengkap' => app(WilayahIndonesiaService::class)
                ->formatAlamatPerusahaan($perusahaan),
        ], $this->sertifikatSekolahData($embedAssets), $this->sertifikatDesignData($siswa));
    }

    protected function sertifikatSekolahData(bool $embedAssets = false): array
    {
        $sekolah = Sekolah::latest('id')->first();
        $relatifJikaAda = fn (?string $path) => $path && is_file(public_path($path)) ? $path : null;
        $ttdPath = $relatifJikaAda($sekolah?->ttd_kepala_sekolah_path ? 'storage/' . $sekolah->ttd_kepala_sekolah_path : null);
        $capPath = $relatifJikaAda($sekolah?->cap_sekolah_path ? 'storage/' . $sekolah->cap_sekolah_path : null);
        $logoPath = $relatifJikaAda('logo.png');
        $sertifikatSetting = $this->currentSertifikatSetting();
        $customFonts = SertifikatFont::whereIn('name', $sertifikatSetting->usedCustomFontNames())
            ->orderBy('id')
            ->get()
            ->unique('name')
            ->values();
        $customFont = $customFonts->firstWhere('name', $sertifikatSetting->font_family);
        $frontAsset = $embedAssets
            ? $sertifikatSetting->backgroundFrontDataUri()
            : $sertifikatSetting->backgroundFrontUrl();
        $pembekalanFrontAsset = $embedAssets
            ? $sertifikatSetting->backgroundFrontPembekalanDataUri()
            : $sertifikatSetting->backgroundFrontPembekalanUrl();
        $backAsset = $embedAssets
            ? $sertifikatSetting->backgroundBackDataUri()
            : $sertifikatSetting->backgroundBackUrl();

        return [
            'tanggalCetak' => now()->locale('id'),
            'kepalaSekolah' => optional($sekolah)->nama_kepala_sekolah ?? 'MOH. ROFIK ZEN, S.Pd., M.M.Pd.',
            'nipKepalaSekolah' => optional($sekolah)->nip_kepala_sekolah ?? '196906131994121002',
            'ttdPath' => $ttdPath,
            'capPath' => $capPath,
            'logoPath' => $logoPath,
            'fontFamily' => $sertifikatSetting->font_family,
            'backgroundDataUri' => $frontAsset,
            'backgroundFrontDataUri' => $frontAsset,
            'backgroundFrontPembekalanDataUri' => $pembekalanFrontAsset,
            'backgroundBackDataUri' => $backAsset,
            // Font dirender satu kali melalui customFontFaces pada overlay aktif.
            'customFontDataUri' => null,
            'customFontName' => $customFont?->name ?? $sertifikatSetting->custom_font_name,
            'customFontFaces' => $customFonts->map(fn ($font) => [
                'name' => $font->name,
                'src' => $embedAssets ? $font->dataUri() : $font->url(),
            ])->filter(fn ($font) => !empty($font['src']))->values(),
        ];
    }

    protected function predikatNilaiPembekalan(?float $nilai): ?string
    {
        if ($nilai === null) {
            return null;
        }

        return match (true) {
            $nilai >= 90 => 'Sangat Baik (A)',
            $nilai >= 80 => 'Baik (B)',
            $nilai >= 70 => 'Cukup (C)',
            default => 'Perlu Bimbingan (D)',
        };
    }

    protected function sertifikatDesignData(Siswa $siswa, string $type = 'pelaksanaan', $rows = null): array
    {
        $setting = $this->currentSertifikatSetting();
        if ($type === 'pembekalan') {
            return [
                'certificateType' => 'pembekalan',
                'designLayout' => $setting->pembekalanLayout(),
                'backDesignLayout' => $setting->pembekalanBackLayout(),
                'assessmentRows' => collect($rows ?? []),
            ];
        }

        $jurusanId = $siswa->kelas?->jurusan_id;
        $aspeks = SertifikatAspek::where('jurusan_id', $jurusanId)->orderBy('urutan')->orderBy('id')->get();
        $nilai = SertifikatPenilaian::where('siswa_id', $siswa->id)->get()->keyBy('sertifikat_aspek_id');
        $sertifikatSidang = SertifikatSidang::where('siswa_id', $siswa->id)->first();

        $assessmentRows = $aspeks->map(fn ($aspek) => [
            'aspek' => $aspek->nama_aspek,
            'nilai' => $nilai->get($aspek->id)?->nilai,
            'predikat' => $nilai->get($aspek->id)?->predikat,
        ])->push([
            'aspek' => 'Sidang PKL',
            'nilai' => $sertifikatSidang?->nilai_akhir,
            'predikat' => $sertifikatSidang?->predikat,
        ]);

        return [
            'certificateType' => 'pelaksanaan',
            'designLayout' => $setting->layout(),
            'backDesignLayout' => $setting->backLayout(),
            'assessmentRows' => $assessmentRows,
        ];
    }

    private function currentSertifikatSetting(): SertifikatSetting
    {
        return $this->sertifikatSettingCache ??= SertifikatSetting::current();
    }
}
