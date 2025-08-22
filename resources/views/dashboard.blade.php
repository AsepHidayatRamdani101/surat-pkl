@extends('adminlte::page')

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Dashboard</h1>
        </div>
    </div>
@endsection



@section('content')
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
                        <p>{{ number_format((\App\Models\Siswa::whereHas('kelas.jurusan', function ($query) {$query->where('id', auth()->user()->jurusan_id);})->count() /\App\Models\Siswa::count()) *100,2) }}
                            %</p>

                        <h3>{{ \App\Models\Siswa::whereHas('kelas.jurusan', function ($query) {$query->where('id', auth()->user()->jurusan_id);})->count() }}
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
                        <p>{{ number_format((\App\Models\SuratIzinOrtu::whereHas('siswa.kelas.jurusan', function ($query) {$query->where('id', auth()->user()->jurusan_id);})->count() /\App\Models\Siswa::whereHas('kelas.jurusan', function ($query) {$query->where('id', auth()->user()->jurusan_id);})->count()) *100,2) }}
                            %</p>

                        <h3>{{ \App\Models\SuratIzinOrtu::whereHas('siswa.kelas.jurusan', function ($query) {$query->where('id', auth()->user()->jurusan_id);})->count() }}
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
                        <p>{{ number_format((\App\Models\TempatPkl::whereHas('siswa.kelas.jurusan', function ($query) {$query->where('id', auth()->user()->jurusan_id);})->count() /\App\Models\Siswa::whereHas('kelas.jurusan', function ($query) {$query->where('id', auth()->user()->jurusan_id);})->count()) *100,2) }}
                            %</p>

                        <h3>{{ \App\Models\TempatPkl::whereHas('siswa.kelas.jurusan', function ($query) {$query->where('id', auth()->user()->jurusan_id);})->count() }}
                        </h3>
                        <p>Jumlah Siswa yang sudah mengisi tempat pkl</p>
                        <a href="/tempat-pkl" class="btn btn-block btn-primary">Lihat Data</a>
                    </div>
                    <div class="icon">
                        <i class="fas fa-building"></i>
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
    @elseif (auth()->user()->role == 'panitia')
        <div class="row pt-4">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-secondary h-100">
                    <div class="inner">
                        <p>{{ number_format((\App\Models\TempatPkl::count() / \App\Models\Siswa::count()) * 100, 2) }} %</p>

                        <h3>{{ \App\Models\Perusahaan::count() }}</h3>
                        <p>Total Perusahaan</p>
                        <a href="/perusahaan" class="btn btn-block btn-primary">Lihat Data</a>
                    </div>
                    <div class="icon">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-info h-100">
                    <div class="inner">
                        <p>{{ number_format((\App\Models\Siswa::count() / \App\Models\Siswa::count()) * 100, 2) }} %</p>
                        <h3>{{ \App\Models\Siswa::count() }}</h3>
                        <p>Total Siswa</p>
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
                        <p>{{ number_format((\App\Models\SuratIzinOrtu::count() / \App\Models\Siswa::count()) * 100, 2) }}
                            %
                        </p>
                        <h3>{{ \App\Models\SuratIzinOrtu::count() }}</h3>
                        <p>Siswa Mengisi Surat Izin</p>
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
                        <p>{{ number_format((\App\Models\TempatPkl::count() / \App\Models\Siswa::count()) * 100, 2) }} %
                        </p>
                        <h3>{{ \App\Models\TempatPkl::count() }}</h3>
                        <p>Siswa Mengisi Tempat PKL</p>
                        <a href="/tempat-pkl" class="btn btn-block btn-primary">Lihat Data</a>
                    </div>
                    <div class="icon">
                        <i class="fas fa-building"></i>
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
