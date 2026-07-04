<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Pembekalan</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: "Helvetica", "Arial", sans-serif;
            background: #e5e7eb;
            color: #111827;
        }

        .print-actions {
            text-align: center;
            margin-bottom: 14px;
        }

        .btn {
            border: 1px solid #d1d5db;
            background: #ffffff;
            color: #111827;
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 0 4px;
        }

        .btn-primary {
            background: #0f766e;
            color: #ffffff;
            border-color: #0f766e;
        }

        .page {
            max-width: 1180px;
            aspect-ratio: 1.414/1;
            margin: 0 auto;
            position: relative;
        }

        .certificate-wrapper {
            width: 100%;
            height: 100%;
            background: #ffffff;
            border: 5px solid #e5e7eb;
            border-radius: 6px;
            padding: 34px 42px;
            position: relative;
            overflow: hidden;
        }

        .curve-mark {
            position: absolute;
            width: 340px;
            height: 340px;
            left: -70px;
            bottom: -210px;
            border: 18px solid #f3f4f6;
            border-radius: 50%;
        }

        .accent-bar {
            position: absolute;
            right: 24px;
            top: 26px;
            width: 22px;
            height: 170px;
        }

        .accent-1 {
            height: 57px;
            background: #f4b83f;
        }

        .accent-2 {
            height: 57px;
            background: #32c48d;
        }

        .accent-3 {
            height: 56px;
            background: #e43d95;
        }

        .certificate-content {
            position: relative;
            z-index: 1;
            width: 100%;
            height: 100%;
        }

        .left-col {
            width: 67%;
            float: left;
            padding-right: 24px;
        }

        .right-col {
            width: 30%;
            float: right;
            padding-top: 14px;
            padding-right: 22px;
        }

        .subtitle {
            letter-spacing: 1.3px;
            font-size: 14px;
            margin: 0;
            text-transform: uppercase;
            color: #0f766e;
            font-weight: 700;
        }

        .title {
            margin: 6px 0 14px;
            font-size: 42px;
            letter-spacing: 0.4px;
            color: #0f172a;
            line-height: 1.1;
        }

        .description {
            margin: 0;
            font-size: 21px;
            line-height: 1.35;
        }

        .student-name {
            margin: 28px 0 8px;
            font-size: 36px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: 0.3px;
        }

        .meta {
            margin: 5px 0;
            font-size: 18px;
            color: #374151;
        }

        .summary {
            margin: 24px 0 0;
            border-collapse: collapse;
            width: 100%;
        }

        .summary td {
            border: 1px solid #d1d5db;
            padding: 9px 12px;
            text-align: left;
            font-size: 16px;
        }

        .summary td:first-child {
            width: 68%;
            font-weight: 700;
            background: #eff6ff;
        }

        .right-brand {
            font-size: 52px;
            font-weight: 700;
            color: #0f766e;
            line-height: 1;
            margin-bottom: 8px;
        }

        .right-sub {
            font-size: 32px;
            color: #111827;
            line-height: 1.22;
            margin-bottom: 30px;
        }

        .motto {
            font-size: 18px;
            line-height: 1.4;
            color: #1f2937;
            margin-bottom: 14px;
        }

        .date-highlight {
            font-size: 20px;
            font-weight: 700;
            color: #0f766e;
            margin-bottom: 22px;
        }

        .qr-box {
            width: 90px;
            height: 90px;
            border: 2px solid #111827;
            font-size: 14px;
            text-align: center;
            line-height: 86px;
            margin-bottom: 8px;
        }

        .tag {
            font-size: 28px;
            font-weight: 700;
            color: #111827;
        }

        .footer {
            position: absolute;
            left: 42px;
            right: 42px;
            bottom: 32px;
        }

        .signature-left {
            float: left;
            width: 52%;
            font-size: 15px;
        }

        .signature-right {
            float: right;
            width: 32%;
            text-align: center;
            font-size: 15px;
        }

        .signature-line {
            margin-top: 56px;
            border-top: 1px solid #111827;
            padding-top: 8px;
            font-weight: 700;
        }

        .clearfix::after {
            content: "";
            display: block;
            clear: both;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }

            .print-actions {
                display: none;
            }

            .page {
                max-width: none;
                width: 100%;
                aspect-ratio: auto;
                margin: 0;
            }

            .certificate-wrapper {
                border-radius: 0;
                border-width: 0;
            }
        }
    </style>
</head>

<body>
    <div class="print-actions">
        <a class="btn btn-primary" href="{{ route('dashboard.siswa.download-sertifikat') }}">Download PDF</a>
        <button class="btn" onclick="window.print()">Print Preview</button>
        <a class="btn" href="{{ route('dashboard') }}">Kembali ke Dashboard</a>
    </div>

    <div class="page">
        <div class="certificate-wrapper">
            <div class="curve-mark"></div>
            <div class="accent-bar">
                <div class="accent-1"></div>
                <div class="accent-2"></div>
                <div class="accent-3"></div>
            </div>

            <div class="certificate-content clearfix">
                <div class="left-col">
                    <p class="subtitle">Sertifikat Resmi</p>
                    <h1 class="title">SERTIFIKAT PEMBEKALAN PKL</h1>
                    <p class="description">Sertifikat ini diberikan kepada peserta didik yang telah menyelesaikan
                        kegiatan pembekalan Praktik Kerja Lapangan.</p>

                    <div class="student-name">{{ strtoupper($siswa->nama_siswa) }}</div>
                    <p class="meta">NIS: {{ $siswa->nis }}</p>
                    <p class="meta">Kelas: {{ $siswa->kelas->nama_kelas ?? '-' }} | Jurusan:
                        {{ $siswa->kelas->jurusan->nama_jurusan ?? '-' }}</p>

                    <table class="summary">
                        <tr>
                            <td>Total Sesi Pembekalan</td>
                            <td>{{ $totalSesi }} sesi</td>
                        </tr>
                        <tr>
                            <td>Jumlah Kehadiran</td>
                            <td>{{ $hadir }} sesi</td>
                        </tr>
                        <tr>
                            <td>Persentase Kemajuan</td>
                            <td>{{ $progres }}%</td>
                        </tr>
                    </table>
                </div>

                <div class="right-col">
                    <div class="right-brand">PKL</div>
                    <div class="right-sub">Pembekalan<br>Kompetensi</div>
                    <div class="motto">Siap belajar, siap berkarya, siap berkontribusi di dunia kerja.</div>
                    <div class="date-highlight">{{ $tanggalCetak->translatedFormat('d F Y') }}</div>
                    <div class="qr-box">VALID</div>
                    <div class="tag">SMK</div>
                </div>
            </div>

            <div class="footer clearfix">
                <div class="signature-left">
                    <div>Ditetapkan pada tanggal {{ $tanggalCetak->translatedFormat('d F Y') }}</div>
                </div>
                <div class="signature-right">
                    <div>Panitia PKL</div>
                    <div class="signature-line">( __________________ )</div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
