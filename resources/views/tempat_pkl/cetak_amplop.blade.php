<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Amplop Surat</title>
    <style>
        @page {
            size: 220mm 110mm;
            margin: 0;
        }

        body {
            background-image: url('{{ public_path('amplop.jpg') }}');
            background-repeat: no-repeat;
            background-size: cover;
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
        }

        .amplop-container {
            width: 100%;
            height: 100%;
            position: relative;
        }

        .logo {
            position: absolute;
            top: 20px;
            left: 20px;
        }

        .logo img {
            height: 50px;
        }

        .alamat {
            position: absolute;
            top: 20px;
            left: 100px;
            font-size: 11px;
        }

        .jendela {
            position: absolute;
            top: 120px;
            left: 340px;
            width: 250px;
            height: 90px;
            border: 1px solid #ccc;
            background: #f1f1f1;
            padding: 10px;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="amplop-container">




        <div style="position: absolute; top: 140px; left: 600px; color: white;width: 200px">
            Kepada Yth:<br>
            <strong>{{ $data->first()->perusahaan->nama_perusahaan }}</strong><br>
            {{ $data->first()->perusahaan->alamat }}<br>

        </div>
    </div>
</body>

</html>
