<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Surat Permohonan Prakerin</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            padding: 30px;
        }

        .text-kop {
            text-align: center;
            margin-top: 20px;
            margin: 0;
        }




        hr {
            border: 1px solid black;
            margin: 10px 0;
        }

        .ttd {
            text-align: right;
            margin-top: 40px;
        }

        .table-bordered {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid black;
            padding: 5px;
            text-align: left;
        }

        .ttd-left {
            text-align: left;
            margin-top: 40px;
        }
    </style>
</head>

<body>

    <div style="page-break-after: always;">
        <div style="margin-top: -50px">
            <table>
                <tr>
                    <td><img src="{{ public_path('LogoJabar.png') }}" class="logo" alt="logo" width="100px"
                            style="margin-right: 20px"></td>
                    <td>
                        <div class="text-kop">
                            <h4>PEMERINTAH DAERAH PROVINSI JAWA BARAT <br>
                                DINAS PENDIDIKAN <br>
                                CABANG DINAS PENDIDIKAN WILAYAH XI</h4>
                            <h3 style="margin-top: -20px;margin-bottom: -15px "><strong>SMK NEGERI 8 GARUT</strong></h3>
                            <p style="text-size: 7pt">JL. RAYA LIMBANGAN-SELAWI KM 12 GARUT <br>
                                <i>Website:</i><span style="color: blue">www.smkn8-garut.sch.id</span> , <i>E-mail:</i>
                                <span style="color: blue">smknegeri8grt@gmail.com</span> <br>
                            </p>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <hr style="margin-top: -10px">


        <p>Nomor&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: 087/PK.03.03-SMKN8GRT<br>
            Lampiran&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: 1 (satu) berkas<br>
            Hal&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: Pengajuan
            Tempat
            Prakerin</p>

        <div style="text-align: justify">
            <p>Yth.<br>
                Pimpinan {{ $data->first()->perusahaan->nama_perusahaan }}<br>
                di Tempat</p>

            <p>Dengan hormat,</p>
            <p>Berdasarkan Program Kurikulum SMK Negeri 8 Garut yang mengharuskan setiap siswa SMK melaksanakan kegiatan
                Praktik Kerja Baik di Dunia Usaha, Dunia Industri, maupun Dunia Kerja,
                maka bersama surat ini kami mengajukan permohonan
                tempat pelaksanaan PRAKERIN bagi siswa kami mulai tanggal 1 September sampai 30 November 2025.</p>

            <p>Adapun nama siswa terlampir.</p>

            <p>Kami mengharapkan bantuan dari Bapak/Ibu Pimpinan {{ $data->first()->perusahaan->nama_perusahaan }} untuk
                meningkatkan kompetensi lulusan SMKN 8 Garut.
                Atas
                perhatian dan kerja sama Bapak/Ibu, kami ucapkan terima kasih.</p>
        </div>

        <div style="margin-left: 350px">
            Garut, 17 April 2025<br>
            Kepala Sekolah,<br>
            <img src="{{ public_path('ttd_kepsek.png') }}" class="ttd-left" alt="ttd_kepsek" width="180px"
                style="margin-top: -10px;margin-bottom: -20px"><br>
            <strong><u>MOH. ROFIK ZEN, S.Pd., M.M.Pd.</u></strong><br>
            NIP. 196906131994121002
        </div>

    </div>


    <div style="page-break-after: always;">

        <div style="margin-top: -50px">
            <table>
                <tr>
                    <td><img src="{{ public_path('LogoJabar.png') }}" class="logo" alt="logo" width="100px"
                            style="margin-right: 20px"></td>
                    <td>
                        <div class="text-kop">
                            <h4>PEMERINTAH DAERAH PROVINSI JAWA BARAT <br>
                                DINAS PENDIDIKAN <br>
                                CABANG DINAS PENDIDIKAN WILAYAH XI</h4>
                            <h2 style="margin-top: -20px;margin-bottom: -15px "><strong>SMK NEGERI 8 GARUT</strong></h2>
                            <p>JL. RAYA LIMBANGAN-SELAWI KM 12 GARUT <br>
                                <i>Website:</i><span style="color: blue">www.smkn8-garut.sch.id</span> , <i>E-mail:</i>
                                <span style="color: blue">smknegeri8grt@gmail.com</span> <br>
                            </p>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <hr style="margin-top: -10px">


        <p><i>lampiran I</i> <br>
            Pengajuan Tempat PRAKERIN <br>
            Nomor&nbsp;&nbsp;&nbsp;: 087/PK.03.03-SMKN8GRT <br>
            Tanggal : 17 April 2025</p>

        <h4 style="text-align: center">DAFTAR SISWA AJUAN TEMPAT PRAKERIN</h4>

        <table class="table-bordered">
            <thead>
                <tr>
                    <th style="text-align: center">NO</th>
                    <th style="text-align: center">NIS</th>
                    <th style="text-align: center">NAMA</th>
                    <th style="text-align: center">KELAS</th>
                    <th style="text-align: center">KONSENTRASI KEAHLIAN</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $index => $item)
                    <tr>
                        <td style="text-align: center">{{ $index + 1 }}</td>
                        <td style="text-align: center">{{ $item->siswa->nis }}</td>
                        <td>{{ $item->siswa->nama_siswa }}</td>
                        <td style="text-align: center">{{ $item->siswa->kelas->nama_kelas ?? '-' }}</td>
                        <td>{{ $item->siswa->kelas->jurusan->nama_jurusan ?? '-' }}</td>

                    </tr>
                @endforeach
            </tbody>
        </table>
        <br><br><br><br>
        <div style="margin-left: 350px">
            Garut, 17 April 2025<br>
            Kepala Sekolah,<br>
            <img src="{{ public_path('ttd_kepsek.png') }}" class="ttd-left" alt="ttd_kepsek" width="180px"
                style="margin-top: -10px;margin-bottom: -20px"><br>
            <strong><u>MOH. ROFIK ZEN, S.Pd., M.M.Pd.</u></strong><br>
            NIP. 196906131994121002
        </div>

    </div>

    <br><br>
    <h4 style="text-align: center">BUKTI PENERIMAAN SISWA PRAKERIN</h4>
    <p>Kami yang bertanda tangan di bawah ini:</p>

    <table>
        <tr>
            <td>Nama</td>
            <td>: ______________________________________________________________</td>
        </tr>
        <tr>
            <td>NIP</td>
            <td>: ______________________________________________________________</td>
        </tr>
        <tr>
            <td>Ka. Bag/Staff</td>
            <td>: ______________________________________________________________</td>
        </tr>

        <tr>
            <td>Alamat Perusahaan</td>
            <td>: ______________________________________________________________</td>
        </tr>
        <tr>
            <td></td>
            <td> &nbsp;&nbsp;______________________________________________________________</td>
        </tr>
        <tr>
            <td>No. Telp/HP</td>
            <td>: ______________________________________________________________</td>
        </tr>
    </table>

    <p style="text-align: justify">
        Menyatakan bahwa siswa-siswi SMKN 8 Garut yang diajukan untuk Prakerin di tempat kami, diterima sebanyak ______
        orang,
        dari tanggal 1 September sampai 30 November 2025.
    </p>

    <p>Dengan daftar siswa sebagai berikut:</p>

    <table class="table-bordered">
        <thead>
            <tr>
                <th style="text-align: center">NO</th>
                <th style="text-align: center">NIS</th>
                <th style="text-align: center">NAMA</th>
                <th style="text-align: center">KONSENTRASI KEAHLIAN</th>
                <th style="text-align: center">PEKERJAAN</th>
                <th style="text-align: center">STATUS</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $index => $item)
                <tr>
                    <td style="text-align: center">{{ $index + 1 }}</td>
                    <td style="text-align: center">{{ $item->siswa->nis }}</td>
                    <td>{{ $item->siswa->nama_siswa }}</td>
                    <td>{{ $item->siswa->kelas->jurusan->nama_jurusan ?? '-' }}</td>
                    <td>......................................</td>
                    <td><strong>diterima/tidak</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-left: 400px">
        <br>
        __________, ______________ 2025<br>
        Yang menyatakan<br><br><br><br><br>
        _______________________
    </div>

</body>

</html>
