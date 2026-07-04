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
        @include('partials.kop_surat_default')


        <div style="text-align: center">
            <div class="judul"><b>SURAT TUGAS</b></div>
            <div class="nomor">Nomor: {{ $nomor_surat }}/PK.03.03-SMKN8GRT</div>
        </div>

        <div class="section">
            <div class="table-like">
                <div style="margin-bottom: 10px"><strong>Yang bertanda tangan di bawah ini:</strong></div>
                <div><span class="label">Nama</span><span class="value">: {{ $nama_kepala_sekolah }}</span></div>
                <div><span class="label">NIP</span><span class="value">: {{ $nip_kepala_sekolah }}</span></div>
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
                <div><span class="label">Hari/Tanggal</span><span class="value">:
                        {{ \Carbon\Carbon::parse($tanggal_berangkat)->translatedFormat('d F Y') }}
                    </span>
                </div>
                <div><span class="label">Waktu</span><span class="value">: 07.00 s.d. selesai</span></div>
                <div><span class="label">Kegiatan</span><span class="value">: Penjemputan PKL</span></div>
            </div>
        </div>

        <div class="section">
            <div class="table-like">
                        @include('partials.ttd_default', [
                            'ttdTanggal' => 'Garut, ' . \\Carbon\\Carbon::parse($tanggal_surat)->translatedFormat('d F Y'),
                            'ttdLabel' => 'Kepala Sekolah',
                            'ttdNama' => $nama_kepala_sekolah,
                            'ttdNip' => $nip_kepala_sekolah,
                            'ttdImage' => $nama_file_ttd,
                            'ttdContainerStyle' => 'margin-left: 300px; margin-top: 6px;',
                            'ttdAlign' => 'left'
                        ])
        <div style="margin-left: 300px">
            Garut, {{ \Carbon\Carbon::parse($tanggal_surat)->translatedFormat('d F Y') }}<br>
            Kepala Sekolah,<br>
            <img src="file:///{{ str_replace('\\', '/', public_path($nama_file_ttd)) }}" class="ttd-left"
                alt="ttd_kepsek" width="180px" style="margin-top: -10px;margin-bottom: -20px"><br>
            <strong><u>{{ $nama_kepala_sekolah }}</u></strong><br>
            NIP. {{ $nip_kepala_sekolah }}
        </div>

    </div>
    <div style="page-break-after: always;">



        {{-- Bagian Notulen Monitoring --}}
                @include('partials.ttd_default', [
                    'ttdTanggal' => 'Garut, ' . \\Carbon\\Carbon::parse($tanggal_berangkat)->translatedFormat('d F Y'),
                    'ttdLabel' => 'Pembimbing',
                    'ttdNama' => $data->first()->pembimbing->nama_pembimbing,
                    'ttdNip' => $data->first()->pembimbing->nip_pembimbing,
                    'ttdImage' => null,
                    'ttdContainerStyle' => 'margin-left: 300px; margin-top: 10px;',
                    'ttdAlign' => 'left'
                ])
                <td style="border: none" width="150px"><span class="label">Hari/Tanggal</span></td>
                <td style="border: none">: {{ \Carbon\Carbon::parse($tanggal_berangkat)->translatedFormat('d F Y') }}
                </td>
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
            Garut, {{ \Carbon\Carbon::parse($tanggal_berangkat)->translatedFormat('d F Y') }}<br>
            Pembimbing,<br>


            <div style="margin-top: 60px">
                <strong><u>{{ $data->first()->pembimbing->nama_pembimbing }}</u></strong><br>
                NIP. {{ $data->first()->pembimbing->nip_pembimbing }}
            </div>
        </div>
    </div>





</body>

</html>
