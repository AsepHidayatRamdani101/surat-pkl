<?php

namespace App\Http\Controllers\Concerns;

use App\Models\AbsensiPembekalan;
use App\Models\NilaiSikapPembekalan;
use App\Models\Sekolah;
use App\Models\SertifikatSidang;
use App\Models\Siswa;
use App\Models\TempatPkl;
use App\Models\TugasPembekalan;
use Carbon\Carbon;

trait BuildsSertifikatData
{
    protected function buildSertifikatPembekalanDataForSiswa(Siswa $siswa): array
    {
        $siswa->loadMissing('kelas.jurusan');

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
        ], $this->sertifikatSekolahData());
    }

    protected function buildSertifikatPelaksanaanDataForSiswa(Siswa $siswa): ?array
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

        // Prioritas: input siswa, lalu data sistem (menu Perusahaan & Pembimbing Perusahaan).
        $namaPemilik = $tempatPkl->nama_pemilik_instansi
            ?: ($perusahaan->nama_pemilik_perusahaan ?: null);
        $namaPembimbing = $tempatPkl->pemilik_sama_pembimbing
            ? $namaPemilik
            : ($tempatPkl->nama_pembimbing_instansi
                ?: ($tempatPkl->pembimbingPerusahaan?->nama_pembimbing
                    ?: ($tempatPkl->nama_pembimbing ?: null)));

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
            'namaPembimbing' => $namaPembimbing,
            'pemilikSamaPembimbing' => (bool) $tempatPkl->pemilik_sama_pembimbing,
            'tanggalMulai' => $tanggalMulai,
            'tanggalSelesai' => $tanggalSelesai,
            'durasi' => $durasi,
            'sertifikatSidang' => $sertifikatSidang,
        ], $this->sertifikatSekolahData());
    }

    protected function sertifikatSekolahData(): array
    {
        $sekolah = Sekolah::latest('id')->first();
        $relatifJikaAda = fn (?string $path) => $path && is_file(public_path($path)) ? $path : null;
        $ttdPath = $relatifJikaAda($sekolah?->ttd_kepala_sekolah_path ? 'storage/' . $sekolah->ttd_kepala_sekolah_path : null);
        $capPath = $relatifJikaAda($sekolah?->cap_sekolah_path ? 'storage/' . $sekolah->cap_sekolah_path : null);
        $logoPath = $relatifJikaAda('logo.png');

        return [
            'tanggalCetak' => now()->locale('id'),
            'kepalaSekolah' => optional($sekolah)->nama_kepala_sekolah ?? 'MOH. ROFIK ZEN, S.Pd., M.M.Pd.',
            'nipKepalaSekolah' => optional($sekolah)->nip_kepala_sekolah ?? '196906131994121002',
            'ttdPath' => $ttdPath,
            'capPath' => $capPath,
            'logoPath' => $logoPath,
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
}
