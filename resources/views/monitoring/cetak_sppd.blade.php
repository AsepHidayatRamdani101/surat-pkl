@php
    $tanggalSuratDisplay = $tanggal_surat ?? '';
    $tanggalBerangkatDisplay = $tanggal_berangkat ?? '';

    try {
        if (!empty($tanggalSuratDisplay)) {
            $tanggalSuratDisplay = \Carbon\Carbon::parse($tanggalSuratDisplay)->translatedFormat('d F Y');
        }
    } catch (\Throwable $e) {
        $tanggalSuratDisplay = $tanggal_surat ?? '';
    }

    try {
        if (!empty($tanggalBerangkatDisplay)) {
            $tanggalBerangkatDisplay = \Carbon\Carbon::parse($tanggalBerangkatDisplay)->translatedFormat('d F Y');
        }
    } catch (\Throwable $e) {
        $tanggalBerangkatDisplay = $tanggal_berangkat ?? '';
    }
@endphp

@php
    $sppdLogo = null;
    $candidateLogoPaths = [
        public_path('LogoJabar.png'),
        public_path('logoJabar.png'),
        public_path('logo.png'),
        public_path('LogoJabar.PNG'),
    ];

    foreach ($candidateLogoPaths as $candidatePath) {
        if (!empty($candidatePath) && file_exists($candidatePath)) {
            $extension = strtolower(pathinfo($candidatePath, PATHINFO_EXTENSION));
            $mimeMap = [
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'svg' => 'image/svg+xml',
                'webp' => 'image/webp',
            ];
            $mimeType = $mimeMap[$extension] ?? 'image/png';
            $sppdLogo = 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($candidatePath));
            break;
        }
    }
