<?php

namespace App\Support;

use App\Models\Siswa;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Merekap nilai keseluruhan siswa: absensi & tugas pembekalan, sikap, nilai praktik PKL, dan sidang.
 */
class RekapNilaiSiswa
{
    private const SKOR_SIKAP = ['sangat_baik' => 4, 'baik' => 3, 'cukup' => 2, 'perlu_bimbingan' => 1];
    private const LABEL_SIKAP = [4 => 'Sangat Baik', 3 => 'Baik', 2 => 'Cukup', 1 => 'Perlu Bimbingan'];

    public function forKelas(int $kelasId): Collection
    {
        $siswa = Siswa::query()->with('kelas')->where('kelas_id', $kelasId)->orderBy('nama_siswa')->get();

        return $this->build($siswa);
    }

    public function build(Collection $siswa): Collection
    {
        $ids = $siswa->pluck('id')->all();

        $absensi = DB::table('absensi_pembekalans')
            ->whereIn('siswa_id', $ids)
            ->selectRaw('siswa_id, status, count(*) total')
            ->groupBy('siswa_id', 'status')
            ->get()
            ->groupBy('siswa_id');

        $tugas = DB::table('nilai_tugas_pembekalans as n')
            ->join('jawaban_tugas_siswas as j', 'j.id', '=', 'n.jawaban_tugas_siswa_id')
            ->whereIn('j.siswa_id', $ids)
            ->selectRaw('j.siswa_id, avg(n.nilai) rata, count(*) jumlah')
            ->groupBy('j.siswa_id')
            ->get()
            ->keyBy('siswa_id');

        $sikap = DB::table('nilai_sikap_pembekalans')
            ->whereIn('siswa_id', $ids)
            ->get(['siswa_id', 'nilai_sikap'])
            ->groupBy('siswa_id');

        $praktik = DB::table('sertifikat_penilaians')
            ->whereIn('siswa_id', $ids)
            ->whereNotNull('nilai')
            ->selectRaw('siswa_id, avg(nilai) rata')
            ->groupBy('siswa_id')
            ->pluck('rata', 'siswa_id');

        $bobot = DB::table('kriteria_sidangs')->pluck('bobot', 'id');
        $sidang = DB::table('nilai_sidangs')
            ->whereIn('siswa_id', $ids)
            ->get(['siswa_id', 'kriteria_sidang_id', 'nilai'])
            ->groupBy('siswa_id');

        return $siswa->map(function ($item) use ($absensi, $tugas, $sikap, $praktik, $sidang, $bobot) {
            $status = ($absensi->get($item->id) ?? collect())->pluck('total', 'status');
            $nilaiTugas = $tugas->get($item->id);
            $nilaiSidang = $this->nilaiSidang($sidang->get($item->id), $bobot);

            return [
                'nis' => $item->nis,
                'nama' => $item->nama_siswa,
                'kelas' => optional($item->kelas)->nama_kelas,
                'hadir' => (int) ($status['hadir'] ?? 0) + (int) ($status['terlambat'] ?? 0),
                'izin' => (int) ($status['izin'] ?? 0),
                'sakit' => (int) ($status['sakit'] ?? 0),
                'alpa' => (int) ($status['alpa'] ?? 0),
                'tugas_dinilai' => $nilaiTugas ? (int) $nilaiTugas->jumlah : 0,
                'tugas_rata' => $nilaiTugas ? round((float) $nilaiTugas->rata, 2) : null,
                'sikap' => $this->labelSikap($sikap->get($item->id)),
                'praktik' => isset($praktik[$item->id]) ? round((float) $praktik[$item->id], 2) : null,
                'sidang' => $nilaiSidang,
                'predikat' => $nilaiSidang !== null ? $this->predikat($nilaiSidang) : null,
            ];
        });
    }

    private function labelSikap(?Collection $rows): ?string
    {
        if (!$rows || $rows->isEmpty()) {
            return null;
        }

        $skor = $rows->avg(fn ($row) => self::SKOR_SIKAP[$row->nilai_sikap] ?? 0);

        return self::LABEL_SIKAP[(int) round($skor)] ?? null;
    }

    // Rumus sama dengan SidangController::hitungNilaiAkhir (rata-rata berbobot).
    private function nilaiSidang(?Collection $rows, Collection $bobot): ?float
    {
        if (!$rows || $rows->isEmpty()) {
            return null;
        }

        $jumlah = 0;
        $totalBobot = 0;
        foreach ($rows as $row) {
            $b = (int) ($bobot[$row->kriteria_sidang_id] ?? 0);
            if ($b <= 0) {
                continue;
            }
            $jumlah += (float) $row->nilai * $b;
            $totalBobot += $b;
        }

        return $totalBobot === 0 ? round((float) $rows->avg('nilai'), 2) : round($jumlah / $totalBobot, 2);
    }

    private function predikat(float $nilai): string
    {
        return match (true) {
            $nilai >= 90 => 'Sangat Baik (A)',
            $nilai >= 80 => 'Baik (B)',
            $nilai >= 70 => 'Cukup (C)',
            default => 'Perlu Bimbingan (D)',
        };
    }
}
