<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Pelaksanaan PKL</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 24px;
            font-family: Georgia, "Times New Roman", serif;
            background: #e5e7eb;
            color: #1f2937;
        }

        .print-actions {
            text-align: center;
            margin-bottom: 16px;
            font-family: "Segoe UI", Arial, sans-serif;
        }

        .btn {
            border: 1px solid #d1d5db;
            background: #ffffff;
            color: #111827;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 0 4px;
            font-size: 14px;
        }

        .btn-primary {
            background: #0f766e;
            color: #ffffff;
            border-color: #0f766e;
        }

        .sheet {
            width: 1123px;
            height: 794px;
            margin: 0 auto;
            background: #fffdf7;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .25);
            padding: 18px;
            position: relative;
        }

        .frame {
            width: 100%;
            height: 100%;
            border: 3px solid #1d4ed8;
            padding: 6px;
            position: relative;
        }

        .frame-inner {
            width: 100%;
            height: 100%;
            border: 1px solid #b45309;
            padding: 30px 56px 24px;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .corner {
            position: absolute;
            width: 46px;
            height: 46px;
            border-color: #1d4ed8;
            border-style: solid;
            border-width: 0;
        }

        .corner.tl { top: 10px; left: 10px; border-top-width: 4px; border-left-width: 4px; }
        .corner.tr { top: 10px; right: 10px; border-top-width: 4px; border-right-width: 4px; }
        .corner.bl { bottom: 10px; left: 10px; border-bottom-width: 4px; border-left-width: 4px; }
        .corner.br { bottom: 10px; right: 10px; border-bottom-width: 4px; border-right-width: 4px; }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 440px;
            opacity: .05;
            pointer-events: none;
        }

        .header {
            text-align: center;
            position: relative;
        }

        .header .logo {
            position: absolute;
            left: 0;
            top: 0;
            width: 72px;
        }

        .header .instansi {
            font-size: 13px;
            letter-spacing: 2px;
            color: #4b5563;
            text-transform: uppercase;
            margin: 0;
        }

        .header .sekolah {
            font-size: 24px;
            font-weight: bold;
            color: #1d4ed8;
            letter-spacing: 1px;
            margin: 2px 0 0;
        }

        .header .alamat {
            font-size: 11px;
            color: #6b7280;
            margin: 2px 0 0;
        }

        .title-block {
            text-align: center;
            margin-top: 18px;
        }

        .title {
            font-size: 44px;
            letter-spacing: 10px;
            color: #b45309;
            margin: 0;
            font-weight: bold;
        }

        .subtitle {
            font-size: 15px;
            letter-spacing: 4px;
            color: #374151;
            text-transform: uppercase;
            margin: 4px 0 0;
        }

        .nomor {
            font-size: 12px;
            color: #6b7280;
            margin: 6px 0 0;
        }

        .body-block {
            text-align: center;
            margin-top: 14px;
        }

        .diberikan {
            font-size: 14px;
            font-style: italic;
            color: #4b5563;
            margin: 0;
        }

        .nama-siswa {
            font-size: 34px;
            font-weight: bold;
            color: #111827;
            margin: 6px auto 0;
            padding-bottom: 6px;
            border-bottom: 2px solid #b45309;
            display: inline-block;
            min-width: 440px;
        }

        .identitas {
            font-size: 13px;
            color: #4b5563;
            margin: 8px 0 0;
        }

        .pernyataan {
            font-size: 13px;
            color: #374151;
            max-width: 760px;
            margin: 10px auto 0;
            line-height: 1.6;
        }

        .pernyataan strong {
            color: #111827;
        }

        .footer {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .ttd-block {
            text-align: center;
            font-size: 13px;
            color: #1f2937;
            min-width: 280px;
        }

        .ttd-block .ttd-img {
            height: 58px;
            margin: 4px 0;
        }

        .ttd-block .ttd-space {
            height: 58px;
        }

        .ttd-block .nama-ttd {
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
        }

        .ttd-block .jabatan {
            font-size: 12px;
            color: #4b5563;
            margin: 2px 0 0;
        }

        .cap-area {
            text-align: center;
            font-size: 11px;
            color: #9ca3af;
        }

        .cap-area img {
            width: 90px;
            opacity: .9;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 0;
            }

            body {
                background: #ffffff;
                padding: 0;
            }

            .print-actions {
                display: none;
            }

            .sheet {
                width: 100%;
                height: 100vh;
                box-shadow: none;
                margin: 0;
            }
        }
    </style>
</head>

<body>
    <div class="print-actions">
        <a class="btn btn-primary" href="{{ $downloadUrl }}">Download PDF</a>
        <button class="btn" onclick="window.print()">Print</button>
        <a class="btn" href="{{ $backUrl ?? route('dashboard') }}">Kembali</a>
    </div>

    <div class="sheet">
        <div class="frame">
            <div class="corner tl"></div>
            <div class="corner tr"></div>
            <div class="corner bl"></div>
            <div class="corner br"></div>

            <div class="frame-inner">
                @if ($logoPath)
                    <img class="watermark" src="{{ asset($logoPath) }}" alt="">
                @endif

                <div class="header">
                    @if ($logoPath)
                        <img class="logo" src="{{ asset($logoPath) }}" alt="Logo SMKN 8 Garut">
                    @endif
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
                        di <strong>{{ $perusahaan->nama_perusahaan }}</strong>
                        @if ($perusahaan->alamat), {{ $perusahaan->alamat }}@endif,
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
                    <div class="ttd-block">
                        <div>&nbsp;</div>
                        <div>{{ $pemilikSamaPembimbing ? 'Pemilik/Pembimbing' : 'Pembimbing' }} {{ $perusahaan->nama_perusahaan }},</div>
                        <div class="ttd-space"></div>
                        <p class="nama-ttd">{{ $namaPembimbing ?? '____________________' }}</p>
                        <p class="jabatan">Pembimbing Perusahaan/Instansi</p>
                    </div>

                    <div class="cap-area">
                        @if ($capPath)
                            <img src="{{ asset($capPath) }}" alt="Cap SMKN 8 Garut">
                        @endif
                        @unless ($pemilikSamaPembimbing)
                            <div style="margin-top: 8px; font-size: 12px; color: #4b5563;">
                                Mengetahui,<br>Pemilik Perusahaan/Instansi<br><br><br>
                                <strong style="color:#1f2937; text-decoration: underline;">{{ $namaPemilik ?? '____________________' }}</strong>
                            </div>
                        @endunless
                    </div>

                    <div class="ttd-block">
                        <div>Garut, {{ $tanggalCetak->translatedFormat('d F Y') }}</div>
                        <div>Kepala SMK Negeri 8 Garut,</div>
                        @if ($ttdPath)
                            <img class="ttd-img" src="{{ asset($ttdPath) }}" alt="Tanda tangan">
                        @else
                            <div class="ttd-space"></div>
                        @endif
                        <p class="nama-ttd">{{ $kepalaSekolah }}</p>
                        <p class="jabatan">NIP. {{ $nipKepalaSekolah }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
