@extends('adminlte::page')

@section('title', 'Data Pembekalan')

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Data Pembekalan</h1>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid pt-3">
        <div class="card app-card mb-3">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center my-1">
                    <span class="card-title-icon"><i class="fas fa-layer-group"></i></span>
                    <div>
                        <h4>Sub Menu Pembekalan</h4>
                        <small>Pantau materi, tugas, absensi, dan catatan sikap siswa pembekalan.</small>
                    </div>
                </div>
                <div class="my-1">
                    <a href="#materi-pembekalan" class="btn btn-sm btn-outline-primary mb-1">Materi</a>
                    <a href="#tugas-pembekalan" class="btn btn-sm btn-outline-success mb-1">Tugas</a>
                    <a href="#absensi-pembekalan" class="btn btn-sm btn-outline-info mb-1">Absensi</a>
                    <a href="#sikap-pembekalan" class="btn btn-sm btn-outline-warning mb-1">Catatan Sikap</a>
                </div>
            </div>
        </div>

        <div class="card app-card mb-3">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center my-1">
                    <span class="card-title-icon"><i class="fas fa-filter"></i></span>
                    <div>
                        <h4>Filter Data</h4>
                        <small>Saring data pembekalan berdasarkan pembimbing, kelas, dan tanggal</small>
                    </div>
                </div>
            </div>
            <div class="card-body py-3">
                <form method="GET" action="{{ route('pembekalan.index') }}">
                    <div class="form-row align-items-end">
                        <div class="col-md-3 mb-2">
                            <label class="mb-1">Pembimbing</label>
                            <select name="pembimbing_id" class="form-control form-control-sm">
                                <option value="">Semua Pembimbing</option>
                                @foreach ($pembimbingOptions as $pembimbing)
                                    <option value="{{ $pembimbing->id }}"
                                        {{ (string) $filters['pembimbing_id'] === (string) $pembimbing->id ? 'selected' : '' }}>
                                        {{ $pembimbing->nama_pembimbing }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="mb-1">Kelas</label>
                            <select name="kelas_id" class="form-control form-control-sm">
                                <option value="">Semua Kelas</option>
                                @foreach ($kelasOptions as $kelas)
                                    <option value="{{ $kelas->id }}"
                                        {{ (string) $filters['kelas_id'] === (string) $kelas->id ? 'selected' : '' }}>
                                        {{ $kelas->nama_kelas }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="mb-1">Tanggal Awal</label>
                            <input type="date" name="tanggal_awal" class="form-control form-control-sm"
                                value="{{ $filters['tanggal_awal'] }}">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="mb-1">Tanggal Akhir</label>
                            <input type="date" name="tanggal_akhir" class="form-control form-control-sm"
                                value="{{ $filters['tanggal_akhir'] }}">
                        </div>
                        <div class="col-md-2 mb-2 d-flex">
                            <button type="submit" class="btn btn-sm btn-primary mr-1 w-100">Filter</button>
                            <a href="{{ route('pembekalan.index') }}"
                                class="btn btn-sm btn-outline-secondary w-100">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-6">
                <div class="small-box bg-primary shadow-sm">
                    <div class="inner">
                        <h3>{{ $summary['total_sesi'] }}</h3>
                        <p>Total Sesi</p>
                    </div>
                    <div class="icon"><i class="fas fa-calendar-alt"></i></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box bg-info shadow-sm">
                    <div class="inner">
                        <h3>{{ $summary['materi'] }}</h3>
                        <p>Materi</p>
                    </div>
                    <div class="icon"><i class="fas fa-book-open"></i></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box bg-success shadow-sm">
                    <div class="inner">
                        <h3>{{ $summary['tugas'] }}</h3>
                        <p>Tugas</p>
                    </div>
                    <div class="icon"><i class="fas fa-tasks"></i></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box bg-warning shadow-sm">
                    <div class="inner">
                        <h3>{{ $summary['hadir'] }}</h3>
                        <p>Absensi Hadir</p>
                    </div>
                    <div class="icon"><i class="fas fa-user-check"></i></div>
                </div>
            </div>
        </div>

        <div class="card app-card mb-3" id="materi-pembekalan">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center my-1">
                    <span class="card-title-icon"><i class="fas fa-book-open"></i></span>
                    <div>
                        <h4>Materi Pembekalan</h4>
                        <small>Daftar materi/topik pembekalan</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-striped table-hover datatable-pembekalan" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Pembimbing</th>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Materi/Topik</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($materi as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->tanggal_bimbingan ? \Carbon\Carbon::parse($item->tanggal_bimbingan)->format('d-m-Y') : '-' }}
                                </td>
                                <td>{{ $item->pembimbing->nama_pembimbing ?? '-' }}</td>
                                <td>{{ $item->siswa->nama_siswa ?? '-' }}</td>
                                <td>{{ $item->siswa->kelas->nama_kelas ?? '-' }}</td>
                                <td>{{ $item->topik_pembekalan }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Belum ada data materi pembekalan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card app-card mb-3" id="tugas-pembekalan">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center my-1">
                    <span class="card-title-icon"><i class="fas fa-tasks"></i></span>
                    <div>
                        <h4>Tugas Pembekalan</h4>
                        <small>Daftar tugas dan jawaban siswa</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-striped table-hover datatable-pembekalan" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Siswa</th>
                            <th>Tugas</th>
                            <th>Jawaban</th>
                            <th>Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tugas as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->tanggal_bimbingan ? \Carbon\Carbon::parse($item->tanggal_bimbingan)->format('d-m-Y') : '-' }}
                                </td>
                                <td>{{ $item->siswa->nama_siswa ?? '-' }}</td>
                                <td>{{ $item->tugas }}</td>
                                <td>{{ $item->tugas_siswa ?? 'Belum dikerjakan' }}</td>
                                <td>{{ $item->nilai_tugas ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Belum ada data tugas pembekalan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card app-card mb-3" id="absensi-pembekalan">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center my-1">
                    <span class="card-title-icon"><i class="fas fa-user-check"></i></span>
                    <div>
                        <h4>Absensi Pembekalan</h4>
                        <small>Daftar kehadiran siswa pembekalan</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-striped table-hover datatable-pembekalan" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Siswa</th>
                            <th>Pembimbing</th>
                            <th>Status Absensi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($absensi as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->tanggal_bimbingan ? \Carbon\Carbon::parse($item->tanggal_bimbingan)->format('d-m-Y') : '-' }}
                                </td>
                                <td>{{ $item->siswa->nama_siswa ?? '-' }}</td>
                                <td>{{ $item->pembimbing->nama_pembimbing ?? '-' }}</td>
                                <td>{{ ucfirst($item->status_absensi) }}</td>
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

        <div class="card app-card mb-3" id="sikap-pembekalan">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center my-1">
                    <span class="card-title-icon"><i class="fas fa-user-graduate"></i></span>
                    <div>
                        <h4>Catatan Sikap Pembekalan</h4>
                        <small>Daftar penilaian sikap siswa</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-striped table-hover datatable-pembekalan" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Siswa</th>
                            <th>Penilaian Sikap</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sikap as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->tanggal_bimbingan ? \Carbon\Carbon::parse($item->tanggal_bimbingan)->format('d-m-Y') : '-' }}
                                </td>
                                <td>{{ $item->siswa->nama_siswa ?? '-' }}</td>
                                <td>{{ $item->penilaian_sikap ? ucwords(str_replace('_', ' ', $item->penilaian_sikap)) : '-' }}
                                </td>
                                <td>{{ $item->catatan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Belum ada data catatan sikap pembekalan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('css')
    @include('partials.table_styles')
    <style>
        #materi-pembekalan,
        #tugas-pembekalan,
        #absensi-pembekalan,
        #sikap-pembekalan {
            scroll-margin-top: 90px;
        }
    </style>
@endsection

@section('js')
    @include('partials.table_scripts')
    <script>
        $(function() {
            $('.datatable-pembekalan').DataTable({
                pageLength: 10,
                lengthChange: true,
                ordering: true,
                searching: true,
                responsive: true,
                autoWidth: false
            });
        });
    </script>
@endsection
