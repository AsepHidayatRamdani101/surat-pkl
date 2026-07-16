@extends('adminlte::page')

@section('title', 'Formulir Kehadiran Peserta Pembekalan PKL')

@section('content-header')
    <div class="content-header no-print">
        <div class="container-fluid d-flex flex-wrap align-items-center justify-content-between">
            <div>
                <h1 class="m-0">Formulir Kehadiran Peserta Pembekalan PKL</h1>
                <small class="text-muted">Lembar rujukan sebelum input absensi ke sistem.</small>
            </div>
            <div class="mt-2 mt-md-0">
                <a href="{{ route('pembekalan.absensi.formulir.pdf', ['pembimbing_id' => $filters['pembimbing_id'], 'kelompok_id' => $filters['kelompok_id'], 'tanggal_formulir' => $filters['tanggal_formulir']]) }}"
                    class="btn btn-sm btn-danger" target="_blank">
                    <i class="fas fa-file-pdf mr-1"></i> Print PDF
                </a>
                <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">
                    <i class="fas fa-print mr-1"></i> Cetak Formulir
                </button>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid pt-3">
        <div class="card shadow-sm border-0 mb-3 no-print">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('pembekalan.absensi.formulir') }}">
                    <div class="form-row align-items-end">
                        <div class="col-md-4 mb-2">
                            <label class="mb-1">Guru Pembimbing</label>
                            <select name="pembimbing_id" class="form-control form-control-sm" onchange="this.form.submit()">
                                <option value="">Semua Pembimbing</option>
                                @foreach ($pembimbingOptions as $pembimbing)
                                    <option value="{{ $pembimbing->id }}"
                                        {{ (string) $filters['pembimbing_id'] === (string) $pembimbing->id ? 'selected' : '' }}>
                                        {{ $pembimbing->nama_pembimbing }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="mb-1">Kelompok Bimbingan</label>
                            <select name="kelompok_id" class="form-control form-control-sm">
                                <option value="">Pilih Kelompok</option>
                                @foreach ($kelompokOptions as $kelompok)
                                    <option value="{{ $kelompok->id }}"
                                        {{ (string) $filters['kelompok_id'] === (string) $kelompok->id ? 'selected' : '' }}>
                                        {{ $kelompok->nama_kelompok }}
                                        @if ($kelompok->pembimbing)
                                            - {{ $kelompok->pembimbing->nama_pembimbing }}
                                        @endif
                                        ({{ $kelompok->siswa_count }} siswa)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="mb-1">Tanggal</label>
                            <input type="date" name="tanggal_formulir" class="form-control form-control-sm"
                                value="{{ $filters['tanggal_formulir'] }}">
                        </div>
                        <div class="col-md-2 mb-2 d-flex">
                            <button type="submit" class="btn btn-sm btn-primary mr-1 w-100">Tampilkan</button>
                            <a href="{{ route('pembekalan.absensi.formulir') }}"
                                class="btn btn-sm btn-outline-secondary w-100">Reset</a>
                        </div>
                    </div>
                </form>
                <div class="d-flex flex-wrap justify-content-end mt-2">
                    <a href="{{ route('pembekalan.absensi.formulir.pdf', ['pembimbing_id' => $filters['pembimbing_id'], 'kelompok_id' => $filters['kelompok_id'], 'tanggal_formulir' => $filters['tanggal_formulir']]) }}"
                        class="btn btn-sm btn-danger mr-2 mb-1" target="_blank">
                        <i class="fas fa-file-pdf mr-1"></i> Print PDF
                    </a>
                    <button type="button" class="btn btn-sm btn-primary mb-1" onclick="window.print()">
                        <i class="fas fa-print mr-1"></i> Cetak Halaman
                    </button>
                </div>
                @if (!$isPanitia)
                    <small class="text-muted">Filter pembimbing dikunci sesuai akun pembimbing yang sedang login.</small>
                @endif
            </div>
        </div>

        <div class="card shadow-sm border-0 formulir-card">
            <div class="card-body formulir-body">
                @include('pembekalan.partials.formulir_kehadiran_sheet')
            </div>
        </div>
    </div>
@endsection

@section('css')
    <style>
        .formulir-body {
            font-family: "Bookman Old Style", "Times New Roman", serif;
            color: #444;
            font-size: 12px;
            padding: 26px;
        }

        .formulir-card {
            border-radius: 8px;
        }

        .frm-title-block {
            text-align: center;
            margin: 6px 0 14px;
        }

        .frm-title {
            color: #16294b;
            font-weight: 700;
            font-size: 20px;
            line-height: 1.12;
        }

        .frm-meta-table,
        .frm-identity-table,
        .frm-attendance-table,
        .frm-violation-table,
        .frm-verify-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .frm-meta-table {
            margin-bottom: 14px;
        }

        .frm-meta-table td {
            border: 0;
            padding: 3px 4px;
            font-size: 12px;
        }

        .frm-meta-label {
            width: 170px;
            color: #16294b;
            font-weight: 700;
        }

        .frm-section-title {
            margin: 18px 0 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #c9a94e;
            color: #16294b;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: .3px;
        }

        .frm-section-title.section-identitas {
            margin-top: 12px;
            margin-bottom: 8px;
        }

        .frm-section-title.section-daftar-hadir {
            margin-top: 16px;
            margin-bottom: 8px;
        }

        .frm-section-title.section-catatan,
        .frm-section-title.section-pelanggaran,
        .frm-section-title.section-verifikasi,
        .frm-section-title.section-petunjuk {
            margin-top: 18px;
            margin-bottom: 8px;
        }

        .frm-identity-table th,
        .frm-identity-table td,
        .frm-attendance-table th,
        .frm-attendance-table td,
        .frm-violation-table th,
        .frm-violation-table td,
        .frm-verify-table td {
            border: 1px solid #b9c2d0;
            padding: 8px 10px;
            font-size: 12px;
            vertical-align: middle;
        }

        .frm-identity-table th,
        .frm-attendance-table th,
        .frm-violation-table th {
            border-color: #1f3864;
        }

        .frm-identity-table th {
            background: #eef2f8;
            color: #16294b;
            text-align: left;
            font-weight: 700;
        }

        .frm-attendance-table thead th,
        .frm-violation-table thead th {
            background: #1f3864;
            color: #fff;
            text-align: center;
            font-weight: 700;
        }

        .frm-identity-table th,
        .frm-identity-table td {
            height: 30px;
            padding: 7px 10px;
        }

        .frm-attendance-table thead th,
        .frm-violation-table thead th {
            height: 32px;
            padding: 6px 6px;
        }

        .frm-attendance-table tbody td,
        .frm-violation-table tbody td {
            height: 26px;
        }

        .frm-attendance-table tbody td {
            padding: 4px 6px;
        }

        .frm-violation-table tbody td {
            height: 28px;
            padding: 6px 8px;
        }

        .nis-cell {
            font-weight: 600;
            letter-spacing: .2px;
            white-space: nowrap;
        }

        .nis-fixed {
            display: inline-block;
            min-width: 74px;
            text-align: center;
            font-variant-numeric: tabular-nums;
            letter-spacing: .35px;
        }

        .frm-checkbox {
            display: inline-block;
            width: 11px;
            height: 11px;
            border: 1px solid #1f3864;
            background: #fff;
            vertical-align: -1px;
            margin-right: 4px;
            box-sizing: border-box;
        }

        .row-striped td {
            background: #f7f8fa;
        }

        .frm-lines {
            margin-top: 2px;
        }

        .frm-line {
            border-bottom: 1px solid #b9c2d0;
            height: 24px;
            margin-bottom: 6px;
        }

        .frm-verify-table td {
            background: #f7f8fa;
            height: 110px;
            padding: 10px 12px;
            vertical-align: top;
        }

        .verify-title {
            color: #16294b;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .verify-spacer {
            height: 58px;
        }

        .frm-note-list {
            margin: 8px 0 0;
            padding-left: 18px;
            font-size: 12px;
        }

        .frm-note-list li {
            margin-bottom: 6px;
            line-height: 1.45;
        }

        .frm-note-list li::marker {
            color: #c9a94e;
            font-size: 1.1em;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 12mm;
            }

            body {
                background: #fff !important;
            }

            .no-print,
            .main-header,
            .main-sidebar,
            .main-footer,
            .content-header,
            .breadcrumb,
            .btn,
            .card-header {
                display: none !important;
            }

            .content-wrapper,
            .content,
            .container-fluid,
            .card,
            .card-body {
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: 0 !important;
                background: #fff !important;
            }

            .formulir-card,
            .formulir-body {
                display: block !important;
                padding: 0 !important;
                border: 0 !important;
            }

            .frm-title {
                font-size: 15pt;
            }

            .frm-section-title.section-identitas {
                margin-top: 10px;
            }

            .frm-section-title.section-daftar-hadir,
            .frm-section-title.section-catatan,
            .frm-section-title.section-pelanggaran,
            .frm-section-title.section-verifikasi,
            .frm-section-title.section-petunjuk {
                margin-top: 14px;
                margin-bottom: 6px;
            }

            .frm-meta-table td,
            .frm-identity-table th,
            .frm-identity-table td,
            .frm-attendance-table th,
            .frm-attendance-table td,
            .frm-violation-table th,
            .frm-violation-table td,
            .frm-verify-table td,
            .frm-note-list {
                font-size: 11px;
            }

            .verify-spacer {
                height: 42px;
            }

            .frm-attendance-table tbody td,
            .frm-violation-table tbody td {
                height: 24px;
            }

            .frm-attendance-table thead th,
            .frm-violation-table thead th {
                height: 26px;
                padding: 4px;
            }

            .frm-identity-table th,
            .frm-identity-table td {
                height: 24px;
                padding: 4px 6px;
            }

            .frm-verify-table td {
                height: 88px;
                padding: 8px 10px;
            }
        }
    </style>
@endsection
