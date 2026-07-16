<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Formulir Kehadiran Peserta Pembekalan PKL</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 11mm 10mm 11mm;
        }

        body {
            font-family: "Bookman Old Style", "Times New Roman", serif;
            color: #444;
            font-size: 12px;
            line-height: 1.25;
            margin: 0;
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
            margin-top: 15px;
            margin-bottom: 8px;
        }

        .frm-section-title.section-catatan,
        .frm-section-title.section-pelanggaran,
        .frm-section-title.section-verifikasi,
        .frm-section-title.section-petunjuk {
            margin-top: 17px;
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
            padding: 6px 7px;
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
            height: 26px;
            padding: 5px 8px;
        }

        .frm-attendance-table thead th,
        .frm-violation-table thead th {
            height: 28px;
            padding: 5px 5px;
        }

        .frm-attendance-table tbody td,
        .frm-violation-table tbody td {
            height: 22px;
        }

        .frm-attendance-table tbody td {
            padding: 3px 5px;
        }

        .frm-violation-table tbody td {
            height: 24px;
            padding: 5px 7px;
        }

        .nis-cell {
            font-weight: 600;
            letter-spacing: .2px;
            white-space: nowrap;
        }

        .nis-fixed {
            display: inline-block;
            min-width: 66px;
            text-align: center;
            font-variant-numeric: tabular-nums;
            letter-spacing: .35px;
        }

        .frm-checkbox {
            display: inline-block;
            width: 10px;
            height: 10px;
            border: 1px solid #1f3864;
            background: #fff;
            vertical-align: -1px;
            margin-right: 3px;
            box-sizing: border-box;
        }

        .text-center {
            text-align: center;
        }

        .row-striped td {
            background: #f7f8fa;
        }

        .frm-lines {
            margin-top: 2px;
        }

        .frm-line {
            border-bottom: 1px solid #b9c2d0;
            height: 22px;
            margin-bottom: 4px;
        }

        .frm-verify-table td {
            background: #f7f8fa;
            height: 100px;
            padding: 9px 10px;
            vertical-align: top;
        }

        .verify-title {
            color: #16294b;
            font-weight: 700;
            margin-bottom: 3px;
        }

        .verify-spacer {
            height: 54px;
        }

        .frm-note-list {
            margin: 8px 0 0;
            padding-left: 16px;
            font-size: 11px;
        }

        .frm-note-list li {
            margin-bottom: 5px;
            line-height: 1.35;
        }

        .frm-note-list li::marker {
            color: #c9a94e;
        }
    </style>
</head>

<body>
    @include('pembekalan.partials.formulir_kehadiran_sheet')
</body>

</html>
