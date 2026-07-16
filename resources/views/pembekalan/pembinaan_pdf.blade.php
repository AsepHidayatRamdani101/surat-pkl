<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Formulir Pembinaan Peserta PKL</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 11mm 10mm 11mm;
        }

        body {
            font-family: "Bookman Old Style", "Times New Roman", serif;
            color: #444;
            font-size: 12px;
            margin: 0;
            line-height: 1.25;
        }

        .frm-title-block {
            text-align: center;
            margin: 6px 0 14px;
        }

        .frm-title {
            color: #16294b;
            font-weight: 700;
            font-size: 19px;
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
            margin-bottom: 12px;
        }

        .frm-meta-table td {
            border: 0;
            padding: 2px 3px;
        }

        .frm-meta-label {
            width: 165px;
            color: #16294b;
            font-weight: 700;
        }

        .frm-section-title {
            margin: 13px 0 7px;
            padding-bottom: 4px;
            border-bottom: 2px solid #c9a94e;
            color: #16294b;
            font-size: 14px;
            font-weight: 700;
        }

        .frm-subtitle {
            font-size: 10px;
            margin-bottom: 5px;
        }

        .frm-identity-table th,
        .frm-identity-table td,
        .frm-check-table th,
        .frm-check-table td,
        .frm-verify-table td,
        .frm-rekap-table th,
        .frm-rekap-table td {
            border: 1px solid #b9c2d0;
            padding: 5px 6px;
            vertical-align: middle;
            font-size: 11px;
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
            min-height: 58px;
            padding: 7px 8px;
            white-space: pre-line;
            font-size: 11px;
        }

        .frm-box.frm-box-tall {
            min-height: 78px;
        }

        .frm-checkbox {
            display: inline-block;
            width: 10px;
            height: 10px;
            border: 1px solid #1f3864;
            vertical-align: -1px;
            position: relative;
        }

        .frm-checkbox.checked::after {
            content: "";
            position: absolute;
            left: 2px;
            top: 0px;
            width: 3px;
            height: 6px;
            border: solid #1f3864;
            border-width: 0 1px 1px 0;
            transform: rotate(45deg);
        }

        .frm-check-line {
            margin-bottom: 4px;
            font-size: 11px;
        }

        .text-center {
            text-align: center;
        }

        .signature-inline {
            margin-top: 7px;
            font-size: 11px;
        }

        .signature-line {
            margin-top: 30px;
            font-weight: 700;
            text-decoration: underline;
            font-size: 11px;
        }

        .frm-verify-table td {
            background: #f7f8fa;
            width: 33.333%;
            vertical-align: top;
            height: 96px;
        }

        .verify-title {
            color: #16294b;
            font-weight: 700;
        }

        .verify-spacer {
            height: 46px;
        }

        .row-highlight td {
            background: #fff9e8;
        }
    </style>
</head>

<body>
    @include('pembekalan.partials.formulir_pembinaan_sheet')
</body>

</html>
