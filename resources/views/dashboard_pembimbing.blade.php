@extends('adminlte::page')

@section('title', 'Dashboard Pembimbing')

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Dashboard Pembimbing</h1>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid pt-3">
        @if (!$pembimbing)
            <div class="callout callout-warning">
                Data pembimbing tidak ditemukan untuk akun ini. Pastikan username akun pembimbing sama dengan NIP di data
                pembimbing.
            </div>
        @else
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body py-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                        <div class="mb-2 mb-md-0">
                            <h5 class="mb-1">Selamat datang, {{ $pembimbing->nama_pembimbing }}</h5>
                            <small class="text-muted">Pantau kelompok bimbingan dan buka modul tugas, absensi, serta catatan
                                sikap dari dashboard ini.</small>
                        </div>
                        <div class="dashboard-anchor-nav">
                            <a href="#kelompok-pembimbing" class="btn btn-sm btn-outline-primary mb-1">Kelompok</a>
                            <a href="{{ url('pembekalan/jawaban-siswa') }}"
                                class="btn btn-sm btn-outline-success mb-1">Tugas & Nilai</a>
                            <a href="{{ url('pembekalan/absensi/input') }}" class="btn btn-sm btn-outline-info mb-1">Input
                                Absensi</a>
                            <a href="{{ url('pembekalan/sikap/input') }}" class="btn btn-sm btn-outline-warning mb-1">Input
                                Sikap</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row dashboard-summary-row">
                <div class="col-md-4">
                    <div class="small-box bg-info shadow-sm dashboard-metric-box">
                        <div class="inner">
                            <h3>{{ $pembimbing->nama_pembimbing }}</h3>
                            <p>Pembimbing</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="small-box bg-success shadow-sm dashboard-metric-box">
                        <div class="inner">
                            <h3>{{ $jumlahKelompok }}</h3>
                            <p>Jumlah Kelompok Bimbingan</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-layer-group"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="small-box bg-warning shadow-sm dashboard-metric-box">
                        <div class="inner">
                            <h3>{{ $jumlahSiswaBimbingan }}</h3>
                            <p>Total Siswa Bimbingan</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row dashboard-summary-row">
                <div class="col-md-3 col-6">
                    <div class="small-box bg-primary shadow-sm dashboard-metric-box dashboard-mini-box">
                        <div class="inner">
                            <h3>{{ $summaryPembimbing['total_sesi'] }}</h3>
                            <p>Total Sesi Bimbingan</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="small-box bg-success shadow-sm dashboard-metric-box dashboard-mini-box">
                        <div class="inner">
                            <h3>{{ $summaryPembimbing['tugas_terkumpul'] }}</h3>
                            <p>Tugas Terkumpul</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-file-upload"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="small-box bg-danger shadow-sm dashboard-metric-box dashboard-mini-box">
                        <div class="inner">
                            <h3>{{ $summaryPembimbing['belum_dinilai'] }}</h3>
                            <p>Tugas Belum Dinilai</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-edit"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="small-box bg-info shadow-sm dashboard-metric-box dashboard-mini-box">
                        <div class="inner">
                            <h3>{{ $summaryPembimbing['hadir'] }}</h3>
                            <p>Total Absensi Hadir</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-3" id="tugas-siswa-pembimbing">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-th-large mr-1 text-success"></i>Akses Cepat Modul Pembimbing</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-3 d-flex">
                            <div class="border rounded p-3 w-100 h-100">
                                <h6 class="mb-2">Tugas Siswa & Nilai</h6>
                                <p class="text-muted small mb-3">Lihat jawaban siswa dan input nilai tugas pada modul
                                    pembekalan.</p>
                                <a href="{{ url('pembekalan/jawaban-siswa') }}" class="btn btn-sm btn-success">Buka
                                    Modul</a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3 d-flex">
                            <div class="border rounded p-3 w-100 h-100">
                                <h6 class="mb-2">Input Absensi</h6>
                                <p class="text-muted small mb-3">Isi absensi kelompok secara multiple sesuai kelompok
                                    bimbingan.</p>
                                <a href="{{ url('pembekalan/absensi/input') }}" class="btn btn-sm btn-info">Buka Modul</a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3 d-flex">
                            <div class="border rounded p-3 w-100 h-100">
                                <h6 class="mb-2">Riwayat Absensi</h6>
                                <p class="text-muted small mb-3">Lihat riwayat absensi siswa dengan filter pembimbing dan
                                    kelompok.</p>
                                <a href="{{ url('pembekalan/absensi/riwayat') }}" class="btn btn-sm btn-outline-info">Buka
                                    Riwayat</a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3 d-flex">
                            <div class="border rounded p-3 w-100 h-100">
                                <h6 class="mb-2">Input Catatan Sikap</h6>
                                <p class="text-muted small mb-3">Isi catatan sikap kelompok secara multiple pada modul
                                    sikap.</p>
                                <a href="{{ url('pembekalan/sikap/input') }}" class="btn btn-sm btn-warning">Buka
                                    Modul</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-3" id="evaluasi-siswa-pembimbing">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-clipboard-check mr-1 text-info"></i>Ringkasan Aktivitas Pembimbing
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <h6 class="mb-2">Status Operasional</h6>
                                <ul class="list-unstyled mb-0 text-muted small">
                                    <li class="mb-2">Kelompok bimbingan aktif: <strong>{{ $jumlahKelompok }}</strong>
                                    </li>
                                    <li class="mb-2">Siswa bimbingan terdata:
                                        <strong>{{ $jumlahSiswaBimbingan }}</strong>
                                    </li>
                                    <li class="mb-2">Tugas terkumpul:
                                        <strong>{{ $summaryPembimbing['tugas_terkumpul'] }}</strong>
                                    </li>
                                    <li>Total hadir pembekalan tercatat: <strong>{{ $summaryPembimbing['hadir'] }}</strong>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <h6 class="mb-2">Riwayat Penilaian</h6>
                                <p class="text-muted small mb-3">Gunakan halaman riwayat untuk meninjau catatan sikap dan
                                    absensi yang sudah tersimpan.</p>
                                <a href="{{ url('pembekalan/sikap/riwayat') }}"
                                    class="btn btn-sm btn-outline-warning mr-2 mb-1">Riwayat Sikap</a>
                                <a href="{{ url('pembekalan/absensi/riwayat') }}"
                                    class="btn btn-sm btn-outline-info mb-1">Riwayat Absensi</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('css')
    <style>
        #kelompok-pembimbing,
        #tugas-siswa-pembimbing,
        #evaluasi-siswa-pembimbing {
            scroll-margin-top: 90px;
        }

        .dashboard-anchor-nav .btn {
            margin-right: 6px;
        }

        .dashboard-metric-box {
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 1rem;
        }

        .dashboard-metric-box .inner {
            padding-right: 78px;
        }

        .dashboard-metric-box h3 {
            font-size: 1.35rem;
            line-height: 1.25;
        }

        .dashboard-mini-box {
            min-height: 120px;
        }

        .card-header h5 {
            font-weight: 600;
        }

        .kelompok-divider-row td {
            background: #f4f8ff;
            font-weight: 600;
            border-top: 2px solid #d6e4ff;
        }
    </style>
@endsection

@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const successMessage = @json(session('success'));
        const errorMessage = @json(session('error'));
        const errorMessages = @json($errors->all());

        if (successMessage) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: successMessage,
                timer: 1800,
                showConfirmButton: false
            });
        }

        if (errorMessage) {
            Swal.fire({
                icon: 'error',
                title: 'Terjadi kesalahan',
                text: errorMessage,
            });
        }

        if (errorMessages.length > 0) {
            Swal.fire({
                icon: 'error',
                title: 'Data belum valid',
                html: errorMessages.join('<br>'),
            });
        }
    </script>
@endsection
