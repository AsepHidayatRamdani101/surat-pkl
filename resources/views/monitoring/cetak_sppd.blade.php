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
    </style>
</head>

<body>

    <table>
        <tr>
            <!-- Kolom 1 -->
            <td style="width: 50%;">
                <table style="width: 100%; border: none;">
                    <tr>
                        <td style="width: 15%; border: none;">
                            <img src="{{ public_path('LogoJabar.png') }}" alt="Logo SMKN 8 Garut" style="width: 80px;">
                        </td>
                        <td style="width: 85%; border: none;">
                            <div class="header">
                                <h4>PEMERINTAH DAERAH PROVINSI JAWA BARAT <br>
                                    DINAS PENDIDIKAN <br>
                                    CABANG DINAS PENDIDIKAN WILAYAH XI</h4>
                                <h2 style="margin-top: -10px;margin-bottom: -5px "><strong>SMK NEGERI 8 GARUT</strong>
                                </h2>
                                <p style="text-size: 7pt">JL. RAYA LIMBANGAN-SELAWI KM 12 GARUT <br>
                                    <i>Website:</i><span style="color: blue">www.smkn8-garut.sch.id</span> ,
                                    <i>E-mail:</i>
                                    <span style="color: blue">smknegeri8grt@gmail.com</span> <br>
                                </p>
                            </div>
                        </td>
                    </tr>
                </table>
                <hr>
                <br>
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
                        <td style="width: 30%;">: Moh. Rofiq Zen, S.Pd., M.M.Pd</td>
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
                        <td>: Pengantaran dan Monitoring PKL</td>
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
                        <td> <br>: 1 September 2025 <br>: .................................................</td>
                    </tr>
                    <tr>
                        <td>8.</td>
                        <td>Pembebanan Anggaran <br>a. Instansi <br>b. Akun</td>
                        <td><br>:SMKN 8 Garut<br>: .................................................
                        </td>
                    </tr>
                </table>

                <br>
                <div style="margin-left: 240px">
                    <p>Dikeluarkan di : SMK Negeri 8 Garut<br>
                        Tanggal : 29 Agustus 2025<br>
                        KUASA PENGGUNA ANGGARAN<br><br><br><br><br>
                        <b>MOH. ROFIK ZEN, S.Pd., M.M.Pd</b><br>
                        NIP. 196906131994121002
                    </p>
                </div>
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
                        <td style="width: 50%;height: 150px;">
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
                                        KUASA PENGGUNA ANGGARAN,
                                        <br><br><br><br>
                                        <b>MOH. ROFIK ZEN, S.Pd.,M.M.Pd</b> <br>
                                        NIP. 196906131994121002

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
