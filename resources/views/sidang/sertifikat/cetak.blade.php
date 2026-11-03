<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Hasil Sidang PKL</title>
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
            background: #1d4ed8;
            color: #ffffff;
            border-color: #1d4ed8;
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
            padding: 28px 56px 22px;
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
            width: 68px;
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
            margin-top: 14px;
        }

        .title {
            font-size: 40px;
            letter-spacing: 9px;
            color: #b45309;
            margin: 0;
            font-weight: bold;
        }

        .subtitle {
            font-size: 14px;
            letter-spacing: 4px;
            color: #374151;
            text-transform: uppercase;
            margin: 4px 0 0;
        }

        .nomor {
            font-size: 12px;
            color: #6b7280;
            margin: 5px 0 0;
        }

        .body-block {
            text-align: center;
            margin-top: 10px;
        }

        .diberikan {
            font-size: 13px;
            font-style: italic;
            color: #4b5563;
            margin: 0;
        }

        .nama-siswa {
            font-size: 32px;
            font-weight: bold;
            color: #111827;
            margin: 5px auto 0;
            padding-bottom: 5px;
            border-bottom: 2px solid #b45309;
            display: inline-block;
            min-width: 420px;
        }

        .identitas {
            font-size: 13px;
            color: #4b5563;
            margin: 7px 0 0;
        }

        .pernyataan {
            font-size: 13px;
            color: #374151;
            max-width: 740px;
            margin: 8px auto 0;
            line-height: 1.45;
        }

        .hasil-utama {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 12px;
            align-items: stretch;
        }

        .nilai-besar {
            border: 1px solid #d6d3d1;
            border-top: 3px solid #1d4ed8;
            background: #ffffff;
            padding: 10px 28px;
            text-align: center;
        }

        .nilai-besar .label {
            font-size: 10px;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #6b7280;
            display: block;
        }

        .nilai-besar .angka {
            font-size: 34px;
            font-weight: bold;
            color: #1d4ed8;
            display: block;
            line-height: 1.1;
        }

        .nilai-besar .predikat {
            font-size: 13px;
            color: #b45309;
            font-weight: bold;
            display: block;
        }

        .rincian {
            border-collapse: collapse;
            font-size: 11.5px;
            min-width: 380px;
        }

        .rincian th,
        .rincian td {
            border: 1px solid #d6d3d1;
            padding: 4px 10px;
            text-align: left;
        }

        .rincian th {
            background: #eff6ff;
            color: #374151;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: .5px;
        }

        .rincian td.angka {
            text-align: center;
            font-weight: bold;
            color: #1d4ed8;
        }

        .footer {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .footer .cap-area {
            text-align: center;
        }

        .footer .cap-area img {
            width: 88px;
            opacity: .9;
        }

        .ttd-block {
            text-align: center;
            font-size: 13px;
            color: #1f2937;
            min-width: 280px;
        }

        .ttd-block .ttd-img {
            height: 54px;
            margin: 3px 0;
        }

        .ttd-block .ttd-space {
            height: 54px;
        }

        .ttd-block .nama-kepsek {
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
        }

        .ttd-block .nip {
            font-size: 12px;
            color: #4b5563;
            margin: 2px 0 0;
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
        <a class="btn btn-primary"
            href="{{ auth()->user()->role === 'siswa' ? route('sidang.sertifikat.download') : route('sidang.sertifikat.download', $siswa->id) }}">
            Download PDF
        </a>
        <button class="btn" onclick="window.print()">Print</button>
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

                    <div class="hasil-utama">
                        <div class="nilai-besar">
                            <span class="label">Nilai Akhir</span>
                            <span class="angka">{{ rtrim(rtrim(number_format($sertifikat->nilai_akhir, 2), '0'), '.') }}</span>
                            <span class="predikat">{{ $sertifikat->predikat }}</span>
                        </div>
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
                    </div>
                </div>

                <div class="footer">
                    <div class="cap-area">
                        @if ($capPath)
                            <img src="{{ asset($capPath) }}" alt="Cap SMKN 8 Garut">
                        @endif
                    </div>
                    <div class="ttd-block">
                        <div>Garut, {{ $sertifikat->generated_at->locale('id')->translatedFormat('d F Y') }}</div>
                        <div>Kepala SMK Negeri 8 Garut,</div>
                        @if ($ttdPath)
                            <img class="ttd-img" src="{{ asset($ttdPath) }}" alt="Tanda tangan">
                        @else
                            <div class="ttd-space"></div>
                        @endif
                        <p class="nama-kepsek">{{ $kepalaSekolah }}</p>
                        <p class="nip">NIP. {{ $nipKepalaSekolah }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
