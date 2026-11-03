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
            font-family: Georgia, "Times New Roman", serif;
            color: #1f2937;
            background: #fffdf7;
        }

        .frame {
            position: absolute;
            top: 8mm;
            left: 8mm;
            right: 8mm;
            bottom: 8mm;
            border: 1.2mm solid #b45309;
        }

        .frame-inner {
            position: absolute;
            top: 2mm;
            left: 2mm;
            right: 2mm;
            bottom: 2mm;
            border: 0.3mm solid #0f766e;
        }

        .corner {
            position: absolute;
            width: 12mm;
            height: 12mm;
        }

        .corner.tl { top: 2.5mm; left: 2.5mm; border-top: 1.4mm solid #b45309; border-left: 1.4mm solid #b45309; }
        .corner.tr { top: 2.5mm; right: 2.5mm; border-top: 1.4mm solid #b45309; border-right: 1.4mm solid #b45309; }
        .corner.bl { bottom: 2.5mm; left: 2.5mm; border-bottom: 1.4mm solid #b45309; border-left: 1.4mm solid #b45309; }
        .corner.br { bottom: 2.5mm; right: 2.5mm; border-bottom: 1.4mm solid #b45309; border-right: 1.4mm solid #b45309; }

        .content {
            position: absolute;
            top: 14mm;
            left: 22mm;
            right: 22mm;
            bottom: 12mm;
        }

        .logo-kiri {
            position: absolute;
            top: 0;
            left: 0;
            width: 19mm;
        }

        .header {
            text-align: center;
        }

        .header .instansi {
            font-size: 9pt;
            letter-spacing: 1.5pt;
            color: #4b5563;
            text-transform: uppercase;
            margin: 0;
        }

        .header .sekolah {
            font-size: 17pt;
            font-weight: bold;
            color: #0f766e;
            letter-spacing: 1pt;
            margin: 1mm 0 0;
        }

        .header .alamat {
            font-size: 8pt;
            color: #6b7280;
            margin: 1mm 0 0;
        }

        .title-block {
            text-align: center;
            margin-top: 6mm;
        }

        .title {
            font-size: 30pt;
            letter-spacing: 7pt;
            color: #b45309;
            margin: 0;
            font-weight: bold;
        }

        .subtitle {
            font-size: 11pt;
            letter-spacing: 3pt;
            color: #374151;
            text-transform: uppercase;
            margin: 1.5mm 0 0;
        }

        .nomor {
            font-size: 8.5pt;
            color: #6b7280;
            margin: 2mm 0 0;
        }

        .body-block {
            text-align: center;
            margin-top: 4mm;
        }

        .diberikan {
            font-size: 10pt;
            font-style: italic;
            color: #4b5563;
            margin: 0;
        }

        .nama-siswa {
            font-size: 24pt;
            font-weight: bold;
            color: #111827;
            margin: 2mm auto 0;
            padding: 0 10mm 1.5mm;
            border-bottom: 0.6mm solid #b45309;
            display: inline-block;
        }

        .identitas {
            font-size: 9.5pt;
            color: #4b5563;
            margin: 2.5mm 0 0;
        }

        .pernyataan {
            font-size: 9.5pt;
            color: #374151;
            width: 180mm;
            margin: 3mm auto 0;
            line-height: 1.5;
        }

        .nilai-table {
            margin: 5mm auto 0;
            border-collapse: separate;
            border-spacing: 3mm 0;
        }

        .nilai-box {
            width: 44mm;
            border: 0.3mm solid #d6d3d1;
            border-top: 1mm solid #0f766e;
            background: #ffffff;
            padding: 3mm 2mm;
            text-align: center;
            vertical-align: top;
        }

        .nilai-box .label {
            font-size: 7pt;
            letter-spacing: 0.8pt;
            text-transform: uppercase;
            color: #6b7280;
            display: block;
        }

        .nilai-box .value {
            font-size: 15pt;
            font-weight: bold;
            color: #0f766e;
            display: block;
            margin-top: 1.5mm;
        }

        .nilai-box .value-kecil {
            font-size: 11.5pt;
        }

        .nilai-box .detail {
            font-size: 8pt;
            color: #6b7280;
            display: block;
            margin-top: 1mm;
        }

        .footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-table td {
            vertical-align: bottom;
        }

        .cap-area {
            text-align: left;
            padding-left: 10mm;
        }

        .cap-area img {
            width: 26mm;
        }

        .ttd-block {
            text-align: center;
            font-size: 10pt;
            color: #1f2937;
            width: 80mm;
        }

        .ttd-block .ttd-img {
            height: 16mm;
            margin: 1mm 0;
        }

        .ttd-block .ttd-space {
            height: 16mm;
        }

        .ttd-block .nama-kepsek {
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
        }

        .ttd-block .nip {
            font-size: 9pt;
            color: #4b5563;
            margin: 1mm 0 0;
        }
    </style>
</head>

<body>
    <div class="frame">
        <div class="frame-inner"></div>
        <div class="corner tl"></div>
        <div class="corner tr"></div>
        <div class="corner bl"></div>
        <div class="corner br"></div>
    </div>

    <div class="content">
        @if ($logoPath)
            <img class="logo-kiri" src="{{ public_path($logoPath) }}" alt="">
        @endif

        <div class="header">
            <p class="instansi">Pemerintah Daerah Provinsi Jawa Barat &mdash; Dinas Pendidikan</p>
            <p class="sekolah">SMK NEGERI 8 GARUT</p>
            <p class="alamat">Praktik Kerja Lapangan Tahun {{ $tanggalCetak->format('Y') }}</p>
        </div>

        <div class="title-block">
            <h1 class="title">SERTIFIKAT</h1>
            <p class="subtitle">Pembekalan Praktik Kerja Lapangan</p>
            <p class="nomor">Nomor: SPB/{{ $siswa->nis }}/{{ $tanggalCetak->format('Y') }}</p>
        </div>

        <div class="body-block">
            <p class="diberikan">diberikan kepada</p>
            <div class="nama-siswa">{{ strtoupper($siswa->nama_siswa) }}</div>
            <p class="identitas">
                NIS: {{ $siswa->nis }} &nbsp;|&nbsp;
                Kelas: {{ $siswa->kelas->nama_kelas ?? '-' }} &nbsp;|&nbsp;
                Jurusan: {{ $siswa->kelas->jurusan->nama_jurusan ?? '-' }}
            </p>
            <p class="pernyataan">
                atas partisipasi dan keberhasilannya dalam mengikuti kegiatan
                <strong>Pembekalan Praktik Kerja Lapangan (PKL)</strong>
                yang diselenggarakan oleh SMK Negeri 8 Garut, dengan hasil sebagai berikut:
            </p>

            <table class="nilai-table">
                <tr>
                    <td class="nilai-box">
                        <span class="label">Kehadiran</span>
                        <span class="value">{{ $progres }}%</span>
                        <span class="detail">{{ $hadir }} dari {{ $totalSesi }} sesi</span>
                    </td>
                    <td class="nilai-box">
                        <span class="label">Tugas Selesai</span>
                        <span class="value">{{ $tugasSelesai }}/{{ $totalTugas }}</span>
                        <span class="detail">tugas pembekalan</span>
                    </td>
                    <td class="nilai-box">
                        <span class="label">Rata-rata Nilai</span>
                        <span class="value">{{ $avgNilai !== null ? $avgNilai : '-' }}</span>
                        <span class="detail">{{ $predikatNilai ?? 'belum dinilai' }}</span>
                    </td>
                    <td class="nilai-box">
                        <span class="label">Catatan Sikap</span>
                        <span class="value value-kecil">{{ $sikap ?? '-' }}</span>
                        <span class="detail">selama pembekalan</span>
                    </td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <table class="footer-table">
                <tr>
                    <td class="cap-area">
                        @if ($capPath)
                            <img src="{{ public_path($capPath) }}" alt="">
                        @endif
                    </td>
                    <td align="right">
                        <div class="ttd-block">
                            <div>Garut, {{ $tanggalCetak->translatedFormat('d F Y') }}</div>
                            <div>Kepala SMK Negeri 8 Garut,</div>
                            @if ($ttdPath)
                                <img class="ttd-img" src="{{ public_path($ttdPath) }}" alt="">
                            @else
                                <div class="ttd-space"></div>
                            @endif
                            <p class="nama-kepsek">{{ $kepalaSekolah }}</p>
                            <p class="nip">NIP. {{ $nipKepalaSekolah }}</p>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>
