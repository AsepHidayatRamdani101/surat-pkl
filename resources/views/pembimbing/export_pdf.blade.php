<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Export Data Pembimbing</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
        }

        h2 {
            margin: 0 0 4px;
            font-size: 16px;
        }

        .meta {
            margin-bottom: 10px;
            font-size: 9px;
            color: #4b5563;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #9ca3af;
            padding: 5px;
            vertical-align: top;
            text-align: left;
            word-wrap: break-word;
        }

        th {
            background: #eef2f7;
            font-weight: 700;
            text-align: center;
        }
    </style>
</head>

<body>
    <h2>Data Pembimbing</h2>
    <div class="meta">Dicetak: {{ $generatedAt }}</div>

    <table>
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 13%;">Nama</th>
                <th style="width: 11%;">NIP</th>
                <th style="width: 7%;">Jenis Kelamin</th>
                <th style="width: 10%;">Jabatan</th>
                <th style="width: 8%;">No HP</th>
                <th style="width: 6%;">Jml Jam</th>
                <th style="width: 6%;">Jml Siswa</th>
                <th style="width: 8%;">Jenis Guru</th>
                <th style="width: 9%;">Jurusan</th>
                <th style="width: 9%;">Status Akun</th>
                <th style="width: 9%;">Username Akun</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $index => $row)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $row['nama'] }}</td>
                    <td>{{ $row['nip'] }}</td>
                    <td>{{ $row['jenis_kelamin'] }}</td>
                    <td>{{ $row['jabatan'] }}</td>
                    <td>{{ $row['no_hp'] }}</td>
                    <td style="text-align: center;">{{ $row['jumlah_jam'] }}</td>
                    <td style="text-align: center;">{{ $row['jumlah_siswa'] }}</td>
                    <td>{{ $row['jenis_guru'] }}</td>
                    <td>{{ $row['jurusan'] }}</td>
                    <td style="text-align: center;">{{ $row['status_akun'] }}</td>
                    <td>{{ $row['username_akun'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" style="text-align: center;">Tidak ada data pembimbing.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
