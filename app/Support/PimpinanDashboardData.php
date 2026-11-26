<?php

namespace App\Support;

use App\Models\JadwalSidang;
use App\Models\Pembimbing;
use App\Models\RuanganSidang;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Menyusun data dashboard untuk role pimpinan/bidang. Setiap role hanya
 * mendapat data yang relevan dengan tugasnya.
 */
class PimpinanDashboardData
{
    public function build(string $role): array
    {
        return match ($role) {
            'kepala_sekolah' => $this->kepalaSekolah(),
            'waka_kurikulum' => $this->kurikulum(),
            'waka_hubin' => $this->hubin(),
            'waka_kesiswaan' => $this->kesiswaan(),
            'waka_sarpras' => $this->sarpras(),
            'waka_bina_program' => $this->binaProgram(),
            default => [],
        };
    }

    private function kepalaSekolah(): array
    {
        $totalSiswa = DB::table('siswa')->count();
        $siswaPenempatan = DB::table('tempat_pkl')->distinct()->count('siswa_id');
        $nilaiAkhir = DB::table('sertifikat_sidangs')->avg('nilai_akhir');

        return [
            'stats' => [
                ['label' => 'Total Siswa', 'value' => $totalSiswa, 'icon' => 'fas fa-user-graduate', 'color' => 'info', 'url' => url('siswa')],
                ['label' => 'Sudah Mendapat Tempat PKL', 'value' => $siswaPenempatan, 'note' => $this->percent($siswaPenempatan, $totalSiswa), 'icon' => 'fas fa-briefcase', 'color' => 'success', 'url' => url('tempat-pkl')],
                ['label' => 'Mitra Industri', 'value' => DB::table('perusahaan')->count(), 'icon' => 'fas fa-building', 'color' => 'warning', 'url' => url('perusahaan')],
                ['label' => 'Pembimbing', 'value' => DB::table('pembimbings')->count(), 'icon' => 'fas fa-users', 'color' => 'primary', 'url' => url('pembimbing')],
                ['label' => 'Sidang Terjadwal', 'value' => $this->jadwalKe('sidang')->count(), 'icon' => 'fas fa-gavel', 'color' => 'danger', 'url' => url('sidang/jadwal')],
                ['label' => 'Rata-rata Nilai Sidang', 'value' => $nilaiAkhir !== null ? number_format((float) $nilaiAkhir, 2) : '-', 'icon' => 'fas fa-star', 'color' => 'secondary', 'url' => url('sidang/penilaian')],
            ],
            'perJurusan' => $this->rekapPerJurusan(),
            'jadwalMendatang' => $this->jadwalMendatang('sidang', 8),
        ];
    }

