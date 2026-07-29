@extends('adminlte::page')

@section('title', 'Laporan Absensi Pembekalan')

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Laporan Absensi Pembekalan</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Laporan Absensi</li>
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
                <form method="GET" action="{{ route('pembekalan.laporan-absensi') }}" class="mb-3">
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
                            <label class="mb-1">Status</label>
                            <select name="status" class="form-control form-control-sm">
                                <option value="">Semua Status</option>
                                <option value="hadir" {{ $filters['status'] === 'hadir' ? 'selected' : '' }}>Hadir</option>
                                <option value="izin" {{ $filters['status'] === 'izin' ? 'selected' : '' }}>Izin</option>
                                <option value="alpa" {{ $filters['status'] === 'alpa' ? 'selected' : '' }}>Alpa</option>
                                <option value="sakit" {{ $filters['status'] === 'sakit' ? 'selected' : '' }}>Sakit
                                </option>
                                <option value="terlambat" {{ $filters['status'] === 'terlambat' ? 'selected' : '' }}>
                                    Terlambat</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="mb-1">Sesi</label>
                            <select name="sesi_absensi" class="form-control form-control-sm">
                                <option value="">Semua Sesi</option>
                                <option value="datang" {{ $filters['sesi_absensi'] === 'datang' ? 'selected' : '' }}>Datang
                                </option>
                                <option value="pulang" {{ $filters['sesi_absensi'] === 'pulang' ? 'selected' : '' }}>Pulang
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex">
                            <button type="submit" class="btn btn-sm btn-primary mr-1 w-100">Filter</button>
                            <a href="{{ route('pembekalan.laporan-absensi') }}"
                                class="btn btn-sm btn-outline-secondary w-100">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Summary --}}
        <div class="row mb-3" id="print-summary">
            <div class="col-md-3">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $summary['total_absensi'] }}</h3>
                        <p>Total Absensi</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-list"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $summary['hadir'] }}</h3>
                        <p>Hadir</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $summary['izin'] }}</h3>
                        <p>Izin</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $summary['alpa'] }}</h3>
                        <p>Alpa</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0" id="print-table">
            <div class="card-header bg-white">
                <h5 class="mb-0">Detail Absensi</h5>
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
                            <th style="width: 90px;">Status</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->tanggal_absensi)->format('d-m-Y') }}</td>
                                <td>
                                    <strong>{{ $item->siswa->nama_siswa ?? '-' }}</strong><br>
                                    <small class="text-muted">{{ $item->siswa->nis ?? '-' }}</small>
                                </td>
                                <td>{{ optional($item->siswa)->kelas?->nama_kelas ?? '-' }}</td>
                                <td>{{ $item->pembimbing->nama_pembimbing ?? '-' }}</td>
                                <td>{{ ucfirst($item->sesi_absensi ?? '-') }}</td>
                                <td>
                                    @php
                                        $badge =
                                            [
                                                'hadir' => 'success',
                                                'izin' => 'warning',
                                                'alpa' => 'danger',
                                                'sakit' => 'info',
                                                'terlambat' => 'primary',
                                            ][$item->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge badge-{{ $badge }}">{{ strtoupper($item->status) }}</span>
                                </td>
                                <td>{{ $item->keterangan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Tidak ada data absensi</td>
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
