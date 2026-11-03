<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Pembekalan PKL</title>
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
            border: 3px solid #b45309;
            padding: 6px;
            position: relative;
        }

        .frame-inner {
            width: 100%;
            height: 100%;
            border: 1px solid #0f766e;
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
            border-color: #b45309;
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
            color: #0f766e;
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
            font-size: 36px;
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
            max-width: 720px;
            margin: 10px auto 0;
            line-height: 1.5;
        }

        .nilai-grid {
            display: flex;
            justify-content: center;
            gap: 14px;
            margin-top: 16px;
        }

        .nilai-box {
            width: 172px;
            border: 1px solid #d6d3d1;
            border-top: 3px solid #0f766e;
            background: #ffffff;
            padding: 10px 8px;
            text-align: center;
        }

        .nilai-box .label {
            font-size: 10px;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #6b7280;
            display: block;
        }

        .nilai-box .value {
            font-size: 22px;
            font-weight: bold;
            color: #0f766e;
            display: block;
            margin-top: 4px;
        }

        .nilai-box .detail {
            font-size: 11px;
            color: #6b7280;
            display: block;
            margin-top: 2px;
        }

        .footer {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .footer .cap-area {
            text-align: center;
            font-size: 11px;
            color: #9ca3af;
        }

        .footer .cap-area img {
            width: 90px;
            opacity: .9;
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
        <a class="btn btn-primary" href="{{ $downloadUrl ?? route('dashboard.siswa.download-sertifikat') }}">Download PDF</a>
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

                    <div class="nilai-grid">
                        <div class="nilai-box">
                            <span class="label">Kehadiran</span>
                            <span class="value">{{ $progres }}%</span>
                            <span class="detail">{{ $hadir }} dari {{ $totalSesi }} sesi</span>
                        </div>
                        <div class="nilai-box">
                            <span class="label">Tugas Selesai</span>
                            <span class="value">{{ $tugasSelesai }}/{{ $totalTugas }}</span>
                            <span class="detail">tugas pembekalan</span>
                        </div>
                        <div class="nilai-box">
                            <span class="label">Rata-rata Nilai</span>
                            <span class="value">{{ $avgNilai !== null ? $avgNilai : '-' }}</span>
                            <span class="detail">{{ $predikatNilai ?? 'belum dinilai' }}</span>
                        </div>
                        <div class="nilai-box">
                            <span class="label">Catatan Sikap</span>
                            <span class="value" style="font-size: 17px; margin-top: 8px;">{{ $sikap ?? '-' }}</span>
                            <span class="detail">selama pembekalan</span>
                        </div>
                    </div>
                </div>

                <div class="footer">
                    <div class="cap-area">
                        @if ($capPath)
                            <img src="{{ asset($capPath) }}" alt="Cap SMKN 8 Garut">
                        @endif
                    </div>
                    <div class="ttd-block">
                        <div>Garut, {{ $tanggalCetak->translatedFormat('d F Y') }}</div>
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
