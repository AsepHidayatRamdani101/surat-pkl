@extends('adminlte::page')

@section('title', 'Dashboard Siswa')

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Dashboard Siswa</h1>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid pt-3">
        @if (!$siswa)
            <div class="callout callout-warning">
                Data siswa tidak ditemukan untuk akun ini. Pastikan username akun siswa sama dengan NIS di data siswa.
            </div>
        @else
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body py-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                        <div class="mb-2 mb-md-0">
                            <h5 class="mb-1">Selamat datang, {{ $siswa->nama_siswa }}</h5>
                            <small class="text-muted">Gunakan menu cepat berikut untuk melihat informasi pembekalan
                                PKL.</small>
                        </div>
                        <div class="dashboard-anchor-nav">
                            <a href="#absensi-siswa" class="btn btn-sm btn-outline-primary mb-1">Absensi</a>
                            <a href="#tugas-siswa" class="btn btn-sm btn-outline-success mb-1">Kerjakan Tugas</a>
                            <a href="#nilai-siswa" class="btn btn-sm btn-outline-warning mb-1">Nilai Tugas</a>
                            <a href="#sikap-siswa" class="btn btn-sm btn-outline-info mb-1">Catatan Sikap</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row dashboard-identity-row">
                <div class="col-md-4">
                    <div class="small-box bg-info shadow-sm dashboard-metric-box dashboard-identity-box">
                        <div class="inner">
                            <h3>{{ $siswa->nama_siswa }}</h3>
                            <p>Nama Siswa</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="small-box bg-success shadow-sm dashboard-metric-box dashboard-identity-box">
                        <div class="inner">
                            <h3>{{ $siswa->kelas->nama_kelas ?? '-' }}</h3>
                            <p>Kelas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chalkboard"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="small-box bg-warning shadow-sm dashboard-metric-box dashboard-identity-box">
                        <div class="inner">
                            <h3>{{ $siswa->kelas->jurusan->nama_jurusan ?? '-' }}</h3>
                            <p>Jurusan</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row dashboard-summary-row">
                <div class="col-md-3 col-6">
                    <div class="small-box bg-primary shadow-sm dashboard-metric-box dashboard-summary-box">
                        <div class="inner">
                            <h3>{{ $summary['total_sesi'] }}</h3>
                            <p>Total Sesi Pembekalan</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-6">
                    <div class="small-box bg-success shadow-sm dashboard-metric-box dashboard-summary-box">
                        <div class="inner">
                            <h3>{{ $summary['hadir'] }}</h3>
                            <p>Absensi Hadir</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-6">
                    <div class="small-box bg-warning shadow-sm dashboard-metric-box dashboard-summary-box">
                        <div class="inner">
                            <h3>{{ $summary['avg_nilai'] !== null ? $summary['avg_nilai'] : '-' }}</h3>
                            <p>Rata-rata Nilai Tugas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-6">
                    <div class="small-box bg-danger shadow-sm dashboard-metric-box dashboard-summary-box">
                        <div class="inner">
                            <h3>{{ $summary['progres'] }}%</h3>
                            <p>Kemajuan Pembekalan</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle mr-1 text-primary"></i>Ringkasan Informasi</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <strong>NIS:</strong> {{ $siswa->nis }}
                        </div>
                        <div class="col-md-4 mb-2">
                            <strong>Status Siswa:</strong> {{ $siswa->status }}
                        </div>
                        <div class="col-md-4 mb-2">
                            <strong>Surat Izin:</strong>
                            @if ($hasSuratIzin)
                                <span class="badge badge-success">Sudah Isi</span>
                            @else
                                <span class="badge badge-secondary">Belum Isi</span>
                            @endif
                        </div>
                        <div class="col-md-4 mb-2">
                            <strong>Tempat PKL:</strong>
                            @if ($hasTempatPkl)
                                <span class="badge badge-success">Sudah Pilih</span>
                            @else
                                <span class="badge badge-secondary">Belum Pilih</span>
                            @endif
                        </div>
                        <div class="col-md-4 mb-2">
                            <strong>Tugas Dikerjakan:</strong>
                            {{ $summary['tugas_selesai'] }} / {{ $summary['total_sesi'] }}
                        </div>
                        <div class="col-md-4 mb-2">
                            <strong>Penilaian Sikap Terakhir:</strong>
                            @if ($summary['latest_sikap'])
                                <span
                                    class="badge badge-info">{{ ucwords(str_replace('_', ' ', $summary['latest_sikap'])) }}</span>
                            @else
                                <span class="badge badge-secondary">Belum Dinilai</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-chart-line mr-1 text-success"></i>Grafik Kemajuan Selama Pembekalan
                        PKL</h5>
                </div>
                <div class="card-body">
                    @if (count($chartLabels) > 0)
                        <canvas id="progressChart" height="95"></canvas>
                    @else
                        <p class="text-muted mb-0">Data pembekalan belum tersedia.</p>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-3" id="absensi-siswa">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-user-check mr-1 text-primary"></i>Lihat Absensi</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Pembimbing</th>
                                <th>Topik Pembekalan</th>
                                <th>Absensi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bimbingan as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->tanggal_bimbingan ? \Carbon\Carbon::parse($item->tanggal_bimbingan)->format('d-m-Y') : '-' }}
                                    </td>
                                    <td>{{ $item->pembimbing->nama_pembimbing ?? '-' }}</td>
                                    <td>{{ $item->topik_pembekalan ?? '-' }}</td>
                                    <td>
                                        <span
                                            class="badge {{ $item->status_absensi === 'hadir' ? 'badge-success' : ($item->status_absensi === 'izin' ? 'badge-warning' : 'badge-danger') }}">
                                            {{ ucfirst($item->status_absensi ?? '-') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Belum ada data absensi pembekalan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-3" id="tugas-siswa">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-tasks mr-1 text-success"></i>Kerjakan Tugas</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Topik Pembekalan</th>
                                <th>Tugas</th>
                                <th>Jawaban Tugas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bimbingan->filter(fn ($row) => !empty($row->tugas))->values() as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->tanggal_bimbingan ? \Carbon\Carbon::parse($item->tanggal_bimbingan)->format('d-m-Y') : '-' }}
                                    </td>
                                    <td>{{ $item->topik_pembekalan ?? '-' }}</td>
                                    <td>{{ $item->tugas }}</td>
                                    <td style="min-width: 260px;">
                                        <form action="{{ route('dashboard.siswa.submit-tugas', $item->id) }}"
                                            method="POST">
                                            @csrf
                                            <textarea name="tugas_siswa" class="form-control mb-2" rows="2" placeholder="Ketik jawaban tugas...">{{ old('tugas_siswa', $item->tugas_siswa) }}</textarea>
                                            <button type="submit" class="btn btn-sm btn-primary">Simpan Tugas</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Belum ada tugas dari pembimbing.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-3" id="nilai-siswa">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-star mr-1 text-warning"></i>Lihat Nilai Tugas</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Topik Pembekalan</th>
                                <th>Tugas</th>
                                <th>Nilai Tugas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bimbingan->whereNotNull('nilai_tugas')->values() as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->tanggal_bimbingan ? \Carbon\Carbon::parse($item->tanggal_bimbingan)->format('d-m-Y') : '-' }}
                                    </td>
                                    <td>{{ $item->topik_pembekalan ?? '-' }}</td>
                                    <td>{{ $item->tugas ?? '-' }}</td>
                                    <td><span class="badge badge-success">{{ $item->nilai_tugas }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Nilai tugas belum tersedia.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-3" id="sikap-siswa">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-notes-medical mr-1 text-info"></i>Lihat Catatan Sikap</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Pembimbing</th>
                                <th>Penilaian Sikap</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bimbingan->filter(fn ($row) => !empty($row->penilaian_sikap) || !empty($row->catatan))->values() as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->tanggal_bimbingan ? \Carbon\Carbon::parse($item->tanggal_bimbingan)->format('d-m-Y') : '-' }}
                                    </td>
                                    <td>{{ $item->pembimbing->nama_pembimbing ?? '-' }}</td>
                                    <td>
                                        @if ($item->penilaian_sikap)
                                            <span
                                                class="badge badge-info">{{ ucwords(str_replace('_', ' ', $item->penilaian_sikap)) }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $item->catatan ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Catatan sikap belum tersedia.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('css')
    <style>
        #absensi-siswa,
        #tugas-siswa,
        #nilai-siswa,
        #sikap-siswa {
            scroll-margin-top: 90px;
        }

        .dashboard-anchor-nav .btn {
            margin-right: 6px;
        }

        .card-header h5 {
            font-weight: 600;
        }

        .dashboard-metric-box {
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 1rem;
        }

        .dashboard-metric-box .inner {
            padding: 16px;
            padding-right: 78px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .dashboard-metric-box h3 {
            font-size: 1.45rem;
            line-height: 1.25;
            margin-bottom: 6px;
        }

        .dashboard-metric-box p {
            margin-bottom: 0;
        }

        .dashboard-metric-box .icon {
            top: 10px;
            right: 14px;
            font-size: 54px;
        }

        .dashboard-identity-box {
            min-height: 132px;
        }

        .dashboard-identity-box h3 {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .dashboard-summary-box {
            min-height: 120px;
        }

        @media (max-width: 576px) {
            .dashboard-anchor-nav .btn {
                margin-bottom: 6px;
            }

            .dashboard-metric-box {
                min-height: 110px;
            }

            .dashboard-metric-box h3 {
                font-size: 1.2rem;
            }
        }
    </style>
@endsection

@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const successMessage = @json(session('success'));
        const errorMessage = @json(session('error'));

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

        const labels = @json($chartLabels ?? []);
        const chartData = @json($chartProgres ?? []);

        if (labels.length > 0) {
            const ctx = document.getElementById('progressChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Kemajuan Kehadiran (%)',
                        data: chartData,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.15)',
                        tension: 0.25,
                        fill: true,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });
        }
    </script>
@endsection
