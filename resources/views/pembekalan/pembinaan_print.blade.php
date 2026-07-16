@extends('adminlte::page')

@section('title', 'Formulir Pembinaan Peserta PKL')

@section('content-header')
    <div class="content-header no-print">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h1 class="m-0">Formulir Pembinaan Peserta PKL</h1>
            <div>
                <a href="{{ route('pembekalan.pembinaan.pdf', $record->id) }}" class="btn btn-sm btn-danger" target="_blank">
                    <i class="fas fa-file-pdf mr-1"></i> Print PDF
                </a>
                <button class="btn btn-sm btn-primary" onclick="window.print()">
                    <i class="fas fa-print mr-1"></i> Cetak Browser
                </button>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid pt-3">
        <div class="card shadow-sm border-0 formulir-card">
            <div class="card-body formulir-body">
                @include('pembekalan.partials.formulir_pembinaan_sheet')
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
        .frm-check-table,
        .frm-verify-table,
        .frm-rekap-table {
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
        }

        .frm-meta-label {
            width: 170px;
            color: #16294b;
            font-weight: 700;
        }

        .frm-section-title {
            margin: 15px 0 8px;
            padding-bottom: 5px;
            border-bottom: 2px solid #c9a94e;
            color: #16294b;
            font-size: 15px;
            font-weight: 700;
        }

        .frm-subtitle {
            font-size: 11px;
            margin-bottom: 6px;
        }

        .frm-identity-table th,
        .frm-identity-table td,
        .frm-check-table th,
        .frm-check-table td,
        .frm-verify-table td,
        .frm-rekap-table th,
        .frm-rekap-table td {
            border: 1px solid #b9c2d0;
            padding: 6px 8px;
            vertical-align: middle;
        }

        .frm-identity-table th,
        .frm-check-table th,
        .frm-rekap-table th {
            border-color: #1f3864;
            background: #eef2f8;
            color: #16294b;
            font-weight: 700;
        }

        .frm-check-table thead th,
        .frm-rekap-table thead th {
            text-align: center;
        }

        .frm-box {
            border: 1px solid #b9c2d0;
            min-height: 64px;
            padding: 8px 10px;
            white-space: pre-line;
        }

        .frm-box.frm-box-tall {
            min-height: 88px;
        }

        .frm-checkbox {
            display: inline-block;
            width: 11px;
            height: 11px;
            border: 1px solid #1f3864;
            vertical-align: -1px;
            position: relative;
        }

        .frm-checkbox.checked::after {
            content: "";
            position: absolute;
            left: 3px;
            top: 0px;
            width: 3px;
            height: 7px;
            border: solid #1f3864;
            border-width: 0 1.5px 1.5px 0;
            transform: rotate(45deg);
        }

        .frm-check-line {
            margin-bottom: 5px;
        }

        .text-center {
            text-align: center;
        }

        .signature-inline {
            margin-top: 8px;
        }

        .signature-line {
            margin-top: 36px;
            font-weight: 700;
            text-decoration: underline;
        }

        .frm-verify-table td {
            background: #f7f8fa;
            width: 33.333%;
            vertical-align: top;
            height: 102px;
        }

        .verify-title {
            color: #16294b;
            font-weight: 700;
        }

        .verify-spacer {
            height: 52px;
        }

        .row-highlight td {
            background: #fff9e8;
        }

        @media print {

            .no-print,
            .main-header,
            .main-sidebar,
            .main-footer {
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
            }
        }
    </style>
@endsection