    private function kurikulum(): array
    {
        $pembimbing = Pembimbing::with('jurusan')->orderBy('nama_pembimbing')->get();
        $siswaPerPembimbing = $this->siswaPerPembimbing();
        $jadwal = JadwalSidang::select('pembimbing_id', 'jenis', DB::raw('count(*) as total'))
            ->groupBy('pembimbing_id', 'jenis')->get()->groupBy('pembimbing_id');
        $kelompok = DB::table('kelompok_bimbingan')->select('pembimbing_id', DB::raw('count(*) as total'))
            ->groupBy('pembimbing_id')->pluck('total', 'pembimbing_id');

        $rows = $pembimbing->map(function ($item) use ($siswaPerPembimbing, $jadwal, $kelompok) {
            $jadwalItem = $jadwal->get($item->id, collect());

            return [
                'nama' => $item->nama_pembimbing,
                'nip' => $item->nip_pembimbing,
                'jenis_guru' => $item->jenis_guru === 'guru_produktif' ? 'Guru Produktif' : 'Adaptif/Normatif',
                'jurusan' => optional($item->jurusan)->nama_jurusan ?? '-',
                'kuota' => (int) $item->jumlah_siswa,
                'siswa' => $siswaPerPembimbing->get($item->id, 0),
                'kelompok' => (int) ($kelompok[$item->id] ?? 0),
                'bimbingan' => (int) optional($jadwalItem->firstWhere('jenis', 'bimbingan'))->total,
                'sidang' => (int) optional($jadwalItem->firstWhere('jenis', 'sidang'))->total,
            ];
        });

        $siswaBelumPembimbing = DB::table('siswa')
            ->whereNotIn('id', DB::table('kelompok_bimbingan_siswa')->select('siswa_id'))
            ->whereNotIn('id', DB::table('pembimbing_siswa')->select('siswa_id'))
            ->count();

        return [
            'stats' => [
                ['label' => 'Total Pembimbing', 'value' => $pembimbing->count(), 'icon' => 'fas fa-users', 'color' => 'primary', 'url' => url('pembimbing')],
                ['label' => 'Kelompok Bimbingan', 'value' => DB::table('kelompok_bimbingan')->count(), 'icon' => 'fas fa-layer-group', 'color' => 'info', 'url' => url('kelompok-bimbingan')],
                ['label' => 'Siswa Belum Punya Pembimbing', 'value' => $siswaBelumPembimbing, 'icon' => 'fas fa-user-slash', 'color' => $siswaBelumPembimbing ? 'danger' : 'success', 'url' => url('kelompok-bimbingan')],
                ['label' => 'Jadwal Bimbingan', 'value' => $this->jadwalKe('bimbingan')->count(), 'icon' => 'fas fa-comments', 'color' => 'warning', 'url' => url('sidang/bimbingan')],
                ['label' => 'Jadwal Sidang', 'value' => $this->jadwalKe('sidang')->count(), 'icon' => 'fas fa-gavel', 'color' => 'secondary', 'url' => url('sidang/jadwal')],
                ['label' => 'Rata-rata Nilai Tugas', 'value' => number_format((float) DB::table('nilai_tugas_pembekalans')->avg('nilai'), 2), 'icon' => 'fas fa-star', 'color' => 'success', 'url' => url('pembekalan/jawaban-siswa')],
            ],
            'pembimbingRows' => $rows,
            'jadwalMendatang' => $this->jadwalMendatang('bimbingan', 8),
        ];
    }

    private function hubin(): array
    {
        $totalSiswa = DB::table('siswa')->count();
        $siswaPenempatan = DB::table('tempat_pkl')->distinct()->count('siswa_id');
        $hariIni = Carbon::today()->toDateString();

        $topPerusahaan = DB::table('tempat_pkl')
            ->join('perusahaan', 'perusahaan.id', '=', 'tempat_pkl.perusahaan_id')
            ->select('perusahaan.nama_perusahaan', 'perusahaan.alamat', DB::raw('count(distinct tempat_pkl.siswa_id) as total'))
            ->groupBy('perusahaan.id', 'perusahaan.nama_perusahaan', 'perusahaan.alamat')
            ->orderByDesc('total')->limit(10)->get();

        $statusPkl = DB::table('tempat_pkl')
            ->select(DB::raw("coalesce(status_pkl, 'PKL') as status"), DB::raw('count(*) as total'))
            ->groupBy('status')->pluck('total', 'status');

        return [
            'stats' => [
                ['label' => 'Mitra Industri', 'value' => DB::table('perusahaan')->count(), 'icon' => 'fas fa-building', 'color' => 'warning', 'url' => url('perusahaan')],
                ['label' => 'Siswa Sudah Ditempatkan', 'value' => $siswaPenempatan, 'note' => $this->percent($siswaPenempatan, $totalSiswa), 'icon' => 'fas fa-briefcase', 'color' => 'success', 'url' => url('tempat-pkl')],
                ['label' => 'Siswa Belum Ditempatkan', 'value' => max(0, $totalSiswa - $siswaPenempatan), 'icon' => 'fas fa-user-clock', 'color' => 'danger', 'url' => url('tempat-pkl')],
                ['label' => 'Surat Izin Orang Tua', 'value' => DB::table('surat_izin_ortu')->distinct()->count('siswa_id'), 'icon' => 'fas fa-file-alt', 'color' => 'info', 'url' => url('surat-izin-ortu')],
                ['label' => 'Absen Selfie Hari Ini', 'value' => DB::table('absensi_selfie_pkls')->whereDate('tanggal', $hariIni)->distinct()->count('siswa_id'), 'icon' => 'fas fa-camera', 'color' => 'primary', 'url' => url('absensi-selfie')],
                ['label' => 'Resume Bulanan Masuk', 'value' => DB::table('resume_pkl_bulanans')->count(), 'icon' => 'fas fa-file-signature', 'color' => 'secondary', 'url' => url('pkl/resume')],
            ],
            'statusPkl' => $statusPkl,
            'topPerusahaan' => $topPerusahaan,
            'perJurusan' => $this->rekapPerJurusan(),
        ];
    }

