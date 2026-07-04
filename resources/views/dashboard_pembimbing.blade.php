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
                            <small class="text-muted">Kelola progres bimbingan siswa, nilai tugas, absensi, dan catatan
                                sikap dari satu halaman.</small>
                        </div>
                        <div class="dashboard-anchor-nav">
                            <a href="#kelompok-pembimbing" class="btn btn-sm btn-outline-primary mb-1">Kelompok</a>
                            <a href="{{ url('pembekalan/jawaban-siswa') }}"
                                class="btn btn-sm btn-outline-success mb-1">Tugas & Nilai</a>
                            <a href="#evaluasi-siswa-pembimbing" class="btn btn-sm btn-outline-info mb-1">Absensi &
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

            <div class="card shadow-sm border-0 mb-3" id="kelompok-pembimbing">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-layer-group mr-1 text-primary"></i>Daftar Kelompok Bimbingan</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kelompok</th>
                                <th>Metode</th>
                                <th>Jumlah Siswa</th>
                                <th>Anggota</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($kelompok as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->nama_kelompok }}</td>
                                    <td>{{ ucfirst($item->metode) }}</td>
                                    <td>{{ $item->siswa_count }}</td>
                                    <td>
                                        @foreach ($item->siswa as $anggota)
                                            <div>{{ $anggota->nama_siswa }} - {{ $anggota->kelas->nama_kelas ?? '-' }}
                                            </div>
                                        @endforeach
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Belum ada kelompok bimbingan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-3" id="tugas-siswa-pembimbing">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-tasks mr-1 text-success"></i>Guru Melihat Tugas Siswa & Input Nilai
                    </h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Siswa</th>
                                <th>Topik</th>
                                <th>Tugas</th>
                                <th>Jawaban Siswa</th>
                                <th>Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tugasSiswa as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->tanggal_bimbingan ? \Carbon\Carbon::parse($item->tanggal_bimbingan)->format('d-m-Y') : '-' }}
                                    </td>
                                    <td>
                                        <strong>{{ $item->siswa->nama_siswa ?? '-' }}</strong><br>
                                        <small>{{ $item->siswa->kelas->nama_kelas ?? '-' }}</small>
                                    </td>
                                    <td>{{ $item->topik_pembekalan ?? '-' }}</td>
                                    <td>{{ $item->tugas ?? '-' }}</td>
                                    <td>{{ $item->tugas_siswa ?? 'Belum dikumpulkan' }}</td>
                                    <td style="min-width: 180px;">
                                        <form action="{{ route('dashboard.pembimbing.update-nilai', $item->id) }}"
                                            method="POST" class="d-flex align-items-center">
                                            @csrf
                                            <input type="number" name="nilai_tugas"
                                                class="form-control form-control-sm mr-2" min="0" max="100"
                                                step="0.01" value="{{ old('nilai_tugas', $item->nilai_tugas) }}"
                                                required>
                                            <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Belum ada data tugas siswa pada sesi bimbingan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-3" id="evaluasi-siswa-pembimbing">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-clipboard-check mr-1 text-info"></i>Guru Input Absensi & Catatan
                        Sikap Siswa</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Siswa</th>
                                <th>Topik</th>
                                <th>Absensi</th>
                                <th>Penilaian Sikap</th>
                                <th>Catatan Sikap</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bimbinganPembimbing as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->tanggal_bimbingan ? \Carbon\Carbon::parse($item->tanggal_bimbingan)->format('d-m-Y') : '-' }}
                                    </td>
                                    <td>
                                        <strong>{{ $item->siswa->nama_siswa ?? '-' }}</strong><br>
                                        <small>{{ $item->siswa->kelas->nama_kelas ?? '-' }}</small>
                                    </td>
                                    <td>{{ $item->topik_pembekalan ?? '-' }}</td>
                                    <td style="min-width: 150px;">
                                        <select name="status_absensi" form="evaluasi-form-{{ $item->id }}"
                                            class="form-control form-control-sm mb-2" required>
                                            <option value="hadir"
                                                {{ old('status_absensi', $item->status_absensi) === 'hadir' ? 'selected' : '' }}>
                                                Hadir</option>
                                            <option value="izin"
                                                {{ old('status_absensi', $item->status_absensi) === 'izin' ? 'selected' : '' }}>
                                                Izin</option>
                                            <option value="alpa"
                                                {{ old('status_absensi', $item->status_absensi) === 'alpa' ? 'selected' : '' }}>
                                                Alpa</option>
                                        </select>
                                    </td>
                                    <td style="min-width: 160px;">
                                        <select name="penilaian_sikap" form="evaluasi-form-{{ $item->id }}"
                                            class="form-control form-control-sm">
                                            <option value="">- Pilih -</option>
                                            <option value="sangat_baik"
                                                {{ old('penilaian_sikap', $item->penilaian_sikap) === 'sangat_baik' ? 'selected' : '' }}>
                                                Sangat Baik</option>
                                            <option value="baik"
                                                {{ old('penilaian_sikap', $item->penilaian_sikap) === 'baik' ? 'selected' : '' }}>
                                                Baik</option>
                                            <option value="cukup"
                                                {{ old('penilaian_sikap', $item->penilaian_sikap) === 'cukup' ? 'selected' : '' }}>
                                                Cukup</option>
                                            <option value="kurang"
                                                {{ old('penilaian_sikap', $item->penilaian_sikap) === 'kurang' ? 'selected' : '' }}>
                                                Kurang</option>
                                        </select>
                                    </td>
                                    <td style="min-width: 220px;">
                                        <textarea name="catatan" form="evaluasi-form-{{ $item->id }}" rows="2"
                                            class="form-control form-control-sm" placeholder="Masukkan catatan sikap siswa...">{{ old('catatan', $item->catatan) }}</textarea>
                                    </td>
                                    <td>
                                        <form id="evaluasi-form-{{ $item->id }}"
                                            action="{{ route('dashboard.pembimbing.update-evaluasi', $item->id) }}"
                                            method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-info">Simpan</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">Belum ada data sesi bimbingan untuk evaluasi.
                                    </td>
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
