@extends('adminlte::page')

@section('title', $detailTitle)

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $detailTitle }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('pembekalan.absensi.riwayat') }}">Absensi Pembekalan</a></li>
                        <li class="breadcrumb-item active">{{ $detailTitle }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid pt-3">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">{{ $detailTitle }}</h5>
                        <small class="text-muted">{{ $detailDescription }}</small>
                    </div>
                    <a href="{{ route('pembekalan.absensi.riwayat') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                {{-- Filter Form --}}
                @if($detailType === 'siswa_belum' || $detailType === 'guru_belum')
                    <form method="GET" action="{{ route('pembekalan.absensi.detail') }}" class="mb-3">
                        <input type="hidden" name="type" value="{{ $detailType }}">
                        <div class="form-row align-items-end">
                            <div class="col-md-6">
                                <label class="mb-1">Cari Data</label>
                                <input type="text" name="keyword" class="form-control form-control-sm"
                                    placeholder="Cari berdasarkan nama atau NIP/NIS"
                                    value="{{ $filters['keyword'] }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-sm btn-primary w-100">Cari</button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('pembekalan.absensi.detail', ['type' => $detailType]) }}"
                                    class="btn btn-sm btn-outline-secondary w-100">Reset</a>
                            </div>
                        </div>
                    </form>
                @endif

                {{-- Detail Table --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                @if($detailType === 'siswa_absen' || $detailType === 'siswa_belum')
                                    <th>Nama Siswa</th>
                                    <th style="width: 120px;">NIS</th>
                                    <th style="width: 140px;">Kelas</th>
                                    <th style="width: 180px;">Kelompok Bimbingan</th>
                                @else
                                    <th>Nama Guru</th>
                                    <th style="width: 140px;">NIP</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($detailData as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    @if($detailType === 'siswa_absen' || $detailType === 'siswa_belum')
                                        <td>{{ $item->nama_siswa }}</td>
                                        <td>{{ $item->nis ?? '-' }}</td>
                                        <td>{{ optional($item->kelas)->nama_kelas ?? '-' }}</td>
                                        <td>
                                            @if($item->kelompokBimbingan)
                                                {{ $item->kelompokBimbingan->pluck('nama_kelompok')->join(', ') }}
                                            @else
                                                <span class="badge badge-warning">Tidak Ada</span>
                                            @endif
                                        </td>
                                    @else
                                        <td>{{ $item->nama_pembimbing }}</td>
                                        <td>{{ $item->nip_pembimbing ?? '-' }}</td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p>Tidak ada data {{ strtolower($detailTitle) }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Summary --}}
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="alert alert-info mb-0">
                            <strong>Total:</strong> {{ $detailData->count() }} 
                            @if($detailType === 'siswa_absen' || $detailType === 'siswa_belum')
                                siswa
                            @else
                                guru
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('plugins.Datatables', true)

@section('js')
    <script>
        $(function() {
            if ($.fn.DataTable) {
                $('table').DataTable({
                    pageLength: 25,
                    lengthChange: true,
                    ordering: true,
                    searching: true,
                    responsive: true,
                    autoWidth: false,
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
                        emptyTable: 'Tidak ada data'
                    }
                });
            }
        });
    </script>
@endsection