    private function kesiswaan(): array
    {
        $absensi = DB::table('absensi_pembekalans')->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status');
        $totalAbsensi = (int) $absensi->sum();
        $sikap = DB::table('nilai_sikap_pembekalans')->select('nilai_sikap', DB::raw('count(*) as total'))->groupBy('nilai_sikap')->pluck('total', 'nilai_sikap');
        $kelengkapanTotal = DB::table('cek_kelengkapan_siswas')->count();
        $kelengkapanLengkap = DB::table('cek_kelengkapan_siswas')->where('is_lengkap', true)->count();

        $perluPerhatian = DB::table('siswa')
            ->join('kelas', 'kelas.id', '=', 'siswa.kelas_id')
            ->leftJoin('absensi_pembekalans as a', function ($join) {
                $join->on('a.siswa_id', '=', 'siswa.id')->whereIn('a.status', ['alpa', 'izin', 'sakit']);
            })
            ->leftJoin('nilai_sikap_pembekalans as s', function ($join) {
                $join->on('s.siswa_id', '=', 'siswa.id')->where('s.nilai_sikap', 'perlu_bimbingan');
            })
            ->select('siswa.nama_siswa', 'kelas.nama_kelas',
                DB::raw("count(distinct case when a.status = 'alpa' then a.id end) as alpa"),
                DB::raw("count(distinct case when a.status in ('izin','sakit') then a.id end) as izin_sakit"),
                DB::raw('count(distinct s.id) as sikap_kurang'))
            ->groupBy('siswa.id', 'siswa.nama_siswa', 'kelas.nama_kelas')
            ->havingRaw('alpa > 0 or sikap_kurang > 0')
            ->orderByDesc('alpa')->orderByDesc('sikap_kurang')->limit(10)->get();

        return [
            'stats' => [
                ['label' => 'Total Siswa', 'value' => DB::table('siswa')->count(), 'icon' => 'fas fa-user-graduate', 'color' => 'info', 'url' => url('siswa')],
                ['label' => 'Kehadiran Pembekalan', 'value' => $this->percent((int) ($absensi['hadir'] ?? 0) + (int) ($absensi['terlambat'] ?? 0), $totalAbsensi), 'icon' => 'fas fa-user-check', 'color' => 'success', 'url' => url('pembekalan/absensi/riwayat')],
                ['label' => 'Catatan Alpa', 'value' => (int) ($absensi['alpa'] ?? 0), 'icon' => 'fas fa-user-times', 'color' => 'danger', 'url' => url('pembekalan/absensi/riwayat')],
                ['label' => 'Sikap Perlu Bimbingan', 'value' => (int) ($sikap['perlu_bimbingan'] ?? 0), 'icon' => 'fas fa-notes-medical', 'color' => 'warning', 'url' => url('pembekalan/sikap/riwayat')],
                ['label' => 'Kelengkapan Atribut', 'value' => $this->percent($kelengkapanLengkap, $kelengkapanTotal), 'icon' => 'fas fa-user-tag', 'color' => 'primary', 'url' => url('pembekalan/kelengkapan-siswa')],
                ['label' => 'Pembinaan Siswa', 'value' => DB::table('pembinaan_pembekalan')->count(), 'icon' => 'fas fa-user-shield', 'color' => 'secondary', 'url' => url('pembekalan/pembinaan')],
            ],
            'absensi' => $absensi,
            'sikap' => $sikap,
            'perluPerhatian' => $perluPerhatian,
        ];
    }

