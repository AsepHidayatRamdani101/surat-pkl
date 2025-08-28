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

        @page {
            size: A4;
            margin: 20mm 18mm 22mm 18mm;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            color: #000;
        }

        .kop {
            text-align: center;
            line-height: 1.2;
        }

        .kop .instansi {
            font-weight: bold;
        }

        .kop .nama-sekolah {
            font-weight: 800;
            font-size: 16pt;
            letter-spacing: .5px;
        }

        .subheader {
            font-size: 10pt;
        }

        hr.garis {
            border: 0;
            border-top: 2px solid #000;
            margin: 6px 0 12px;
        }

        .judul {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            margin-top: 8px;
        }

        .nomor {
            text-align: center;
            margin-bottom: 14px;
        }

        .section {
            margin-top: 10px;
        }

        .label {
            display: inline-block;
            width: 115px;
            vertical-align: top;
        }

        .value {
            display: inline-block;
            width: calc(100% - 120px);
        }

        .table-like {
            margin-left: 10px;
        }

        .ttd {
            width: 100%;
            margin-top: 26px;
        }

        .ttd td {
            vertical-align: bottom;
        }

        .kiri {
            width: 55%;
        }

        .kanan {
            width: 45%;
        }

        .small {
            font-size: 10pt;
        }

        .toolbar {
            display: flex;
            gap: 8px;
            margin-bottom: 12px;
        }

        .btn {
            padding: 6px 10px;
            border: 1px solid #333;
            background: #f8f8f8;
            cursor: pointer;
            border-radius: 4px;
            font-size: 12px;
        }

        @media print {
            .toolbar {
                display: none !important;
            }

            a[href]:after {
                content: "";
            }
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


        <div style="text-align: center">
            <div class="judul"><b>SURAT TUGAS</b></div>
            <div class="nomor">Nomor: 131/PK.03.03-SMKN8GRT</div>
        </div>

        <div class="section">
            <div class="table-like">
                <div style="margin-bottom: 10px"><strong>Yang bertanda tangan di bawah ini:</strong></div>
                <div><span class="label">Nama</span><span class="value">: Moh Rofik Zen, S.Pd., M.M.Pd.</span></div>
                <div><span class="label">NIP</span><span class="value">: 196906131994121002</span></div>
                <div><span class="label">Jabatan</span><span class="value">: Kepala SMK Negeri 8 Garut </span></div>
            </div>
        </div>

        <div class="section">
            <div class="table-like">
                <div style="margin-bottom: 10px"><strong>Dengan ini memberikan tugas kepada:</strong></div>
                <div><span class="label">Nama</span><span class="value">:
                        {{ $data->first()->pembimbing->nama_pembimbing }} </span>
                </div>
                <div><span class="label">NIP</span><span class="value">:
                        {{ $data->first()->pembimbing->nip_pembimbing }}</span>
                </div>
                <div><span class="label">Jabatan</span><span class="value">:
                        {{ $data->first()->pembimbing->jabatan_pembimbing }}</span>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="table-like">
                <div>
                    Untuk melaksanakan tugas sebagai pendamping dan pengantar siswa PRAKERIN ke
                    {{ $data->first()->perusahaan->nama_perusahaan }}
                    <strong></strong>
                    yang beralamat di {{ $data->first()->perusahaan->alamat }}
                    <strong></strong>,
                    pada:
                </div>
                <br>
                <div><span class="label">Hari/Tanggal</span><span class="value">: Senin, 1 September 2025
                    </span>
                </div>
                <div><span class="label">Waktu</span><span class="value">: 07.00 s.d. selesai</span></div>
                <div><span class="label">Kegiatan</span><span class="value">: Monitoring PKL</span></div>
            </div>
        </div>

        <div class="section">
            <div class="table-like">
                <p>
                    Tugas ini dilaksanakan sebagai bagian dari program kerjasama pendidikan antara
                    SMK Negeri 8 Garut dengan Dunia Usaha/Dunia Industri.
                </p>
                <p>Demikian surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.</p>
            </div>
        </div>



        <div style="margin-left: 300px">
            Garut, 29 Agustus 2025<br>
            Kepala Sekolah,<br>
            <img src="{{ public_path('ttd_kepsek.png') }}" class="ttd-left" alt="ttd_kepsek" width="180px"
                style="margin-top: -10px;margin-bottom: -20px"><br>
            <strong><u>MOH. ROFIK ZEN, S.Pd., M.M.Pd.</u></strong><br>
            NIP. 196906131994121002
        </div>

    </div>
    <div style="page-break-after: always;">



        {{-- Bagian Notulen Monitoring --}}
        @php
            $tahunPelajaran = $surat->tahun_pelajaran ?? '2025/2026';

        @endphp

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

        <div style="text-align: center"><b>NOTULEN MONITORING PKL</b></div>
        <div class="nomor">SMKN 8 GARUT<br>Tahun Pelajaran {{ $tahunPelajaran }}</div>

        <table style="border: none">
            <tr>
                <td style="border: none" width="150px"><span class="label">Hari/Tanggal</span></td>
                <td style="border: none">: 1 September 2025</td>
            </tr>
            <tr>
                <td style="border: none" width="200px">Waktu Monitoring</td>
                <td style="border: none">: 07.00 s.d. selesai</td>
            </tr>
            <tr>
                <td style="border: none" width="150px   ">Tempat / Instansi PKL</td>
                <td style="border: none">: {{ $data->first()->perusahaan->nama_perusahaan }}</td>
            </tr>
            <tr>
                <td style="border: none" width="150px">Alamat Tempat PKL</td>
                <td style="border: none">:
                    {{ $data->first()->perusahaan->alamat }}
                </td>
            </tr>


            <tr>
                <td style="border: none" width="150px">Nama Pembimbing Industri</td>
                <td style="border: none">
                    :.....................................................................................
                </td>
            </tr>
            <tr>
                <td style="border: none" width="150px">Nama Guru Pendamping</td>
                <td style="border: none">
                    :.....................................................................................
                </td>
            </tr>
        </table>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th style="text-align: center">No</th>
                    <th style="text-align: center">Nama</th>
                    <th style="text-align: center">Kelas</th>
                    <th style="text-align: center">Jurusan</th>
                    <th style="text-align: center">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $key => $item)
                    <tr>
                        <td style="text-align: center">{{ $key + 1 }}</td>
                        <td>{{ $item->siswa->nama_siswa }}</td>
                        <td style="text-align: center">{{ $item->siswa->kelas->nama_kelas }}</td>
                        <td>{{ $item->siswa->kelas->jurusan->nama_jurusan }}</td>
                        <td>Ada/Tidak Ada</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div style="page-break-after: always;">


        <div class="section"><strong>Uraian Hasil Monitoring</strong></div>
        <p><em>Kehadiran</em></p>
        <p>......................................................................................................................................................
        </p>


        <p><em>Sikap & Etika Kerja</em></p>
        <p>......................................................................................................................................................
        </p>
        <p>......................................................................................................................................................
        </p>

        <p><em>Kinerja/Tugas yang Dilakukan</em></p>
        <p>......................................................................................................................................................
        </p>
        <p>......................................................................................................................................................
        </p>

        <p><em>MoU dan Kerjasama</em></p>
        <p>......................................................................................................................................................
        </p>

        <p><em>Hambatan/Kendala</em></p>
        <p>......................................................................................................................................................
        </p>
        <p>......................................................................................................................................................
        </p>

        <p><em>Respon/Pendapat dari Pembimbing Industri</em></p>
        <p>......................................................................................................................................................
        </p>
        <p>......................................................................................................................................................
        </p>

        <p><em>Tindak Lanjut yang Disarankan</em></p>
        <p>......................................................................................................................................................
        </p>
        <p>......................................................................................................................................................
        </p>


        <div style="margin-left: 300px">
            Garut, 29 Agustus 2025<br>
            Pembimbing,<br>


            <div style="margin-top: 60px">
                <strong><u>{{ $data->first()->pembimbing->nama_pembimbing }}</u></strong><br>
                NIP. {{ $data->first()->pembimbing->nip_pembimbing }}
            </div>
        </div>
    </div>





</body>

</html>
