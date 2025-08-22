<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Izin Orang Tua</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            line-height: 1.6;
            font-size: 11pt
        }

        p {
            text-align: justify;
            font-size: 11pt
        }

        .content {
            margin: 0 auto;
            width: 90%;
        }

        .header,
        .footer {
            text-align: center;
        }

        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }

        .section-title {
            font-weight: bold;
            margin-top: 20px;
        }

        li {
            font-size: 11pt;
            text-align: justify
        }

        td {
            font-size: 11pt
        }

        .content p {
            margin-bottom: 10px;
        }

        .sign {
            margin-top: 40px;
            text-align: right;
        }

        .sign p {
            padding-left: 70%;
            margin: 0;
        }
    </style>
</head>

<body>

    <div class="content">

        <div class="title">
            <h2>Surat Izin Orang Tua</h2>
        </div>

        <p>Saya yang bertanda tangan di bawah ini:</p>

        <p>
        <table style="margin-bottom: -10px">
            <tr>
                <td width="30%">Nama Orang Tua/Wali </td>
                <td>:</td>
                <td width="65%">{{ $izin->nama_ortu }}</td>
            </tr>
            <tr>
                <td>Nama Siswa</td>
                <td>:</td>
                <td>{{ $izin->siswa->nama_siswa }}</td>
            </tr>
            <tr>
                <td>NISN</td>
                <td>:</td>
                <td>{{ $izin->siswa->nis }}</td>
            </tr>
            <tr>
                <td>Kelas</td>
                <td>:</td>
                <td>{{ $izin->siswa->kelas->nama_kelas }}</td>
            </tr>
            <tr>
                <td>Konsentrasi Keahlian</td>
                <td>:</td>
                <td>{{ $izin->siswa->kelas->jurusan->nama_jurusan }}</td>
            </tr>
            <tr>
                <td>Alamat </td>
                <td>:</td>
                <td>{{ $izin->alamat_ortu }}</td>
            </tr>
        </table>

        </p>

        <p>Dengan ini memberikan izin kepada anak saya tersebut di atas untuk mengikuti kegiatan
            Praktik Kerja Industri
            (PRAKERIN) yang diselenggarakan oleh SMK Negeri 8 Garut, sesuai dengan program sekolah.</p>

        <div>
            <p>Saya bersedia:</p>
            <ol style="margin: 0px -5px 0px -5px">
                <li>Membimbing, mengawasi, dan memberikan dukungan selama anak saya mengikuti kegiatan PRAKERIN.</li>
                <li>Bertanggung jawab atas segala tindakan dan perilaku anak saya selama kegiatan berlangsung.</li>
                <li>Tidak akan menuntut pihak sekolah apabila terjadi sesuatu di luar tanggung jawab sekolah selama
                    pelaksanaan PRAKERIN, sejauh sekolah telah menjalankan prosedur yang berlaku.</li>
            </ol>
        </div>

        <p>Demikian surat izin ini saya buat dengan sebenar-benarnya untuk digunakan sebagaimana mestinya.</p>

        <div class="sign" style="text-align: right;margin-top: -10px">
            <p>Garut, ................. 2025</p>
            <p>Hormat saya,</p>
            <br>
            <div
                style="border: 1px solid black; width: 75px; height: 50px; text-align: center;margin-left: 400px;margin-top: -10px;margin-bottom: -10px ">
                <div style="display: flex; justify-content: center; align-items: center; height: 100%;font-size: 10px">
                    Materai <br> Rp. 10.000
                </div>
            </div>
            <br>
            <p>{{ $izin->nama_ortu }}</p>
        </div>
    </div>

</body>

</html>