    private function sarpras(): array
    {
        $hariIni = Carbon::today();
        $jadwal = JadwalSidang::with(['siswa', 'ruangan', 'pembimbing'])
            ->whereNotNull('ruangan_sidang_id')
            ->orderBy('tanggal')->orderBy('jam_mulai')->get();

        $ruangan = RuanganSidang::orderBy('nama_ruangan')->get()->map(function ($item) use ($jadwal, $hariIni) {
            $milik = $jadwal->where('ruangan_sidang_id', $item->id);

            return [
                'id' => $item->id,
                'nama' => $item->nama_ruangan,
                'lokasi' => $item->lokasi ?: '-',
                'kapasitas' => $item->kapasitas ?: '-',
                'sidang' => $milik->where('jenis', 'sidang')->count(),
                'bimbingan' => $milik->where('jenis', 'bimbingan')->count(),
                'hari_ini' => $milik->filter(fn ($row) => $row->tanggal && $row->tanggal->isSameDay($hariIni))->count(),
                'mendatang' => $milik->filter(fn ($row) => $row->tanggal && $row->tanggal->gte($hariIni))->count(),
            ];
        });

        $bentrok = $this->bentrokRuangan($jadwal->where('jenis', 'sidang'));
        $sidangBelumRuangan = JadwalSidang::where('jenis', 'sidang')->whereNull('ruangan_sidang_id')->count();
        $jadwalHariIni = $jadwal->filter(fn ($row) => $row->tanggal && $row->tanggal->isSameDay($hariIni))->values();

        return [
            'stats' => [
                ['label' => 'Total Ruangan', 'value' => $ruangan->count(), 'icon' => 'fas fa-door-open', 'color' => 'primary', 'url' => url('sidang/ruangan')],
                ['label' => 'Ruangan Dipakai Hari Ini', 'value' => $ruangan->where('hari_ini', '>', 0)->count(), 'icon' => 'fas fa-calendar-day', 'color' => 'success', 'url' => url('sidang/jadwal')],
                ['label' => 'Ruangan Belum Pernah Dipakai', 'value' => $ruangan->filter(fn ($r) => $r['sidang'] + $r['bimbingan'] === 0)->count(), 'icon' => 'fas fa-door-closed', 'color' => 'secondary', 'url' => url('sidang/ruangan')],
                ['label' => 'Sidang Terjadwal', 'value' => $this->jadwalKe('sidang')->count(), 'icon' => 'fas fa-gavel', 'color' => 'info', 'url' => url('sidang/jadwal')],
                ['label' => 'Sidang Tanpa Ruangan', 'value' => $sidangBelumRuangan, 'icon' => 'fas fa-exclamation-circle', 'color' => $sidangBelumRuangan ? 'danger' : 'success', 'url' => url('sidang/jadwal')],
                ['label' => 'Bentrok Ruangan', 'value' => $bentrok->count(), 'icon' => 'fas fa-exclamation-triangle', 'color' => $bentrok->count() ? 'danger' : 'success', 'url' => url('sidang/jadwal')],
            ],
            'ruangan' => $ruangan,
            'jadwalHariIni' => $jadwalHariIni,
            'bentrok' => $bentrok,
            'jadwalMendatang' => $this->jadwalMendatang('sidang', 10),
        ];
    }

