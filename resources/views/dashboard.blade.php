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
        $rataNilaiGlobal = round(
            (float) (\App\Models\Bimbingan::whereNotNull('nilai_tugas')->avg('nilai_tugas') ?? 0),
            2,
        );
        $siswaBelumTempat = max(0, $totalSiswa - $totalTempatPkl);

        $selectedJurusanId = request('jurusan_id');
        $selectedKelasId = request('kelas_id');

        $jurusanOptions = \App\Models\Jurusan::orderBy('nama_jurusan')->get(['id', 'nama_jurusan']);

        $kelasOptionsQuery = \App\Models\Kelas::query()->orderBy('nama_kelas');
        if (!empty($selectedJurusanId)) {
            $kelasOptionsQuery->where('jurusan_id', $selectedJurusanId);
        }
        $kelasOptions = $kelasOptionsQuery->get(['id', 'nama_kelas']);

        $topSiswaQuery = \App\Models\Siswa::query()
            ->leftJoin('bimbingans', 'bimbingans.siswa_id', '=', 'siswa.id')
            ->leftJoin('kelas', 'kelas.id', '=', 'siswa.kelas_id')
            ->select('siswa.id', 'siswa.nama_siswa', 'siswa.nis', 'kelas.nama_kelas')
            ->selectRaw('COALESCE(ROUND(AVG(bimbingans.nilai_tugas),2),0) as rata_nilai')
            ->selectRaw("SUM(CASE WHEN bimbingans.status_absensi = 'hadir' THEN 1 ELSE 0 END) as total_hadir")
            ->selectRaw(
                "SUM(CASE WHEN bimbingans.tugas_siswa IS NOT NULL AND bimbingans.tugas_siswa <> '' THEN 1 ELSE 0 END) as tugas_terkumpul",
            );

        if (!empty($selectedJurusanId)) {
            $topSiswaQuery->where('kelas.jurusan_id', $selectedJurusanId);
        }

        if (!empty($selectedKelasId)) {
            $topSiswaQuery->where('siswa.kelas_id', $selectedKelasId);
        }

        $topSiswa = $topSiswaQuery
            ->groupBy('siswa.id', 'siswa.nama_siswa', 'siswa.nis', 'kelas.nama_kelas')
            ->orderByDesc('rata_nilai')
            ->orderByDesc('total_hadir')
            ->limit(10)
            ->get();
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
    @elseif (auth()->user()->role == 'panitia')
        <div class="card shadow-sm border-0 mt-3 mb-3">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between">
                    <div class="mb-2 mb-md-0">
                        <h5 class="mb-1">Dashboard Panitia PKL</h5>
                        <small class="text-muted">Pantau progres administrasi PKL, pembekalan, dan performa siswa dalam satu
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
                    <a href="/perusahaan" class="small-box-footer">Lihat Data <i class="fas fa-arrow-circle-right"></i></a>
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
                                    <th>Rata Nilai</th>
                                    <th>Hadir</th>
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
                                        <td><span class="badge badge-success">{{ $row->rata_nilai }}</span></td>
                                        <td>{{ $row->total_hadir }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Data nilai siswa belum tersedia.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
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
