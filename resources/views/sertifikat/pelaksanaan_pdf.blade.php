<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Sertifikat Pelaksanaan PKL</title>
    <style>
        @if(!empty($customFontDataUri) && !empty($customFontName)) @font-face { font-family: '{{ $customFontName }}'; src: url('{{ $customFontDataUri }}'); } @endif
        @page {
            size: A4 landscape;
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: {{ $fontFamily ?? 'Georgia, "Times New Roman", serif' }};
            color: #1f2937;
            background: #fffdf7;
            @if(!empty($backgroundFrontDataUri ?? $backgroundDataUri)) background-image: url('{{ $backgroundFrontDataUri ?? $backgroundDataUri }}'); background-size: cover; background-position: center; @endif
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
            font-size: 22pt;
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
            font-size: 10pt;
            color: #374151;
            width: 190mm;
            margin: 3mm auto 0;
            line-height: 1.6;
        }

        .pernyataan strong {
            color: #111827;
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
            width: 33%;
        }

        .ttd-block {
            text-align: center;
            font-size: 10pt;
            color: #1f2937;
        }

        .ttd-block .ttd-img {
            height: 16mm;
            margin: 1mm 0;
        }

        .ttd-block .ttd-space {
            height: 16mm;
        }

        .ttd-block .nama-ttd {
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
        }

        .ttd-block .jabatan {
            font-size: 8.5pt;
            color: #4b5563;
            margin: 1mm 0 0;
        }

        .cap-area {
            text-align: center;
        }

        .cap-area img {
            width: 26mm;
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
            <p class="subtitle">Pelaksanaan Praktik Kerja Lapangan</p>
            <p class="nomor">Nomor: SPL/{{ $siswa->nis }}/{{ $tanggalCetak->format('Y') }}</p>
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
                yang telah melaksanakan <strong>Praktik Kerja Lapangan (PKL)</strong>
                di <strong>{{ $perusahaan->nama_perusahaan }}</strong>@if ($perusahaan->alamat), {{ $perusahaan->alamat }}@endif,
                terhitung mulai tanggal
                <strong>{{ $tanggalMulai?->locale('id')->translatedFormat('d F Y') ?? '-' }}</strong>
                sampai dengan
                <strong>{{ $tanggalSelesai?->locale('id')->translatedFormat('d F Y') ?? '-' }}</strong>
                @if ($durasi)({{ $durasi }})@endif
                dengan baik dan penuh tanggung jawab.
                @if ($sertifikatSidang)
                    <br>Nilai Akhir: <strong>{{ rtrim(rtrim(number_format($sertifikatSidang->nilai_akhir, 2), '0'), '.') }}</strong>
                    &mdash; Predikat: <strong>{{ $sertifikatSidang->predikat }}</strong>
                @endif
            </p>
        </div>

        <div class="footer">
            <table class="footer-table">
                <tr>
                    <td>
                        <div class="ttd-block">
                            <div>&nbsp;</div>
                            <div>{{ $pemilikSamaPembimbing ? 'Pemilik/Pembimbing' : 'Pembimbing' }} {{ $perusahaan->nama_perusahaan }},</div>
                            <div class="ttd-space"></div>
                            <p class="nama-ttd">{{ $namaPembimbing ?? '____________________' }}</p>
                            <p class="jabatan">
                                @if ($nipPembimbing)
                                    NIP. {{ $nipPembimbing }}<br>
                                @endif
                                Pembimbing Perusahaan/Instansi
                            </p>
                        </div>
                    </td>
                    <td>
                        <div class="cap-area">
                            @if ($capPath)
                                <img src="{{ public_path($capPath) }}" alt="">
                            @endif
                            @unless ($pemilikSamaPembimbing)
                                <div class="ttd-block" style="margin-top: 2mm;">
                                    <div>Mengetahui,</div>
                                    <div>Pemilik Perusahaan/Instansi</div>
                                    <div class="ttd-space"></div>
                                    <p class="nama-ttd">{{ $namaPemilik ?? '____________________' }}</p>
                                    @if ($nipPemilik)
                                        <p class="jabatan">NIP. {{ $nipPemilik }}</p>
                                    @endif
                                </div>
                            @endunless
                        </div>
                    </td>
                    <td>
                        <div class="ttd-block">
                            <div>Garut, {{ $tanggalCetak->translatedFormat('d F Y') }}</div>
                            <div>Kepala SMK Negeri 8 Garut,</div>
                            @if ($ttdPath)
                                <img class="ttd-img" src="{{ public_path($ttdPath) }}" alt="">
                            @else
                                <div class="ttd-space"></div>
                            @endif
                            <p class="nama-ttd">{{ $kepalaSekolah }}</p>
                            <p class="jabatan">NIP. {{ $nipKepalaSekolah }}</p>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
@include('sertifikat.partials.design_overlay')
@include('sertifikat.partials.halaman_nilai')
</body>

</html>