    private function binaProgram(): array
    {
        $jurusan = DB::table('jurusan')->orderBy('nama_jurusan')->get();
        $siswa = DB::table('siswa')->join('kelas', 'kelas.id', '=', 'siswa.kelas_id')
            ->select('kelas.jurusan_id', DB::raw('count(*) as total'))->groupBy('kelas.jurusan_id')->pluck('total', 'jurusan_id');
        $kelas = DB::table('kelas')->select('jurusan_id', DB::raw('count(*) as total'))->groupBy('jurusan_id')->pluck('total', 'jurusan_id');
        $pembimbing = DB::table('pembimbings')->select('jurusan_id', DB::raw('count(*) as total'))->groupBy('jurusan_id')->pluck('total', 'jurusan_id');
        $penempatan = DB::table('tempat_pkl')->join('siswa', 'siswa.id', '=', 'tempat_pkl.siswa_id')
            ->join('kelas', 'kelas.id', '=', 'siswa.kelas_id')
            ->select('kelas.jurusan_id', DB::raw('count(distinct tempat_pkl.siswa_id) as total'))->groupBy('kelas.jurusan_id')->pluck('total', 'jurusan_id');
        $nilaiSidang = DB::table('sertifikat_sidangs')->join('siswa', 'siswa.id', '=', 'sertifikat_sidangs.siswa_id')
            ->join('kelas', 'kelas.id', '=', 'siswa.kelas_id')
            ->select('kelas.jurusan_id', DB::raw('avg(sertifikat_sidangs.nilai_akhir) as rata'))->groupBy('kelas.jurusan_id')->pluck('rata', 'jurusan_id');
        $nilaiTugas = DB::table('nilai_tugas_pembekalans as n')
            ->join('jawaban_tugas_siswas as j', 'j.id', '=', 'n.jawaban_tugas_siswa_id')
            ->join('siswa', 'siswa.id', '=', 'j.siswa_id')
            ->join('kelas', 'kelas.id', '=', 'siswa.kelas_id')
            ->select('kelas.jurusan_id', DB::raw('avg(n.nilai) as rata'))->groupBy('kelas.jurusan_id')->pluck('rata', 'jurusan_id');

        $rows = $jurusan->map(fn ($item) => [
            'nama' => $item->nama_jurusan,
            'kode' => $item->kode_jurusan,
            'kelas' => (int) ($kelas[$item->id] ?? 0),
            'siswa' => (int) ($siswa[$item->id] ?? 0),
            'pembimbing' => (int) ($pembimbing[$item->id] ?? 0),
            'penempatan' => (int) ($penempatan[$item->id] ?? 0),
            'nilai_tugas' => isset($nilaiTugas[$item->id]) ? number_format((float) $nilaiTugas[$item->id], 2) : '-',
            'nilai_sidang' => isset($nilaiSidang[$item->id]) ? number_format((float) $nilaiSidang[$item->id], 2) : '-',
        ]);

        $totalTugas = DB::table('tugas_pembekalans')->count();
        $totalSiswa = (int) $siswa->sum();
        $kriteria = DB::table('kriteria_sidangs')->orderBy('urutan')->get(['nama_kriteria', 'bobot']);

        return [
            'stats' => [
                ['label' => 'Program Keahlian', 'value' => $jurusan->count(), 'icon' => 'fas fa-graduation-cap', 'color' => 'primary', 'url' => url('jurusan')],
                ['label' => 'Jumlah Kelas', 'value' => DB::table('kelas')->count(), 'icon' => 'fas fa-chalkboard-teacher', 'color' => 'info', 'url' => url('kelas')],
                ['label' => 'Materi Pembekalan', 'value' => DB::table('materis')->count(), 'icon' => 'fas fa-book-open', 'color' => 'success', 'url' => url('pembekalan/materi')],
                ['label' => 'Tugas Pembekalan', 'value' => $totalTugas, 'icon' => 'fas fa-tasks', 'color' => 'warning', 'url' => url('pembekalan/tugas')],
                ['label' => 'Jawaban Tugas Masuk', 'value' => DB::table('jawaban_tugas_siswas')->count(), 'note' => $totalTugas && $totalSiswa ? $this->percent(DB::table('jawaban_tugas_siswas')->count(), $totalTugas * $totalSiswa) . ' dari target' : null, 'icon' => 'fas fa-clipboard-list', 'color' => 'secondary', 'url' => url('pembekalan/jawaban-siswa')],
                ['label' => 'Kriteria Sidang', 'value' => $kriteria->count(), 'note' => 'Total bobot ' . (int) $kriteria->sum('bobot'), 'icon' => 'fas fa-list-ol', 'color' => 'danger', 'url' => url('sidang/kriteria')],
            ],
            'jurusanRows' => $rows,
            'kriteria' => $kriteria,
        ];
    }

