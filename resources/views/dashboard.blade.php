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
        })->count();
        $jurusanTempatPkl = \App\Models\TempatPkl::whereHas('siswa.kelas.jurusan', function ($query) {
            $query->where('id', auth()->user()->jurusan_id);
        })->count();

        $totalPerusahaan = \App\Models\Perusahaan::count();
        $totalSuratIzin = \App\Models\SuratIzinOrtu::count();
        $totalTempatPkl = \App\Models\TempatPkl::count();
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
                        <h3>{{ \App\Models\Siswa::where('status', 'belum_terdaftar')->whereHas('kelas.jurusan', function ($query) {$query->where('id', auth()->user()->jurusan_id);})->count() }}</h3>
                        <p>Belum Mendaftar</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <a href="{{ url('/siswa?status=belum_terdaftar') }}" class="small-box-footer">Lihat Data <i class="fas fa-arrow-circle-right"></i></a>
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
        <div class="row pt-4">
            <div class="col-lg-3 col-md-6 col-12 mb-4 d-flex">
                <div class="small-box bg-secondary shadow-sm h-100 w-100 mb-0">
                    <div class="inner p-3">
                        <p class="mb-1 text-uppercase small">{{ $percent($totalTempatPkl, $totalSiswa) }}%</p>
                        <h3 class="mb-1">{{ $totalPerusahaan }}</h3>
                        <p class="mb-3">Total Perusahaan</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <a href="/perusahaan" class="small-box-footer">Lihat Data <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-12 mb-4 d-flex">
                <div class="small-box bg-info shadow-sm h-100 w-100 mb-0">
                    <div class="inner p-3">
                        <p class="mb-1 text-uppercase small">{{ $percent($totalSiswa, $totalSiswa) }}%</p>
                        <h3 class="mb-1">{{ $totalSiswa }}</h3>
                        <p class="mb-3">Total Siswa</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <a href="/siswa" class="small-box-footer">Lihat Data <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-12 mb-4 d-flex">
                <div class="small-box bg-success shadow-sm h-100 w-100 mb-0">
                    <div class="inner p-3">
                        <p class="mb-1 text-uppercase small">{{ $percent($totalSuratIzin, $totalSiswa) }}%</p>
                        <h3 class="mb-1">{{ $totalSuratIzin }}</h3>
                        <p class="mb-3">Siswa Mengisi Surat Izin</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-signature"></i>
                    </div>
                    <a href="/surat-izin-ortu" class="small-box-footer">Lihat Data <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-12 mb-4 d-flex">
                <div class="small-box bg-warning shadow-sm h-100 w-100 mb-0">
                    <div class="inner p-3">
                        <p class="mb-1 text-uppercase small">{{ $percent($totalTempatPkl, $totalSiswa) }}%</p>
                        <h3 class="mb-1">{{ $totalTempatPkl }}</h3>
                        <p class="mb-3">Siswa Mengisi Tempat PKL</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <a href="/tempat-pkl" class="small-box-footer">Lihat Data <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-12 mb-4 d-flex">
                <div class="small-box bg-danger shadow-sm h-100 w-100 mb-0">
                    <div class="inner p-3">
                        <p class="mb-1">Siswa yang belum memilih tempat PKL</p>
                        <h3 class="mb-1">{{ \App\Models\Siswa::where('status', 'belum_terdaftar')->count() }}</h3>
                        <p class="mb-3">Belum Mendaftar</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <a href="{{ url('/siswa?status=belum_terdaftar') }}" class="small-box-footer">Lihat Data <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>
        <div class="row pt-4">
            <div class="col-md-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 pb-0">
                        <h4>Statistik</h4>
                    </div>
                    <div class="card-body pt-2">
                        <canvas id="myChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        var ctx = document.getElementById('myChart').getContext('2d');
        var myChart = new Chart(ctx, {
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
    </script>
    <script>
        var ctx = document.getElementById('myChartJ').getContext('2d');
        var myChart = new Chart(ctx, {
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
    </script>
@endsection
