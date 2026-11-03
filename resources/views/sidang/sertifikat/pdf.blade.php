<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Sertifikat Hasil Sidang PKL</title>
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
            border: 1.2mm solid #1d4ed8;
        }

        .frame-inner {
            position: absolute;
            top: 2mm;
            left: 2mm;
            right: 2mm;
            bottom: 2mm;
            border: 0.3mm solid #b45309;
        }

        .corner {
            position: absolute;
            width: 12mm;
            height: 12mm;
        }

        .corner.tl { top: 2.5mm; left: 2.5mm; border-top: 1.4mm solid #1d4ed8; border-left: 1.4mm solid #1d4ed8; }
        .corner.tr { top: 2.5mm; right: 2.5mm; border-top: 1.4mm solid #1d4ed8; border-right: 1.4mm solid #1d4ed8; }
        .corner.bl { bottom: 2.5mm; left: 2.5mm; border-bottom: 1.4mm solid #1d4ed8; border-left: 1.4mm solid #1d4ed8; }
        .corner.br { bottom: 2.5mm; right: 2.5mm; border-bottom: 1.4mm solid #1d4ed8; border-right: 1.4mm solid #1d4ed8; }

        .content {
            position: absolute;
            top: 13mm;
            left: 22mm;
            right: 22mm;
            bottom: 11mm;
        }

        .logo-kiri {
            position: absolute;
            top: 0;
            left: 0;
            width: 18mm;
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
            font-size: 16pt;
            font-weight: bold;
            color: #1d4ed8;
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
            margin-top: 4mm;
        }

        .title {
            font-size: 27pt;
            letter-spacing: 6pt;
            color: #b45309;
            margin: 0;
            font-weight: bold;
        }

        .subtitle {
            font-size: 10.5pt;
            letter-spacing: 3pt;
            color: #374151;
            text-transform: uppercase;
            margin: 1.5mm 0 0;
        }

        .nomor {
            font-size: 8.5pt;
            color: #6b7280;
            margin: 1.5mm 0 0;
        }

        .body-block {
            text-align: center;
            margin-top: 3mm;
        }

        .diberikan {
            font-size: 9.5pt;
            font-style: italic;
            color: #4b5563;
            margin: 0;
        }

        .nama-siswa {
            font-size: 21pt;
            font-weight: bold;
            color: #111827;
            margin: 1.5mm auto 0;
            padding: 0 10mm 1.2mm;
            border-bottom: 0.6mm solid #b45309;
            display: inline-block;
        }

        .identitas {
            font-size: 9pt;
            color: #4b5563;
            margin: 2mm 0 0;
        }

        .pernyataan {
            font-size: 9pt;
            color: #374151;
            width: 185mm;
            margin: 2mm auto 0;
            line-height: 1.45;
        }

        .hasil-table {
            margin: 3.5mm auto 0;
            border-collapse: separate;
            border-spacing: 4mm 0;
        }

        .nilai-besar {
            border: 0.3mm solid #d6d3d1;
            border-top: 1mm solid #1d4ed8;
            background: #ffffff;
            padding: 3mm 8mm;
            text-align: center;
            vertical-align: middle;
        }

        .nilai-besar .label {
            font-size: 7pt;
            letter-spacing: 0.8pt;
            text-transform: uppercase;
            color: #6b7280;
            display: block;
        }

        .nilai-besar .angka {
            font-size: 23pt;
            font-weight: bold;
            color: #1d4ed8;
            display: block;
            line-height: 1.1;
        }

        .nilai-besar .predikat {
            font-size: 9.5pt;
            color: #b45309;
            font-weight: bold;
            display: block;
        }

        .rincian-cell {
            vertical-align: middle;
        }

        .rincian {
            border-collapse: collapse;
            font-size: 8pt;
            min-width: 95mm;
        }

        .rincian th,
        .rincian td {
            border: 0.2mm solid #d6d3d1;
            padding: 1.2mm 3mm;
            text-align: left;
        }

        .rincian th {
            background: #eff6ff;
            color: #374151;
            text-transform: uppercase;
            font-size: 7pt;
            letter-spacing: 0.3pt;
        }

        .rincian td.angka {
            text-align: center;
            font-weight: bold;
            color: #1d4ed8;
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
            width: 24mm;
        }

        .ttd-block {
            text-align: center;
            font-size: 9.5pt;
            color: #1f2937;
            width: 80mm;
        }

        .ttd-block .ttd-img {
            height: 14mm;
            margin: 1mm 0;
        }

        .ttd-block .ttd-space {
            height: 14mm;
        }

        .ttd-block .nama-kepsek {
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
        }

        .ttd-block .nip {
            font-size: 8.5pt;
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
            <p class="alamat">Sidang Praktik Kerja Lapangan Tahun {{ $tanggalCetak->format('Y') }}</p>
        </div>

        <div class="title-block">
            <h1 class="title">SERTIFIKAT</h1>
            <p class="subtitle">Hasil Sidang Praktik Kerja Lapangan</p>
            <p class="nomor">Nomor: {{ $sertifikat->nomor_sertifikat }}</p>
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
                yang telah menyelesaikan <strong>Sidang Praktik Kerja Lapangan (PKL)</strong>
                di hadapan penguji SMK Negeri 8 Garut dan dinyatakan <strong>LULUS</strong>
                dengan rincian penilaian:
            </p>

            <table class="hasil-table">
                <tr>
                    <td class="nilai-besar">
                        <span class="label">Nilai Akhir</span>
                        <span class="angka">{{ rtrim(rtrim(number_format($sertifikat->nilai_akhir, 2), '0'), '.') }}</span>
                        <span class="predikat">{{ $sertifikat->predikat }}</span>
                    </td>
                    <td class="rincian-cell">
                        <table class="rincian">
                            <thead>
                                <tr>
                                    <th>Kriteria Penilaian</th>
                                    <th style="text-align: center;">Bobot</th>
                                    <th style="text-align: center;">Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rincian as $item)
                                    <tr>
                                        <td>{{ $item['nama'] }}</td>
                                        <td style="text-align: center;">{{ $item['bobot'] }}%</td>
                                        <td class="angka">
                                            {{ $item['nilai'] !== null ? rtrim(rtrim(number_format($item['nilai'], 2), '0'), '.') : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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
                            <div>Garut, {{ $sertifikat->generated_at->locale('id')->translatedFormat('d F Y') }}</div>
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
