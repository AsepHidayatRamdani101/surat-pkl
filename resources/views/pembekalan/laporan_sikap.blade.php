@extends('adminlte::page')

@section('title', 'Laporan Catatan Sikap Pembekalan')

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Laporan Catatan Sikap Pembekalan</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Laporan Catatan Sikap</li>
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
                <form method="GET" action="{{ route('pembekalan.laporan-sikap') }}" class="mb-3">
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
                            <label class="mb-1">Nilai Sikap</label>
                            <select name="nilai_sikap" class="form-control form-control-sm">
                                <option value="">Semua</option>
                                <option value="sangat_baik"
                                    {{ $filters['nilai_sikap'] === 'sangat_baik' ? 'selected' : '' }}>Sangat Baik</option>
                                <option value="baik" {{ $filters['nilai_sikap'] === 'baik' ? 'selected' : '' }}>Baik
                                </option>
                                <option value="cukup" {{ $filters['nilai_sikap'] === 'cukup' ? 'selected' : '' }}>Cukup
                                </option>
                                <option value="perlu_bimbingan"
                                    {{ $filters['nilai_sikap'] === 'perlu_bimbingan' ? 'selected' : '' }}>Perlu Bimbingan
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="mb-1">Materi</label>
                            <select name="materi_id" class="form-control form-control-sm">
                                <option value="">Semua</option>
                                @foreach ($materiOptions as $materi)
                                    <option value="{{ $materi->id }}"
                                        {{ (string) $filters['materi_id'] === (string) $materi->id ? 'selected' : '' }}>
                                        {{ $materi->nama_materi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex">
                            <button type="submit" class="btn btn-sm btn-primary mr-1 w-100">Filter</button>
                            <a href="{{ route('pembekalan.laporan-sikap') }}"
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
                        <h3>{{ $summary['total_penilaian'] }}</h3>
                        <p>Total Penilaian</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-list"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $summary['sangat_baik'] }}</h3>
                        <p>Sangat Baik</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $summary['baik'] }}</h3>
                        <p>Baik</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-thumbs-up"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $summary['perlu_bimbingan'] }}</h3>
                        <p>Perlu Bimbingan</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0" id="print-table">
            <div class="card-header bg-white">
                <h5 class="mb-0">Detail Catatan Sikap</h5>
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
                            <th>Materi</th>
                            <th style="width: 100px;">Nilai Sikap</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->tanggal_penilaian)->format('d-m-Y') }}</td>
                                <td>
                                    <strong>{{ $item->siswa->nama_siswa ?? '-' }}</strong><br>
                                    <small class="text-muted">{{ $item->siswa->nis ?? '-' }}</small>
                                </td>
                                <td>{{ optional($item->siswa)->kelas?->nama_kelas ?? '-' }}</td>
                                <td>{{ $item->pembimbing->nama_pembimbing ?? '-' }}</td>
                                <td>{{ $item->materi->nama_materi ?? '-' }}</td>
                                <td>
                                    @php
                                        $badgeClass =
                                            [
                                                'sangat_baik' => 'success',
                                                'baik' => 'primary',
                                                'cukup' => 'warning',
                                                'perlu_bimbingan' => 'danger',
                                            ][$item->nilai_sikap] ?? 'secondary';

                                        $nilaiLabel =
                                            [
                                                'sangat_baik' => 'Sangat Baik',
                                                'baik' => 'Baik',
                                                'cukup' => 'Cukup',
                                                'perlu_bimbingan' => 'Perlu Bimbingan',
                                            ][$item->nilai_sikap] ?? '-';
                                    @endphp
                                    <span class="badge badge-{{ $badgeClass }}">{{ $nilaiLabel }}</span>
                                </td>
                                <td>{{ $item->catatan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Tidak ada data catatan sikap</td>
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
