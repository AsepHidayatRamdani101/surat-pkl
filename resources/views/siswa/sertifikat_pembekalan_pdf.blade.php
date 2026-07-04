<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Sertifikat Pembekalan PKL</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: "Helvetica", "Arial", sans-serif;
            color: #1f2937;
        }

        .page {
            width: 277mm;
            height: 190mm;
            margin: 10mm;
            border: 1mm solid #e5e7eb;
            padding: 10mm 12mm;
            position: relative;
            overflow: hidden;
        }

        .curve-mark {
            position: absolute;
            width: 112mm;
            height: 112mm;
            left: -20mm;
            bottom: -72mm;
            border: 5mm solid #f3f4f6;
            border-radius: 50%;
        }

        .accent-bar {
            position: absolute;
            right: 8mm;
            top: 10mm;
            width: 6mm;
            height: 46mm;
        }

        .accent-1 {
            height: 15.4mm;
            background: #f4b83f;
        }

        .accent-2 {
            height: 15.3mm;
            background: #32c48d;
        }

        .accent-3 {
            height: 15.3mm;
            background: #e43d95;
        }

        .layout {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            position: relative;
            z-index: 2;
        }

        .layout td {
            vertical-align: top;
        }

        .left-col {
            width: 69%;
            padding-right: 8mm;
        }

        .right-col {
            width: 31%;
            padding-right: 9mm;
        }

        .subtitle {
            margin: 0;
            font-size: 10pt;
            letter-spacing: 1.1pt;
            text-transform: uppercase;
            color: #0f766e;
            font-weight: 700;
        }

        .title {
            margin: 2.5mm 0 5mm;
            font-size: 22pt;
            line-height: 1.12;
            color: #0f172a;
        }

        .desc {
            margin: 0;
            font-size: 11.2pt;
            line-height: 1.35;
        }

        .student {
            margin: 6mm 0 1.8mm;
            font-size: 20pt;
            font-weight: 700;
            color: #111827;
        }

        .meta {
            margin: 0.8mm 0;
            font-size: 10.8pt;
            color: #374151;
        }

        .summary {
            width: 100%;
            margin-top: 6mm;
            border-collapse: collapse;
        }

        .summary td {
            border: 0.3mm solid #d1d5db;
            padding: 2.2mm 3.2mm;
            font-size: 10pt;
            text-align: left;
        }

        .summary td:first-child {
            width: 68%;
            font-weight: 700;
            background: #eff6ff;
        }

        .right-brand {
            font-size: 24pt;
            font-weight: 700;
            line-height: 1;
            color: #0f766e;
            margin: 1mm 0 2mm;
        }

        .right-sub {
            font-size: 13pt;
            line-height: 1.22;
            color: #111827;
            margin: 0 0 12mm;
        }

        .motto {
            font-size: 10pt;
            line-height: 1.35;
            margin: 0 0 3.5mm;
            color: #1f2937;
        }

        .date-highlight {
            font-size: 10.5pt;
            font-weight: 700;
            color: #0f766e;
            margin: 0 0 7mm;
        }

        .qr-box {
            width: 23mm;
            height: 23mm;
            border: 0.35mm solid #111827;
            text-align: center;
            line-height: 22mm;
            font-size: 8pt;
            margin-bottom: 2.5mm;
        }

        .tag {
            font-size: 11pt;
            font-weight: 700;
            color: #111827;
        }

        .footer {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            position: absolute;
            left: 12mm;
            right: 12mm;
            bottom: 10mm;
            z-index: 2;
        }

        .footer td {
            font-size: 9.8pt;
            color: #111827;
        }

        .footer-right {
            width: 35%;
            text-align: center;
        }

        .signature-name {
            margin-top: 12mm;
            border-top: 0.3mm solid #111827;
            padding-top: 1.6mm;
            font-weight: 700;
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="curve-mark"></div>

        <div class="accent-bar">
            <div class="accent-1"></div>
            <div class="accent-2"></div>
            <div class="accent-3"></div>
        </div>

        <table class="layout">
            <tr>
                <td class="left-col">
                    <p class="subtitle">Sertifikat Resmi</p>
                    <h1 class="title">SERTIFIKAT PEMBEKALAN PKL</h1>
                    <p class="desc">Sertifikat ini diberikan kepada peserta didik yang telah menyelesaikan kegiatan
                        pembekalan Praktik Kerja Lapangan.</p>

                    <div class="student">{{ strtoupper($siswa->nama_siswa) }}</div>
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
                </td>
                <td class="right-col">
                    <div class="right-brand">PKL</div>
                    <div class="right-sub">Pembekalan<br>Kompetensi</div>
                    <p class="motto">Siap belajar, siap berkarya, siap berkontribusi di dunia kerja.</p>
                    <p class="date-highlight">{{ $tanggalCetak->translatedFormat('d F Y') }}</p>
                    <div class="qr-box">VALID</div>
                    <div class="tag">SMK</div>
                </td>
            </tr>
        </table>

        <table class="footer">
            <tr>
                <td>Ditetapkan pada tanggal {{ $tanggalCetak->translatedFormat('d F Y') }}</td>
                <td class="footer-right">
                    <div>Panitia PKL</div>
                    <div class="signature-name">( __________________ )</div>
                </td>
            </tr>
        </table>
    </div>
    </div>
</body>

</html>
