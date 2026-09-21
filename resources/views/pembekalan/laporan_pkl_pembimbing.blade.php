@extends('adminlte::page')

@section('plugins.Datatables', true)

@section('title', 'Laporan Pembekalan PKL')

@section('content_header')
    <h1>Laporan Pembekalan PKL</h1>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('pembekalan.laporan-pembekalan-pkl') }}" class="form-row align-items-end">
                <div class="col-md-4 form-group">
                    <label>Pembimbing</label>
                    <input type="text" class="form-control" value="{{ $pembimbing->nama_pembimbing }}" readonly>
                </div>
                <div class="col-md-3 form-group">
                    <label>Tanggal Awal</label>
                    <input type="date" name="tanggal_awal" class="form-control"
                        value="{{ $filters['tanggal_awal'] ?? '' }}">
                </div>
                <div class="col-md-3 form-group">
                    <label>Tanggal Akhir</label>
                    <input type="date" name="tanggal_akhir" class="form-control"
                        value="{{ $filters['tanggal_akhir'] ?? '' }}">
                </div>
                <div class="col-md-2 form-group d-flex">
                    <button type="submit" class="btn btn-primary mr-1 flex-fill">Filter</button>
                    <a href="{{ route('pembekalan.laporan-pembekalan-pkl') }}"
                        class="btn btn-outline-secondary flex-fill">Reset</a>
                </div>
            </form>
            <div class="mt-2">
                <a href="{{ route('pembekalan.laporan-pembekalan-pkl.export-absensi', request()->query()) }}"
                    class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel mr-1"></i>Export Absensi Pembekalan PKL
                </a>
                <a href="{{ route('pembekalan.laporan-pembekalan-pkl.export-nilai-tugas', request()->query()) }}"
                    class="btn btn-info btn-sm">
                    <i class="fas fa-file-excel mr-1"></i>Export Nilai Tugas Pembekalan PKL
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $summary['total_absensi'] }}</h3>
                    <p>Total Absensi Pembekalan</p>
                </div>
                <div class="icon"><i class="fas fa-user-check"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $summary['hadir'] }}</h3>
                    <p>Hadir</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $summary['total_nilai'] }}</h3>
                    <p>Nilai Tugas Pembekalan</p>
                </div>
                <div class="icon"><i class="fas fa-tasks"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $summary['rata_nilai'] }}</h3>
                    <p>Rata-rata Nilai</p>
                </div>
                <div class="icon"><i class="fas fa-star"></i></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Preview Absensi Pembekalan PKL</h5>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped table-sm" id="absensiTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Siswa</th>
                        <th>Kelas</th>
                        <th>Sesi</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($absensi as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->tanggal_absensi ? \Carbon\Carbon::parse($item->tanggal_absensi)->format('d-m-Y') : '-' }}
                            </td>
                            <td>{{ $item->siswa->nama_siswa ?? '-' }}</td>
                            <td>{{ $item->siswa->kelas->nama_kelas ?? '-' }}</td>
                            <td>{{ ucfirst($item->sesi_absensi ?? '-') }}</td>
                            <td>{{ strtoupper($item->status ?? '-') }}</td>
                            <td>{{ $item->keterangan ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Belum ada data absensi pembekalan PKL.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Preview Nilai Tugas Pembekalan PKL</h5>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped table-sm" id="nilaiTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal Dinilai</th>
                        <th>Siswa</th>
                        <th>Kelas</th>
                        <th>Tugas</th>
                        <th>Nilai</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($nilaiTugas as $index => $item)
                        @php($jawaban = $item->jawabanTugasSiswa)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->dinilai_at ? \Carbon\Carbon::parse($item->dinilai_at)->format('d-m-Y H:i') : '-' }}
                            </td>
                            <td>{{ $jawaban->siswa->nama_siswa ?? '-' }}</td>
                            <td>{{ $jawaban->siswa->kelas->nama_kelas ?? '-' }}</td>
                            <td>{{ $jawaban->tugasPembekalan->judul_tugas ?? '-' }}</td>
                            <td>{{ $item->nilai }}</td>
                            <td>{{ $item->catatan ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Belum ada data nilai tugas pembekalan PKL.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(function() {
            $('#absensiTable, #nilaiTable').DataTable({
                pageLength: 10,
                order: []
            });
        });
    </script>
@endsection