@endphp

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>SPD A4 Landscape</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }

        @page {
            size: A4 landscape;
            margin: 15mm;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
        }

        td,
        th {
            border: 1px solid black;
            vertical-align: top;
            padding: 4px;
        }

        .no-border {
            border: none;
        }

        .header {
            text-align: center;
            font-weight: bold;
        }

        .kop-sppd-table {
            width: 100%;
            border: none;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .kop-logo {
            width: 20%;
            border: none;
            text-align: center;
            vertical-align: middle;
            padding: 0 8px 6px 0;
        }

        .kop-text {
            width: 80%;
            border: none;
            text-align: center;
            vertical-align: middle;
            padding: 4px 0;
        }

        .kop-text .instansi {
            font-size: 11px;
            font-weight: bold;
            line-height: 1.25;
        }

        .kop-text .nama-sekolah {
            font-size: 20px;
            font-weight: bold;
            margin-top: 2px;
        }

        .kop-text .alamat {
            font-size: 10px;
            margin-top: 2px;
        }

        .kop-text .kontak {
            font-size: 9px;
            margin-top: 2px;
        }
    </style>
</head>

<body>

    <table>
        <tr>
            <!-- Kolom 1 -->
            <td style="width: 50%;">
                <table class="kop-sppd-table">
                    <tr>
                        <td class="kop-logo">
                            @if (!empty($sppdLogo))
                                <img src="{{ $sppdLogo }}" alt="Logo Jawa Barat"
                                    style="width: 82px; max-width: 100%; height: auto; display: block; margin: 0 auto;">
                            @else
                                <div
                                    style="width: 82px; height: 82px; margin: 0 auto; border: 1px solid #000; display: block;">
                                    Logo</div>
                            @endif
                        </td>
                        <td class="kop-text">
                            <div class="instansi">
                                PEMERINTAH DAERAH PROVINSI JAWA BARAT<br>
                                DINAS PENDIDIKAN<br>
                                CABANG DINAS PENDIDIKAN WILAYAH XI
                            </div>
                            <div class="nama-sekolah">SMK NEGERI 8 GARUT</div>
                            <div class="alamat">JL. RAYA LIMBANGAN-SELAWI KM 12 GARUT</div>
                            <div class="kontak">Website: www.smkn8-garut.sch.id, E-mail: smknegeri8grt@gmail.com</div>
                        </td>
                    </tr>
                </table>

                <hr style="border: 1px solid black; margin: 10px 0;">
                <div>
                    <table style="border: none; border-collapse: collapse;width: 30%">
                        <tr>
                            <td style="border: none;">Lembar Ke
                            </td>
                            <td style="border: none;">: ...................</td>
                        </tr>
                        <tr>
                            <td style="border: none;">Kode No
                            </td>
                            <td style="border: none;">: ...................</td>
                        </tr>
                        <tr>
                            <td style="border: none;"> Nomor
                            </td>
                            <td style="border: none;">: ...................</td>
                        </tr>
                    </table>
                </div>
                <div style="text-align: center;">
                    <u><b>SURAT PERJALANAN DINAS (SPD)</b></u>
                </div>
                <br>

                <table style="width:100%;">

                    <tr>
                        <td style="width: 5%">1.</td>
                        <td style="width: 45%;">Pengguna Anggaran/Kuasa Pengguna Anggaran</td>
                        <td style="width: 30%;">: {{ $nama_kepala_sekolah }}</td>
                    </tr>
                    <tr>
                        <td>2.</td>
                        <td>Nama/NIP Pegawai yang melaksanakan perjalanan</td>
                        <td>: {{ $data->first()->pembimbing->nama_pembimbing }} / <br>
                            &nbsp;&nbsp;{{ $data->first()->pembimbing->nip_pembimbing }}</td>

                    </tr>
                    <tr>
                        <td>3.</td>
                        <td>a.Pangkat dan Golongan <br>b.Jabatan
                            <br>c.Tingkat Biaya Perjalanan Dinas
                        </td>
                        <td>: .................................................<br>:
                            {{ $data->first()->pembimbing->jabatan_pembimbing }}
                            <br>: .................................................
                        </td>
                    </tr>
                    <tr>
                        <td>4.</td>
                        <td>Maksud Perjalanan</td>
                        <td>: Penjemputan PKL</td>
                    </tr>
                    <tr>
                        <td>5.</td>
                        <td>Alat Angkut yang dipergunakan</td>
                        <td>: ................................................. </td>
                    </tr>
                    <tr>
                        <td>6.</td>
                        <td>a. Tempat berangkat <br>b. Tempat tujuan</td>
                        <td>: SMKN 8 Garut<br>: {{ $data->first()->perusahaan->nama_perusahaan }}</td>
                    </tr>
                    <tr>
                        <td>7.</td>
                        <td>Lamanya Perjalanan Dinas <br>a. Tanggal berangkat <br>b. Tanggal harus kembali / tiba di
                            tempat baru *)</td>
                        <td> <br>: {{ $tanggalBerangkatDisplay }} <br>:
                            .................................................</td>
                    </tr>
                    <tr>
                        <td>8.</td>
                        <td>Pembebanan Anggaran <br>a. Instansi <br>b. Akun</td>
                        <td><br>:SMKN 8 Garut<br>: .................................................
                        </td>
                    </tr>
                </table>

                <br>
                @include('partials.ttd_default', [
                    'ttdTanggal' => 'Dikeluarkan di : SMK Negeri 8 Garut',
                    'ttdLabel' => 'Pada Tanggal : ' . $tanggalSuratDisplay,
                    //buat dibawah tanggal tulisan kuasa pengguna anggaran
                    'ttdRole' => 'KUASA PENGGUNA ANGGARAN',
                
                    'ttdNama' => $nama_kepala_sekolah,
                    'ttdNip' => $nip_kepala_sekolah,
                    'ttdImage' => $nama_file_ttd,
                    'ttdContainerStyle' => 'margin-left: 240px; margin-top: 4px;',
                    'ttdAlign' => 'left',
                ])
            </td>

            <!-- Kolom 2 -->
            <td style="width: 50%;">
                <table style="width:100%;">
                    <tr>
                        <td style="width: 50%"></td>
                        <td>
                            <table style="border: none; border-collapse: collapse;">
                                <tr>
                                    <td style="border: none;width: 40%;">Berangkat dari</td>
                                    <td style="border: none;">: SMK Negeri 8 Garut</td>
                                </tr>
                                <tr>
                                    <td style="border: none;" colspan="2">(Tempat Kedudukan)</td>

                                </tr>
                                <tr>
                                    <td style="border: none;width: 40%;">Ke</td>
                                    <td style="border: none;">: {{ $data->first()->perusahaan->nama_perusahaan }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 50%;height: 130px;">
                            <table style="border: none; border-collapse: collapse;">
                                <tr>
                                    <td style="border: none;width: 40%;">II. Tiba di</td>
                                    <td style="border: none;">: {{ $data->first()->perusahaan->nama_perusahaan }}</td>
                                </tr>

                                <tr>
                                    <td style="border: none;width: 40%;">&nbsp;&nbsp;&nbsp;&nbsp;Pada
                                        Tanggal</td>
                                    <td style="border: none;">: </td>
                                </tr>
                                <tr>
                                    <td style="border: none;width: 40%;">&nbsp;&nbsp;&nbsp;&nbsp;Kepala</td>
                                    <td style="border: none;">: </td>
                                </tr>

                            </table>
                        </td>
                        <td>
                            <table style="border: none; border-collapse: collapse;">
                                <tr>
                                    <td style="border: none;width: 40%;">Berangkat dari</td>
                                    <td style="border: none;">: </td>
                                </tr>
                                <tr>
                                    <td style="border: none;width: 40%;">Ke</td>
                                    <td style="border: none;">: </td>
                                </tr>
                                <tr>
                                    <td style="border: none;width: 40%;">Pada Tanggal</td>
                                    <td style="border: none;">: </td>
                                </tr>
                                <tr>
                                    <td style="border: none;width: 40%;">Kepala</td>
                                    <td style="border: none;">: </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 50%;height: 150px;">
                            <table style="border: none; border-collapse: collapse;">
                                <tr>
                                    <td style="border: none;width: 40%;">II. Tiba di</td>
                                    <td style="border: none;">: </td>
                                </tr>

                                <tr>
                                    <td style="border: none;width: 40%;">&nbsp;&nbsp;&nbsp;&nbsp;Pada
                                        Tanggal</td>
                                    <td style="border: none;">: </td>
                                </tr>
                                <tr>
                                    <td style="border: none;width: 40%;">&nbsp;&nbsp;&nbsp;&nbsp;Kepala</td>
                                    <td style="border: none;">: </td>
                                </tr>

                            </table>
                        </td>
                        <td>
                            <table style="border: none; border-collapse: collapse;">
                                <tr>
                                    <td style="border: none;width: 40%;">Berangkat dari</td>
                                    <td style="border: none;">: </td>
                                </tr>
                                <tr>
                                    <td style="border: none;width: 40%;">Ke</td>
                                    <td style="border: none;">: </td>
                                </tr>
                                <tr>
                                    <td style="border: none;width: 40%;">Pada Tanggal</td>
                                    <td style="border: none;">: </td>
                                </tr>
                                <tr>
                                    <td style="border: none;width: 40%;">Kepala</td>
                                    <td style="border: none;">: </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <table style="border: none; border-collapse: collapse;">
                                <tr>
                                    <td style="border: none;width: 20%;">III. Tiba kembali di</td>
                                    <td style="border: none;">: SMKN 8 Garut</td>
                                </tr>
                                <tr>
                                    <td style="border: none;width: 20%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pada
                                        Tanggal</td>
                                    <td style="border: none;">: </td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="border: none;">
                                        <div style="margin-left: 17px;">
                                            Telah diperiksa dengan keterangan bahwa perjalanan tersebut atas perintahnya
                                            dan
                                            semata-mata
                                            untuk kepentingan jabatan dalam waktu yang sesingkat-singkatnya

                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="text-align: center;border: none;">
                                        @include('partials.ttd_default', [
                                            'ttdTanggal' => '',
                                            'ttdLabel' => 'KUASA PENGGUNA ANGGARAN',
                                            'ttdNama' => $nama_kepala_sekolah,
                                            'ttdNip' => $nip_kepala_sekolah,
                                            'ttdImage' => $nama_file_ttd,
                                            'ttdContainerStyle' => 'margin-top: 0;',
                                            'ttdAlign' => 'center',
                                        ])

                                    </td>

                                </tr>
                                <tr>
                                    <td colspan="2" style="border: none;">
                                        VI. PERHATIAN
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="border: none;">
                                        <div style="margin-left: 17px;">
                                            PA/KPA yang menerbitkan SPD, pegawai yang
                                            melakukan
                                            perjalanan dinas, para
                                            pejabat yang mengesahkan tanggal berangkat/tiba, serta bendahara pengeluaran
                                            bertanggung jawab berdasarkan peraturan-peraturan Keuangan Negara apabila
                                            Negara
                                            menderita rugi akibat kesalahan, kelalaian, dan kealpaannya.
                                        </div>
                                    </td>
                                </tr>

                            </table>

                        </td>

                    </tr>


                </table>
            </td>
        </tr>

    </table>
    <br>
    </td>
    </tr>
    </table>

</body>

</html>
