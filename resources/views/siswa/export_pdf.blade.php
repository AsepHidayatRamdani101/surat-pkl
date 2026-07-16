<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Export Data Siswa</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
        }

        h2 {
            margin: 0 0 4px;
            font-size: 16px;
        }

        .meta {
            margin-bottom: 10px;
            font-size: 10px;
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
            padding: 6px;
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
    <h2>Data Siswa</h2>
    <div class="meta">Dicetak: {{ $generatedAt }}</div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 14%;">NIS</th>
                <th style="width: 20%;">Nama Siswa</th>
                <th style="width: 13%;">Kelas</th>
                <th style="width: 16%;">Jurusan</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 11%;">Status Akun</th>
                <th style="width: 11%;">Username Akun</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $index => $row)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $row['nis'] }}</td>
                    <td>{{ $row['nama_siswa'] }}</td>
                    <td>{{ $row['kelas'] }}</td>
                    <td>{{ $row['jurusan'] }}</td>
                    <td>{{ $row['status'] }}</td>
                    <td style="text-align: center;">{{ $row['status_akun'] }}</td>
                    <td>{{ $row['username_akun'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center;">Tidak ada data siswa.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