    private function rekapPerJurusan(): Collection
    {
        $siswa = DB::table('siswa')->join('kelas', 'kelas.id', '=', 'siswa.kelas_id')
            ->select('kelas.jurusan_id', DB::raw('count(*) as total'))->groupBy('kelas.jurusan_id')->pluck('total', 'jurusan_id');
        $penempatan = DB::table('tempat_pkl')->join('siswa', 'siswa.id', '=', 'tempat_pkl.siswa_id')
            ->join('kelas', 'kelas.id', '=', 'siswa.kelas_id')
            ->select('kelas.jurusan_id', DB::raw('count(distinct tempat_pkl.siswa_id) as total'))->groupBy('kelas.jurusan_id')->pluck('total', 'jurusan_id');

        return DB::table('jurusan')->orderBy('nama_jurusan')->get()->map(function ($item) use ($siswa, $penempatan) {
            $jumlah = (int) ($siswa[$item->id] ?? 0);
            $ditempatkan = (int) ($penempatan[$item->id] ?? 0);

            return [
                'nama' => $item->nama_jurusan,
                'siswa' => $jumlah,
                'ditempatkan' => $ditempatkan,
                'belum' => max(0, $jumlah - $ditempatkan),
                'persen' => $this->percent($ditempatkan, $jumlah),
            ];
        });
    }

    private function siswaPerPembimbing(): Collection
    {
        $dariKelompok = DB::table('kelompok_bimbingan_siswa as kbs')
            ->join('kelompok_bimbingan as kb', 'kb.id', '=', 'kbs.kelompok_bimbingan_id')
            ->select('kb.pembimbing_id', 'kbs.siswa_id')
            ->union(
                DB::table('kelompok_bimbingan_siswa as kbs')
                    ->join('kelompok_bimbingan_pembimbing as kbp', 'kbp.kelompok_bimbingan_id', '=', 'kbs.kelompok_bimbingan_id')
                    ->select('kbp.pembimbing_id', 'kbs.siswa_id')
            )
            ->union(DB::table('pembimbing_siswa')->select('pembimbing_id', 'siswa_id'));

        return DB::query()->fromSub($dariKelompok, 'x')
            ->whereNotNull('pembimbing_id')
            ->select('pembimbing_id', DB::raw('count(distinct siswa_id) as total'))
            ->groupBy('pembimbing_id')->pluck('total', 'pembimbing_id');
    }

    private function jadwalKe(string $jenis): Collection
    {
        return JadwalSidang::where('jenis', $jenis)->get(['id']);
    }

    private function jadwalMendatang(string $jenis, int $limit): Collection
    {
        return JadwalSidang::with(['siswa.kelas', 'pembimbing', 'ruangan'])
            ->where('jenis', $jenis)
            ->whereDate('tanggal', '>=', Carbon::today())
            ->orderBy('tanggal')->orderBy('jam_mulai')
            ->limit($limit)->get();
    }

    private function bentrokRuangan(Collection $jadwalSidang): Collection
    {
        $bentrok = collect();

        foreach ($jadwalSidang->groupBy(fn ($row) => $row->ruangan_sidang_id . '|' . optional($row->tanggal)->toDateString()) as $group) {
            $urut = $group->sortBy('jam_mulai')->values();
            for ($i = 1; $i < $urut->count(); $i++) {
                if ($urut[$i]->jam_mulai < $urut[$i - 1]->jam_selesai) {
                    $bentrok->push([
                        'ruangan' => optional($urut[$i]->ruangan)->nama_ruangan ?? '-',
                        'tanggal' => optional($urut[$i]->tanggal)->locale('id')->translatedFormat('d F Y'),
                        'a' => optional($urut[$i - 1]->siswa)->nama_siswa . ' (' . substr($urut[$i - 1]->jam_mulai, 0, 5) . '-' . substr($urut[$i - 1]->jam_selesai, 0, 5) . ')',
                        'b' => optional($urut[$i]->siswa)->nama_siswa . ' (' . substr($urut[$i]->jam_mulai, 0, 5) . '-' . substr($urut[$i]->jam_selesai, 0, 5) . ')',
                    ]);
                }
            }
        }

        return $bentrok;
    }

    private function percent(int|float $num, int|float $den): string
    {
        return ($den > 0 ? number_format(($num / $den) * 100, 1) : '0.0') . '%';
    }
}
