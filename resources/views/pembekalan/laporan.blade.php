@extends('adminlte::page')

@section('title', 'Laporan Pembekalan')

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Laporan Pembekalan</h1>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid pt-3">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('pembekalan.laporan') }}">
                    <div class="form-row align-items-end">
                        <div class="col-md-4 mb-2">
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
                            <label class="mb-1">Tanggal Awal</label>
                            <input type="date" name="tanggal_awal" class="form-control form-control-sm"
                                value="{{ $filters['tanggal_awal'] }}">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="mb-1">Tanggal Akhir</label>
                            <input type="date" name="tanggal_akhir" class="form-control form-control-sm"
                                value="{{ $filters['tanggal_akhir'] }}">
                        </div>
                        <div class="col-md-2 mb-2 d-flex">
                            <button type="submit" class="btn btn-sm btn-primary mr-1 w-100">Filter</button>
                            <a href="{{ route('pembekalan.laporan') }}"
                                class="btn btn-sm btn-outline-secondary w-100">Reset</a>
                        </div>
                    </div>
                </form>
                <div class="mt-2">
                    <a href="{{ route('pembekalan.laporan.export-pdf', array_merge(request()->query(), ['stream' => 1])) }}"
                        class="btn btn-sm btn-primary" target="_blank">
                        <i class="fas fa-print mr-1"></i>Print Langsung
                    </a>
                    <a href="{{ route('pembekalan.laporan.export-excel', request()->query()) }}"
                        class="btn btn-sm btn-success">
                        <i class="fas fa-file-excel mr-1"></i>Export Excel
                    </a>
                    <a href="{{ route('pembekalan.laporan.export-pdf', request()->query()) }}"
                        class="btn btn-sm btn-danger">
                        <i class="fas fa-file-pdf mr-1"></i>Export PDF
                    </a>
                </div>
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
                        <h3>{{ $summary['total_siswa'] }}</h3>
                        <p>Siswa Aktif</p>
                    </div>
                    <div class="icon"><i class="fas fa-user-graduate"></i></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box bg-success shadow-sm">
                    <div class="inner">
                        <h3>{{ $summary['hadir'] }}</h3>
                        <p>Total Hadir</p>
                    </div>
                    <div class="icon"><i class="fas fa-user-check"></i></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box bg-warning shadow-sm">
                    <div class="inner">
                        <h3>{{ $summary['rata_nilai'] }}</h3>
                        <p>Rata-rata Nilai</p>
                    </div>
                    <div class="icon"><i class="fas fa-star"></i></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-7 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Rekap Pembekalan per Pembimbing</h5>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-striped table-sm mb-0 datatable-pembekalan">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Pembimbing</th>
                                    <th>Total Sesi</th>
                                    <th>Total Hadir</th>
                                    <th>Rata Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rekapPembimbing as $index => $row)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $row->nama_pembimbing ?? '-' }}</td>
                                        <td>{{ $row->total_sesi }}</td>
                                        <td>{{ $row->total_hadir }}</td>
                                        <td>{{ $row->rata_nilai }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Belum ada data rekap pembimbing.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-5 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">10 Siswa Terbaik Pembekalan</h5>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-striped table-sm mb-0 datatable-pembekalan">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Kelas</th>
                                    <th>Rata Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($topSiswa as $index => $row)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $row->nama_siswa ?? '-' }}</td>
                                        <td>{{ $row->nama_kelas ?? '-' }}</td>
                                        <td>{{ $row->rata_nilai }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Belum ada data top siswa.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h5 class="mb-0">Detail Sesi Pembekalan</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped table-sm mb-0 datatable-pembekalan">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Pembimbing</th>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Materi</th>
                            <th>Tugas</th>
                            <th>Absensi</th>
                            <th>Nilai</th>
                            <th>Sikap</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->tanggal_bimbingan ? \Carbon\Carbon::parse($item->tanggal_bimbingan)->format('d-m-Y') : '-' }}
                                </td>
                                <td>{{ $item->pembimbing->nama_pembimbing ?? '-' }}</td>
                                <td>{{ $item->siswa->nama_siswa ?? '-' }}</td>
                                <td>{{ $item->siswa->kelas->nama_kelas ?? '-' }}</td>
                                <td>{{ $item->topik_pembekalan ?? '-' }}</td>
                                <td>{{ $item->tugas ?? '-' }}</td>
                                <td>{{ $item->status_absensi ?? '-' }}</td>
                                <td>{{ $item->nilai_tugas ?? '-' }}</td>
                                <td>{{ $item->penilaian_sikap ? ucwords(str_replace('_', ' ', $item->penilaian_sikap)) : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">Belum ada data detail pembekalan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('plugins.Datatables', true)

@section('js')
    <script>
        $(function() {
            $('.datatable-pembekalan').DataTable({
                pageLength: 10,
                lengthChange: true,
                ordering: true,
                searching: true,
                responsive: true,
                autoWidth: false,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                }
            });
        });
    </script>
@endsection
