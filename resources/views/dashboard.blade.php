@extends('adminlte::page')

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Dashboard</h1>
        </div>
    </div>
@endsection



@section('content')
    @php
        $percent = static function ($num, $den) {
            return $den > 0 ? number_format(($num / $den) * 100, 2) : '0.00';
        };

        $totalSiswa = \App\Models\Siswa::count();
        $jurusanSiswa = \App\Models\Siswa::whereHas('kelas.jurusan', function ($query) {
            $query->where('id', auth()->user()->jurusan_id);
        })->count();
        $jurusanSuratIzin = \App\Models\SuratIzinOrtu::whereHas('siswa.kelas.jurusan', function ($query) {
            $query->where('id', auth()->user()->jurusan_id);
        })
            ->distinct('siswa_id')
            ->count('siswa_id');
        $jurusanTempatPkl = \App\Models\TempatPkl::whereHas('siswa.kelas.jurusan', function ($query) {
            $query->where('id', auth()->user()->jurusan_id);
        })
            ->distinct('siswa_id')
            ->count('siswa_id');

        $totalPerusahaan = \App\Models\Perusahaan::count();
        $totalSuratIzin = \App\Models\SuratIzinOrtu::distinct('siswa_id')->count('siswa_id');
        $totalTempatPkl = \App\Models\TempatPkl::distinct('siswa_id')->count('siswa_id');
        $totalPembimbing = \App\Models\Pembimbing::count();
        $totalKelompokBimbingan = \App\Models\KelompokBimbingan::count();
        $totalSesiBimbingan = \App\Models\Bimbingan::count();
        $rataNilaiGlobal = round((float) (\App\Models\NilaiTugasPembekalan::avg('nilai') ?? 0), 2);
        $siswaBelumTempat = max(0, $totalSiswa - $totalTempatPkl);

        $selectedJurusanId = request('jurusan_id');
        $selectedKelasId = request('kelas_id');

        $jurusanOptions = \App\Models\Jurusan::orderBy('nama_jurusan')->get(['id', 'nama_jurusan']);

        $kelasOptionsQuery = \App\Models\Kelas::query()->orderBy('nama_kelas');
        if (!empty($selectedJurusanId)) {
            $kelasOptionsQuery->where('jurusan_id', $selectedJurusanId);
        }
        $kelasOptions = $kelasOptionsQuery->get(['id', 'nama_kelas']);

        $latestSikapSub = \Illuminate\Support\Facades\DB::table('nilai_sikap_pembekalans')
            ->selectRaw('siswa_id, MAX(id) as max_id')
            ->groupBy('siswa_id');

        $latestKelengkapanSub = \Illuminate\Support\Facades\DB::table('cek_kelengkapan_siswas')
            ->selectRaw('siswa_id, MAX(id) as max_id')
            ->groupBy('siswa_id');

        $topSiswaQuery = \App\Models\Siswa::query()
            ->leftJoin('jawaban_tugas_siswas as jts', 'jts.siswa_id', '=', 'siswa.id')
            ->leftJoin('nilai_tugas_pembekalans as ntp', 'ntp.jawaban_tugas_siswa_id', '=', 'jts.id')
            ->leftJoin('absensi_pembekalans as ap', 'ap.siswa_id', '=', 'siswa.id')
            ->leftJoinSub($latestSikapSub, 'nsl', function ($join) {
                $join->on('nsl.siswa_id', '=', 'siswa.id');
            })
            ->leftJoin('nilai_sikap_pembekalans as nsp', 'nsp.id', '=', 'nsl.max_id')
            ->leftJoinSub($latestKelengkapanSub, 'ckl', function ($join) {
                $join->on('ckl.siswa_id', '=', 'siswa.id');
            })
            ->leftJoin('cek_kelengkapan_siswas as cks', 'cks.id', '=', 'ckl.max_id')
            ->leftJoin('kelas', 'kelas.id', '=', 'siswa.kelas_id')
            ->select('siswa.id', 'siswa.nama_siswa', 'siswa.nis', 'kelas.nama_kelas', 'kelas.jurusan_id')
            ->selectRaw('nsp.nilai_sikap as nilai_sikap_terakhir')
            ->selectRaw('cks.is_lengkap as kelengkapan_terakhir')
            ->selectRaw('COALESCE(ROUND(AVG(ntp.nilai),2),0) as rata_nilai')
            ->selectRaw(
                "COUNT(DISTINCT CASE WHEN ap.status = 'hadir' THEN CONCAT(ap.tanggal_absensi, '-', ap.sesi_absensi) ELSE NULL END) as total_hadir",
            )
            ->selectRaw('COUNT(DISTINCT CONCAT(ap.tanggal_absensi, "-", ap.sesi_absensi)) as total_sesi_absensi')
            ->selectRaw('COUNT(DISTINCT ntp.id) as tugas_terkumpul')->selectRaw("ROUND(
                COALESCE(
                    (COUNT(DISTINCT CASE WHEN ap.status = 'hadir' THEN CONCAT(ap.tanggal_absensi, '-', ap.sesi_absensi) ELSE NULL END)
                        / NULLIF(COUNT(DISTINCT CONCAT(ap.tanggal_absensi, '-', ap.sesi_absensi)), 0))
* 80,
                    0
                )
                + COALESCE(AVG(ntp.nilai), 0) * 0.20
            , 2) as skor");

        if (!empty($selectedJurusanId)) {
            $topSiswaQuery->where('kelas.jurusan_id', $selectedJurusanId);
        }

        if (!empty($selectedKelasId)) {
            $topSiswaQuery->where('siswa.kelas_id', $selectedKelasId);
        }

        $topSiswa = $topSiswaQuery
            ->groupBy(
                'siswa.id',
                'siswa.nama_siswa',
                'siswa.nis',
                'kelas.nama_kelas',
                'kelas.jurusan_id',
                'nsp.nilai_sikap',
                'cks.is_lengkap',
            )
            ->orderByDesc('skor')
            ->limit(10)
            ->get();

        // Get top 10 students for each jurusan (for dashboard display)
        $topSiswaPerJurusan = [];
        if (empty($selectedJurusanId) && empty($selectedKelasId)) {
            $allJurusan = \App\Models\Jurusan::orderBy('nama_jurusan')->get(['id', 'nama_jurusan']);
            foreach ($allJurusan as $jurusan) {
                $topSiswaPerJurusan[$jurusan->id] = \App\Models\Siswa::query()
                    ->leftJoin('jawaban_tugas_siswas as jts', 'jts.siswa_id', '=', 'siswa.id')
                    ->leftJoin('nilai_tugas_pembekalans as ntp', 'ntp.jawaban_tugas_siswa_id', '=', 'jts.id')
                    ->leftJoin('absensi_pembekalans as ap', 'ap.siswa_id', '=', 'siswa.id')
                    ->leftJoinSub($latestSikapSub, 'nsl', function ($join) {
                        $join->on('nsl.siswa_id', '=', 'siswa.id');
                    })
                    ->leftJoin('nilai_sikap_pembekalans as nsp', 'nsp.id', '=', 'nsl.max_id')
                    ->leftJoinSub($latestKelengkapanSub, 'ckl', function ($join) {
                        $join->on('ckl.siswa_id', '=', 'siswa.id');
                    })
                    ->leftJoin('cek_kelengkapan_siswas as cks', 'cks.id', '=', 'ckl.max_id')
                    ->leftJoin('kelas', 'kelas.id', '=', 'siswa.kelas_id')
                    ->select('siswa.id', 'siswa.nama_siswa', 'siswa.nis', 'kelas.nama_kelas')
                    ->selectRaw('nsp.nilai_sikap as nilai_sikap_terakhir')
                    ->selectRaw('cks.is_lengkap as kelengkapan_terakhir')
                    ->selectRaw('COALESCE(ROUND(AVG(ntp.nilai),2),0) as rata_nilai')
                    ->selectRaw(
                        "COUNT(DISTINCT CASE WHEN ap.status = 'hadir' THEN CONCAT(ap.tanggal_absensi, '-', ap.sesi_absensi) ELSE NULL END) as total_hadir",
                    )
                    ->selectRaw(
                        'COUNT(DISTINCT CONCAT(ap.tanggal_absensi, "-", ap.sesi_absensi)) as total_sesi_absensi',
                    )
                    ->selectRaw('COUNT(DISTINCT ntp.id) as tugas_terkumpul')
                    ->selectRaw(
                        "ROUND(
                        COALESCE(
                            (
                                COUNT(DISTINCT CASE WHEN ap.status = 'hadir' THEN CONCAT(ap.tanggal_absensi, '-', ap.sesi_absensi) ELSE NULL END)
                                / NULLIF(COUNT(DISTINCT CONCAT(ap.tanggal_absensi, '-', ap.sesi_absensi)), 0)
                            ) * 80,
                            0
                        )
                        + COALESCE(AVG(ntp.nilai), 0) * 0.20
                    , 2) as skor",
                    )
                    ->where('kelas.jurusan_id', $jurusan->id)
                    ->groupBy(
                        'siswa.id',
                        'siswa.nama_siswa',
                        'siswa.nis',
                        'kelas.nama_kelas',
                        'nsp.nilai_sikap',
                        'cks.is_lengkap',
                    )
                    ->orderByDesc('skor')
                    ->limit(10)
                    ->get();
            }
        }

        // Siswa dengan persentase kehadiran < 50%
        $siswaBawahTigaPuluPersen = [];
        $siswaBelumKerjakan = [];

        if (!empty($selectedJurusanId) || !empty($selectedKelasId)) {
            $query = \App\Models\Siswa::query()
                ->leftJoin('absensi_pembekalans as ap', 'ap.siswa_id', '=', 'siswa.id')
                ->leftJoin('jawaban_tugas_siswas as jts', 'jts.siswa_id', '=', 'siswa.id')
                ->leftJoin('kelas', 'kelas.id', '=', 'siswa.kelas_id');

            if (!empty($selectedJurusanId)) {
                $query->where('kelas.jurusan_id', $selectedJurusanId);
            }
            if (!empty($selectedKelasId)) {
                $query->where('siswa.kelas_id', $selectedKelasId);
            }

            $siswaBawahTigaPuluPersen = $query
                ->clone()
                ->select('siswa.id', 'siswa.nama_siswa', 'siswa.nis', 'kelas.nama_kelas')
                ->selectRaw('COUNT(DISTINCT CONCAT(ap.tanggal_absensi, "-", ap.sesi_absensi)) as total_hari')
                ->selectRaw(
                    "COUNT(DISTINCT CASE WHEN ap.status = 'hadir' THEN CONCAT(ap.tanggal_absensi, '-', ap.sesi_absensi) ELSE NULL END) as total_hadir",
                )
                ->groupBy('siswa.id', 'siswa.nama_siswa', 'siswa.nis', 'kelas.nama_kelas')
                ->havingRaw('total_hari > 0 AND ROUND((total_hadir / total_hari) * 100, 2) < 50')
                ->orderBy('siswa.nama_siswa')
                ->get()
                ->map(function ($siswa) {
                    $kehadiran =
                        $siswa->total_hari > 0 ? round(($siswa->total_hadir / $siswa->total_hari) * 100, 2) : 0;
                    $siswa->persentase_kehadiran = $kehadiran;
                    return $siswa;
                });

            $siswaBelumKerjakan = $query
                ->clone()
                ->select('siswa.id', 'siswa.nama_siswa', 'siswa.nis', 'kelas.nama_kelas')
                ->selectRaw('COUNT(DISTINCT jts.tugas_pembekalan_id) as total_tugas_submitted')
                ->where(function ($q) {
                    $q->whereNull('jts.submitted_at')->orWhereRaw('jts.submitted_at = ""');
                })
                ->orWhereDoesntHave('jawabanSiswa')
                ->groupBy('siswa.id', 'siswa.nama_siswa', 'siswa.nis', 'kelas.nama_kelas')
                ->orderBy('siswa.nama_siswa')
                ->get();
        } else {
            $query = \App\Models\Siswa::query()
                ->leftJoin('absensi_pembekalans as ap', 'ap.siswa_id', '=', 'siswa.id')
                ->leftJoin('jawaban_tugas_siswas as jts', 'jts.siswa_id', '=', 'siswa.id')
                ->leftJoin('kelas', 'kelas.id', '=', 'siswa.kelas_id');

            $siswaBawahTigaPuluPersen = $query
                ->clone()
                ->select('siswa.id', 'siswa.nama_siswa', 'siswa.nis', 'kelas.nama_kelas')
                ->selectRaw('COUNT(DISTINCT CONCAT(ap.tanggal_absensi, "-", ap.sesi_absensi)) as total_hari')
                ->selectRaw(
                    "COUNT(DISTINCT CASE WHEN ap.status = 'hadir' THEN CONCAT(ap.tanggal_absensi, '-', ap.sesi_absensi) ELSE NULL END) as total_hadir",
                )
                ->groupBy('siswa.id', 'siswa.nama_siswa', 'siswa.nis', 'kelas.nama_kelas')
                ->havingRaw('total_hari > 0 AND ROUND((total_hadir / total_hari) * 100, 2) < 50')
                ->orderBy('siswa.nama_siswa')
                ->get()
                ->map(function ($siswa) {
                    $kehadiran =
                        $siswa->total_hari > 0 ? round(($siswa->total_hadir / $siswa->total_hari) * 100, 2) : 0;
                    $siswa->persentase_kehadiran = $kehadiran;
                    return $siswa;
                });

            $siswaBelumKerjakan = \App\Models\Siswa::query()
                ->leftJoin('jawaban_tugas_siswas as jts', function ($join) {
                    $join->on('jts.siswa_id', '=', 'siswa.id')->whereNotNull('jts.submitted_at');
                })
                ->leftJoin('kelas', 'kelas.id', '=', 'siswa.kelas_id')
                ->select('siswa.id', 'siswa.nama_siswa', 'siswa.nis', 'kelas.nama_kelas')
                ->selectRaw('COUNT(DISTINCT jts.id) as tugas_selesai')
                ->whereNull('jts.id')
                ->groupBy('siswa.id', 'siswa.nama_siswa', 'siswa.nis', 'kelas.nama_kelas')
                ->orderBy('siswa.nama_siswa')
                ->get();
        }

        // Disuplai dari DashboardController agar view lebih ringan.
        $topGuru = $topGuru ?? collect();
        $guruWeights = $guruWeights ?? ['absensi' => 25, 'sikap' => 25, 'kelengkapan' => 25, 'nilai' => 25];
        $guruTotalBobot = $guruTotalBobot ?? array_sum($guruWeights);
        $guruTanggalAwal = $guruTanggalAwal ?? request('guru_tanggal_awal');
        $guruTanggalAkhir = $guruTanggalAkhir ?? request('guru_tanggal_akhir');
        $guruRankingQueryParams = $guruRankingQueryParams ?? [
            'guru_tanggal_awal' => $guruTanggalAwal,
            'guru_tanggal_akhir' => $guruTanggalAkhir,
            'bobot_absensi' => $guruWeights['absensi'],
            'bobot_sikap' => $guruWeights['sikap'],
            'bobot_kelengkapan' => $guruWeights['kelengkapan'],
            'bobot_nilai' => $guruWeights['nilai'],
        ];
    @endphp

    @if (auth()->user()->role == 'kepala_program')
        <div class="row pt-4">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-primary h-100">
                    <div class="inner">
                        <h3>Welcome</h3>
                        <p>Anda login sebagai Kepala Program.</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-info h-100">
                    <div class="inner">
                        <p>{{ $percent($jurusanSiswa, $totalSiswa) }}
                            %</p>

                        <h3>{{ $jurusanSiswa }}
                        </h3>
                        <p>Jumlah Siswa</p>
                        <a href="/siswa" class="btn btn-block btn-primary">Lihat Data</a>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success h-100">
                    <div class="inner">
                        <p>{{ $percent($jurusanSuratIzin, $jurusanSiswa) }}
                            %</p>

                        <h3>{{ $jurusanSuratIzin }}
                        </h3>
                        <p>Jumlah Siswa yang sudah mengisi surat izin</p>
                        <a href="/surat-izin-ortu" class="btn btn-block btn-primary">Lihat Data</a>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-signature"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning h-100">
                    <div class="inner">
                        <p>{{ $percent($jurusanTempatPkl, $jurusanSiswa) }}
                            %</p>

                        <h3>{{ $jurusanTempatPkl }}
                        </h3>
                        <p>Jumlah Siswa yang sudah mengisi tempat pkl</p>
                        <a href="/tempat-pkl" class="btn btn-block btn-primary">Lihat Data</a>
                    </div>
                    <div class="icon">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger h-100">
                    <div class="inner">
                        <p>Siswa yang belum memilih tempat PKL</p>
                        <h3>{{ \App\Models\Siswa::where('status', 'belum_terdaftar')->whereHas('kelas.jurusan', function ($query) {$query->where('id', auth()->user()->jurusan_id);})->count() }}
                        </h3>
                        <p>Belum Mendaftar</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <a href="{{ url('/siswa?status=belum_terdaftar') }}" class="small-box-footer">Lihat Data <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        <div class="row pt-4">
            <div class="col-lg-6 col-12">
                <div class="small-box shadow-sm cursor-pointer"
                    style="background: linear-gradient(135deg, #ff6b6b 0%, #c92a2a 100%); color: white; cursor: pointer;"
                    data-toggle="modal" data-target="#modalSiswaBawahAbsensi">
                    <div class="inner">
                        <h3 style="color: white;">{{ $siswaBawahTigaPuluPersen->count() }}</h3>
                        <p style="color: rgba(255,255,255,0.9);">Siswa Kehadiran < 50%</p>
                                <small style="color: rgba(255,255,255,0.8);">Klik untuk melihat detail siswa</small>
                    </div>
                    <div class="icon" style="color: rgba(255,255,255,0.3);">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-12">
                <div class="small-box shadow-sm cursor-pointer"
                    style="background: linear-gradient(135deg, #ffa94d 0%, #fd7e14 100%); color: white; cursor: pointer;"
                    data-toggle="modal" data-target="#modalSiswaBelumKerjakan">
                    <div class="inner">
                        <h3 style="color: white;">{{ $siswaBelumKerjakan->count() }}</h3>
                        <p style="color: rgba(255,255,255,0.9);">Siswa Belum Kerjakan Tugas</p>
                        <small style="color: rgba(255,255,255,0.8);">Klik untuk melihat detail siswa</small>
                    </div>
                    <div class="icon" style="color: rgba(255,255,255,0.3);">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row pt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Statistik</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="myChartJ" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Siswa Bawah Kehadiran -->
        <div class="modal fade" id="modalSiswaBawahAbsensi" tabindex="-1" role="dialog"
            aria-labelledby="modalSiswaBawahAbsensiLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalSiswaBawahAbsensiLabel">Siswa Kehadiran < 50%</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                    </div>
                    <div class="modal-body">
                        @if ($siswaBawahTigaPuluPersen->isEmpty())
                            <div class="alert alert-info">Tidak ada siswa dengan kehadiran kurang dari 50%.</div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-sm">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Siswa</th>
                                            <th>NIS</th>
                                            <th>Kelas</th>
                                            <th style="width: 80px;">Total Hari</th>
                                            <th style="width: 80px;">Hadir</th>
                                            <th style="width: 100px;">Persentase</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($siswaBawahTigaPuluPersen as $index => $siswa)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td><strong>{{ $siswa->nama_siswa }}</strong></td>
                                                <td>{{ $siswa->nis }}</td>
                                                <td>{{ $siswa->nama_kelas ?? '-' }}</td>
                                                <td style="text-align: center;">{{ $siswa->total_hari }}</td>
                                                <td style="text-align: center;">{{ $siswa->total_hadir }}</td>
                                                <td style="text-align: center;">
                                                    <span
                                                        class="badge badge-danger">{{ $siswa->persentase_kehadiran }}%</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Siswa Belum Kerjakan Tugas -->
        <div class="modal fade" id="modalSiswaBelumKerjakan" tabindex="-1" role="dialog"
            aria-labelledby="modalSiswaBelumKerjakanLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalSiswaBelumKerjakanLabel">Siswa Belum Kerjakan Tugas</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @if ($siswaBelumKerjakan->isEmpty())
                            <div class="alert alert-info">Semua siswa sudah mengerjakan tugas.</div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-sm">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Siswa</th>
                                            <th>NIS</th>
                                            <th>Kelas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($siswaBelumKerjakan as $index => $siswa)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td><strong>{{ $siswa->nama_siswa }}</strong></td>
                                                <td>{{ $siswa->nis }}</td>
                                                <td>{{ $siswa->nama_kelas ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @elseif (auth()->user()->role == 'panitia')
        <div class="card shadow-sm border-0 mt-3 mb-3">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between">
                    <div class="mb-2 mb-md-0">
                        <h5 class="mb-1">Dashboard Panitia PKL</h5>
                        <small class="text-muted">Pantau progres administrasi PKL, pembekalan, dan performa siswa dalam
                            satu
                            tampilan.</small>
                    </div>
                    <div>
                        <a href="/siswa" class="btn btn-sm btn-outline-primary mb-1">Data Siswa</a>
                        <a href="/tempat-pkl" class="btn btn-sm btn-outline-success mb-1">Data Tempat PKL</a>
                        <a href="/monitoring" class="btn btn-sm btn-outline-info mb-1">Data Monitoring</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3 col-md-6 col-12 mb-4 d-flex">
                <div class="small-box bg-info shadow-sm h-100 w-100 mb-0">
                    <div class="inner p-3">
                        <p class="mb-1 text-uppercase small">Total Data</p>
                        <h3 class="mb-1">{{ $totalSiswa }}</h3>
                        <p class="mb-3">Total Siswa</p>
                    </div>
                    <div class="icon"><i class="fas fa-user-graduate"></i></div>
                    <a href="/siswa" class="small-box-footer">Lihat Data <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-12 mb-4 d-flex">
                <div class="small-box bg-secondary shadow-sm h-100 w-100 mb-0">
                    <div class="inner p-3">
                        <p class="mb-1 text-uppercase small">Kemitraan</p>
                        <h3 class="mb-1">{{ $totalPerusahaan }}</h3>
                        <p class="mb-3">Total Perusahaan</p>
                    </div>
                    <div class="icon"><i class="fas fa-building"></i></div>
                    <a href="/perusahaan" class="small-box-footer">Lihat Data <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-12 mb-4 d-flex">
                <div class="small-box bg-success shadow-sm h-100 w-100 mb-0">
                    <div class="inner p-3">
                        <p class="mb-1 text-uppercase small">{{ $percent($totalSuratIzin, $totalSiswa) }}%</p>
                        <h3 class="mb-1">{{ $totalSuratIzin }}</h3>
                        <p class="mb-3">Siswa Isi Surat Izin</p>
                    </div>
                    <div class="icon"><i class="fas fa-file-signature"></i></div>
                    <a href="/surat-izin-ortu" class="small-box-footer">Lihat Data <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-12 mb-4 d-flex">
                <div class="small-box bg-warning shadow-sm h-100 w-100 mb-0">
                    <div class="inner p-3">
                        <p class="mb-1 text-uppercase small">{{ $percent($totalTempatPkl, $totalSiswa) }}%</p>
                        <h3 class="mb-1">{{ $totalTempatPkl }}</h3>
                        <p class="mb-3">Siswa Sudah Tempat PKL</p>
                    </div>
                    <div class="icon"><i class="fas fa-briefcase"></i></div>
                    <a href="/tempat-pkl" class="small-box-footer">Lihat Data <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3 col-md-6 col-12 mb-4 d-flex">
                <div class="small-box bg-danger shadow-sm h-100 w-100 mb-0">
                    <div class="inner p-3">
                        <p class="mb-1">Siswa belum memilih tempat PKL</p>
                        <h3 class="mb-1">{{ $siswaBelumTempat }}</h3>
                        <p class="mb-3">Belum Mendaftar</p>
                    </div>
                    <div class="icon"><i class="fas fa-user-clock"></i></div>
                    <a href="{{ url('/siswa?status=belum_terdaftar') }}" class="small-box-footer">Lihat Data <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-12 mb-4 d-flex">
                <div class="small-box bg-primary shadow-sm h-100 w-100 mb-0">
                    <div class="inner p-3">
                        <p class="mb-1">Sumber Daya</p>
                        <h3 class="mb-1">{{ $totalPembimbing }}</h3>
                        <p class="mb-3">Total Pembimbing</p>
                    </div>
                    <div class="icon"><i class="fas fa-user-tie"></i></div>
                    <a href="/pembimbing" class="small-box-footer">Lihat Data <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-12 mb-4 d-flex">
                <div class="small-box bg-indigo shadow-sm h-100 w-100 mb-0">
                    <div class="inner p-3">
                        <p class="mb-1">Operasional Bimbingan</p>
                        <h3 class="mb-1">{{ $totalKelompokBimbingan }}</h3>
                        <p class="mb-3">Kelompok Bimbingan</p>
                    </div>
                    <div class="icon"><i class="fas fa-layer-group"></i></div>
                    <a href="/kelompok-bimbingan" class="small-box-footer">Lihat Data <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-12 mb-4 d-flex">
                <div class="small-box bg-teal shadow-sm h-100 w-100 mb-0">
                    <div class="inner p-3">
                        <p class="mb-1">Mutu Pembekalan</p>
                        <h3 class="mb-1">{{ $rataNilaiGlobal }}</h3>
                        <p class="mb-3">Rata-rata Nilai Tugas</p>
                    </div>
                    <div class="icon"><i class="fas fa-star"></i></div>
                    <span class="small-box-footer">Total Sesi: {{ $totalSesiBimbingan }}</span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-7 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-0 pb-0">
                        <h4 class="mb-0">Statistik Sebaran Siswa Perusahaan</h4>
                    </div>
                    <div class="card-body pt-2">
                        <canvas id="myChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-0 pb-0">
                        <h4 class="mb-0">10 Siswa Terbaik</h4>
                    </div>
                    <div class="card-body table-responsive pt-2">
                        <form method="GET" action="{{ url('/dashboard') }}" class="mb-3"
                            id="top-siswa-filter-form">
                            <div class="form-row">
                                <div class="col-md-5 mb-2">
                                    <select name="jurusan_id" class="form-control form-control-sm js-top-siswa-filter">
                                        <option value="">Semua Jurusan</option>
                                        @foreach ($jurusanOptions as $jurusan)
                                            <option value="{{ $jurusan->id }}"
                                                {{ (string) $selectedJurusanId === (string) $jurusan->id ? 'selected' : '' }}>
                                                {{ $jurusan->nama_jurusan }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <select name="kelas_id" class="form-control form-control-sm js-top-siswa-filter">
                                        <option value="">Semua Kelas</option>
                                        @foreach ($kelasOptions as $kelas)
                                            <option value="{{ $kelas->id }}"
                                                {{ (string) $selectedKelasId === (string) $kelas->id ? 'selected' : '' }}>
                                                {{ $kelas->nama_kelas }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2 d-flex">
                                    <button type="submit" class="btn btn-sm btn-primary mr-1 w-100">Filter</button>
                                    <a href="{{ url('/dashboard') }}"
                                        class="btn btn-sm btn-outline-secondary w-100">Reset</a>
                                </div>
                            </div>
                        </form>

                        <table class="table table-bordered table-striped table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th title="20% nilai + 80% kehadiran">Skor</th>
                                    <th>Nilai</th>
                                    <th>Catatan Sikap</th>
                                    <th>Kelengkapan</th>
                                    <th>Hadir (Sesi)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($topSiswa as $index => $row)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $row->nama_siswa }}</strong><br>
                                            <small>NIS: {{ $row->nis }}</small>
                                        </td>
                                        <td>{{ $row->nama_kelas ?? '-' }}</td>
                                        <td><span class="badge badge-primary">{{ $row->skor }}</span></td>
                                        <td><span class="badge badge-success">{{ $row->rata_nilai }}</span></td>
                                        <td>
                                            @php
                                                $sikapLabel = $row->nilai_sikap_terakhir
                                                    ? ucwords(str_replace('_', ' ', $row->nilai_sikap_terakhir))
                                                    : 'Belum Dinilai';
                                                $sikapBadge =
                                                    [
                                                        'sangat_baik' => 'success',
                                                        'baik' => 'primary',
                                                        'cukup' => 'warning',
                                                        'kurang' => 'danger',
                                                    ][$row->nilai_sikap_terakhir] ?? 'secondary';
                                            @endphp
                                            <span class="badge badge-{{ $sikapBadge }}">{{ $sikapLabel }}</span>
                                        </td>
                                        <td>
                                            @if (is_null($row->kelengkapan_terakhir))
                                                <span class="badge badge-secondary">Belum Dicek</span>
                                            @elseif ((int) $row->kelengkapan_terakhir === 1)
                                                <span class="badge badge-success">Lengkap</span>
                                            @else
                                                <span class="badge badge-danger">Belum Lengkap</span>
                                            @endif
                                        </td>
                                        <td>{{ $row->total_hadir }} sesi</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Data nilai siswa belum tersedia.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 pb-0">
                        <div class="d-flex flex-wrap align-items-start justify-content-between">
                            <div>
                                <h4 class="mb-0">10 Guru Terbaik (Kelengkapan Pengisian)</h4>
                                <small class="text-muted">Skor akhir berbobot dari 4 komponen: absensi lengkap,
                                    catatan sikap, cek kelengkapan, dan pengisian nilai tugas.</small>
                            </div>
                            <div class="mt-2 mt-md-0">
                                <a href="{{ route('dashboard.panitia.top-guru.export-excel', array_filter($guruRankingQueryParams, fn($v) => $v !== null && $v !== '')) }}"
                                    class="btn btn-sm btn-outline-success mr-1">
                                    <i class="fas fa-file-excel mr-1"></i> Export Excel
                                </a>
                                <a href="{{ route('dashboard.panitia.top-guru.export-pdf', array_filter($guruRankingQueryParams, fn($v) => $v !== null && $v !== '')) }}"
                                    class="btn btn-sm btn-outline-danger" target="_blank">
                                    <i class="fas fa-file-pdf mr-1"></i> Export PDF
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-2 pb-1 border-bottom">
                        <form method="GET" action="{{ url('/dashboard') }}">
                            <div class="form-row align-items-end">
                                <div class="col-md-2 mb-2">
                                    <label class="mb-1">Dari Tanggal</label>
                                    <input type="date" name="guru_tanggal_awal" class="form-control form-control-sm"
                                        value="{{ $guruTanggalAwal }}">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="mb-1">Sampai Tanggal</label>
                                    <input type="date" name="guru_tanggal_akhir" class="form-control form-control-sm"
                                        value="{{ $guruTanggalAkhir }}">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="mb-1">Bobot Absensi</label>
                                    <input type="number" min="0" step="0.01" name="bobot_absensi"
                                        class="form-control form-control-sm" value="{{ $guruWeights['absensi'] }}">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="mb-1">Bobot Sikap</label>
                                    <input type="number" min="0" step="0.01" name="bobot_sikap"
                                        class="form-control form-control-sm" value="{{ $guruWeights['sikap'] }}">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="mb-1">Bobot Kelengkapan</label>
                                    <input type="number" min="0" step="0.01" name="bobot_kelengkapan"
                                        class="form-control form-control-sm" value="{{ $guruWeights['kelengkapan'] }}">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="mb-1">Bobot Nilai</label>
                                    <input type="number" min="0" step="0.01" name="bobot_nilai"
                                        class="form-control form-control-sm" value="{{ $guruWeights['nilai'] }}">
                                </div>
                            </div>
                            <div class="form-row align-items-center">
                                <div class="col-md-8 mb-2">
                                    <small class="text-muted">Total bobot aktif: {{ number_format($guruTotalBobot, 2) }}.
                                        Jika semua bobot 0, sistem otomatis pakai 25-25-25-25.</small>
                                </div>
                                <div class="col-md-4 mb-2 d-flex justify-content-md-end">
                                    <button type="submit" class="btn btn-sm btn-primary mr-1">Terapkan</button>
                                    <a href="{{ url('/dashboard') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body table-responsive pt-2">
                        <table class="table table-bordered table-striped table-sm mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th>Nama Guru</th>
                                    <th style="width: 110px;">Skor Akhir</th>
                                    <th style="width: 160px;" title="Hari lengkap datang+pulang / total hari absensi">
                                        Absensi</th>
                                    <th style="width: 160px;" title="Total siswa tercatat per hari / target siswa-hari">
                                        Sikap
                                    </th>
                                    <th style="width: 170px;" title="Total siswa dicek per hari / target siswa-hari">
                                        Kelengkapan</th>
                                    <th style="width: 150px;"
                                        title="Jawaban tugas yang sudah dinilai / jawaban yang sudah dikumpulkan">Nilai
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($topGuru as $index => $guru)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $guru->nama_pembimbing }}</strong><br>
                                            <small>NIP: {{ $guru->nip_pembimbing ?? '-' }} | Siswa:
                                                {{ $guru->total_siswa_bimbingan }}</small>
                                        </td>
                                        <td><span class="badge badge-primary">{{ $guru->skor_akhir }}</span></td>
                                        <td>
                                            <span class="badge badge-info">{{ $guru->score_absensi }}%</span>
                                            <div><small>{{ $guru->absensi_hari_lengkap }}/{{ $guru->absensi_hari_total }}
                                                    hari</small></div>
                                        </td>
                                        <td>
                                            <span class="badge badge-warning">{{ $guru->score_sikap }}%</span>
                                            <div><small>{{ $guru->sikap_siswa_hari_tercatat }}/{{ $guru->sikap_siswa_hari_target }}
                                                    siswa-hari</small></div>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary">{{ $guru->score_kelengkapan }}%</span>
                                            <div><small>{{ $guru->kelengkapan_siswa_hari_tercatat }}/{{ $guru->kelengkapan_siswa_hari_target }}
                                                    siswa-hari</small></div>
                                        </td>
                                        <td>
                                            <span class="badge badge-success">{{ $guru->score_nilai }}%</span>
                                            <div><small>{{ $guru->nilai_tugas_terisi }}/{{ $guru->nilai_tugas_submitted }}
                                                    tugas</small></div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-3">Belum ada data pembimbing
                                            yang cukup untuk dinilai.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- 10 Siswa Terbaik Per Jurusan --}}
        @if (!empty($topSiswaPerJurusan) && count($topSiswaPerJurusan) > 0)
            <div class="row pt-4">
                @foreach ($topSiswaPerJurusan as $jurusanId => $siswaList)
                    @php
                        $jurusan = $jurusanOptions->find($jurusanId);
                    @endphp
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">10 Siswa Terbaik - {{ $jurusan->nama_jurusan ?? 'Jurusan' }}</h6>
                            </div>
                            <div class="card-body table-responsive p-0">
                                <table class="table table-bordered table-striped table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 30px;">No</th>
                                            <th>Nama Siswa</th>
                                            <th style="width: 60px;" title="20% nilai + 80% kehadiran">Skor</th>
                                            <th style="width: 60px;">Nilai</th>
                                            <th style="width: 90px;">Sikap</th>
                                            <th style="width: 90px;">Kelengkapan</th>
                                            <th style="width: 50px;">Hadir</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($siswaList as $index => $row)
                                            <tr>
                                                <td class="text-center">{{ $index + 1 }}</td>
                                                <td>
                                                    <small>{{ $row->nama_siswa }}<br>
                                                        <span class="text-muted">{{ $row->nis }}</span></small>
                                                </td>
                                                <td><span class="badge badge-primary">{{ $row->skor }}</span></td>
                                                <td>
                                                    <span
                                                        class="badge badge-{{ $row->rata_nilai >= 80 ? 'success' : ($row->rata_nilai >= 70 ? 'warning' : 'danger') }}">
                                                        {{ $row->rata_nilai }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @php
                                                        $sikapLabel = $row->nilai_sikap_terakhir
                                                            ? ucwords(str_replace('_', ' ', $row->nilai_sikap_terakhir))
                                                            : 'Belum';
                                                        $sikapBadge =
                                                            [
                                                                'sangat_baik' => 'success',
                                                                'baik' => 'primary',
                                                                'cukup' => 'warning',
                                                                'kurang' => 'danger',
                                                            ][$row->nilai_sikap_terakhir] ?? 'secondary';
                                                    @endphp
                                                    <span
                                                        class="badge badge-{{ $sikapBadge }}">{{ $sikapLabel }}</span>
                                                </td>
                                                <td>
                                                    @if (is_null($row->kelengkapan_terakhir))
                                                        <span class="badge badge-secondary">Belum</span>
                                                    @elseif ((int) $row->kelengkapan_terakhir === 1)
                                                        <span class="badge badge-success">Lengkap</span>
                                                    @else
                                                        <span class="badge badge-danger">Belum</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">{{ $row->total_hadir ?? 0 }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-3">Belum ada data</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        var chartElement = document.getElementById('myChart');
        if (chartElement) {
            var ctx = chartElement.getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: [
                        @foreach (\App\Models\TempatPkl::with('perusahaan', 'siswa')->get()->groupBy('perusahaan_id') as $group)
                            '{{ $group->first()->perusahaan->nama_perusahaan }}',
                        @endforeach
                    ],
                    datasets: [{
                        label: 'Jumlah Siswa',
                        data: [
                            @foreach (\App\Models\TempatPkl::with('perusahaan', 'siswa')->get()->groupBy('perusahaan_id') as $group)
                                {{ $group->count() }},
                            @endforeach
                        ],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(255, 159, 64, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    </script>
    <script>
        var chartJurusanElement = document.getElementById('myChartJ');
        if (chartJurusanElement) {
            var ctxJurusan = chartJurusanElement.getContext('2d');
            new Chart(ctxJurusan, {
                type: 'bar',
                data: {
                    labels: [
                        @foreach (\App\Models\TempatPkl::whereHas('siswa.kelas.jurusan', function ($query) {
        $query->where('id', auth()->user()->jurusan_id);
    })->get()->groupBy('perusahaan_id') as $group)
                            '{{ $group->first()->perusahaan->nama_perusahaan }}',
                        @endforeach
                    ],
                    datasets: [{
                        label: 'Jumlah Siswa',
                        data: [
                            @foreach (\App\Models\TempatPkl::whereHas('siswa.kelas.jurusan', function ($query) {
        $query->where('id', auth()->user()->jurusan_id);
    })->get()->groupBy('perusahaan_id') as $group)
                                {{ $group->count() }},
                            @endforeach
                        ],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(255, 159, 64, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    </script>
    <script>
        var filterForm = document.getElementById('top-siswa-filter-form');
        if (filterForm) {
            var filterInputs = document.querySelectorAll('.js-top-siswa-filter');
            filterInputs.forEach(function(input) {
                input.addEventListener('change', function() {
                    filterForm.submit();
                });
            });
        }
    </script>
@endsection
