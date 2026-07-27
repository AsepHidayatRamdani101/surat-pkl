@extends('adminlte::page')

@section('title', 'Laporan Kelengkapan Pembekalan')

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Laporan Kelengkapan Pembekalan</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Laporan Kelengkapan</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Filter Laporan</h5>
                    <button onclick="window.print()" class="btn btn-sm btn-primary">
                        <i class="fas fa-print mr-1"></i> Print
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('pembekalan.laporan-kelengkapan') }}" class="mb-3">
                    <div class="form-row align-items-end">
                        <div class="col-md-2">
                            <label class="mb-1">Dari</label>
                            <input type="date" name="tanggal_awal" class="form-control form-control-sm"
                                value="{{ $filters['tanggal_awal'] }}">
                        </div>
                        <div class="col-md-2">
                            <label class="mb-1">Sampai</label>
                            <input type="date" name="tanggal_akhir" class="form-control form-control-sm"
                                value="{{ $filters['tanggal_akhir'] }}">
                        </div>
                        <div class="col-md-2">
                            <label class="mb-1">Pembimbing</label>
                            <select name="pembimbing_id" class="form-control form-control-sm">
                                <option value="">Semua</option>
                                @foreach ($pembimbingOptions as $pembimbing)
                                    <option value="{{ $pembimbing->id }}"
                                        {{ (string) $filters['pembimbing_id'] === (string) $pembimbing->id ? 'selected' : '' }}>
                                        {{ $pembimbing->nama_pembimbing }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="mb-1">Sesi</label>
                            <select name="sesi_cek" class="form-control form-control-sm">
                                <option value="">Semua Sesi</option>
                                <option value="datang" {{ $filters['sesi_cek'] === 'datang' ? 'selected' : '' }}>Datang
                                </option>
                                <option value="pulang" {{ $filters['sesi_cek'] === 'pulang' ? 'selected' : '' }}>Pulang
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="mb-1">Status</label>
                            <select name="status_kelengkapan" class="form-control form-control-sm">
                                <option value="">Semua</option>
                                <option value="lengkap"
                                    {{ $filters['status_kelengkapan'] === 'lengkap' ? 'selected' : '' }}>Lengkap</option>
                                <option value="belum" {{ $filters['status_kelengkapan'] === 'belum' ? 'selected' : '' }}>
                                    Belum Lengkap</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex">
                            <button type="submit" class="btn btn-sm btn-primary mr-1 w-100">Filter</button>
                            <a href="{{ route('pembekalan.laporan-kelengkapan') }}"
                                class="btn btn-sm btn-outline-secondary w-100">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Summary --}}
        <div class="row mb-3" id="print-summary">
            <div class="col-md-4">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $summary['total_cek'] }}</h3>
                        <p>Total Cek</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-list"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $summary['lengkap'] }}</h3>
                        <p>Lengkap</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $summary['belum_lengkap'] }}</h3>
                        <p>Belum Lengkap</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0" id="print-table">
            <div class="card-header bg-white">
                <h5 class="mb-0">Detail Cek Kelengkapan</h5>
                <small class="text-muted">
                    {{ $summary['siswa_unik'] }} Siswa, {{ $summary['pembimbing_unik'] }} Pembimbing
                </small>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-bordered table-striped table-sm mb-0">
                    <thead>
                        <tr>
                            <th style="width: 40px;">No</th>
                            <th>Tanggal</th>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Pembimbing</th>
                            <th style="width: 80px;">Sesi</th>
                            <th style="width: 100px;">Status</th>
                            <th>Detail Kelengkapan</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->tanggal_cek)->format('d-m-Y') }}</td>
                                <td>
                                    <strong>{{ $item->siswa->nama_siswa ?? '-' }}</strong><br>
                                    <small class="text-muted">{{ $item->siswa->nis ?? '-' }}</small>
                                </td>
                                <td>{{ optional($item->siswa)->kelas?->nama_kelas ?? '-' }}</td>
                                <td>{{ $item->pembimbing->nama_pembimbing ?? '-' }}</td>
                                <td>{{ ucfirst($item->sesi_cek ?? '-') }}</td>
                                <td>
                                    @if ($item->is_lengkap)
                                        <span class="badge badge-success">LENGKAP</span>
                                    @else
                                        <span class="badge badge-warning">BELUM</span>
                                    @endif
                                </td>
                                <td>
                                    @if (is_array($item->item_checks) && count($item->item_checks) > 0)
                                        <small>
                                            @php
                                                $checked = collect($item->item_checks)->where('checked', true)->count();
                                                $total = count($item->item_checks);
                                            @endphp
                                            {{ $checked }}/{{ $total }} Item
                                        </small>
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
                                <td>{{ $item->catatan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">Tidak ada data cek kelengkapan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <style>
        @media print {

            .content-header,
            .card-header,
            .btn,
            form {
                display: none !important;
            }

            #print-summary,
            #print-table {
                page-break-inside: avoid;
            }
        }
    </style>
@endsection
