<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Export Kelompok Bimbingan</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
        }

        h2 {
            margin: 0 0 4px;
            font-size: 17px;
        }

        .meta {
            margin-bottom: 12px;
            font-size: 10px;
            color: #4b5563;
        }

        .report-header {
            border: 1px solid #9ca3af;
            background: #f8fafc;
            padding: 10px 12px;
            margin-bottom: 12px;
        }

        .report-subtitle {
            font-size: 10px;
            color: #6b7280;
            margin: 0;
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
        }

        th {
            background: #eef2f7;
            font-weight: 700;
            text-align: center;
            vertical-align: middle;
        }

        .group-start td {
            border-top: 2px solid #94a3b8;
        }

        .main-cell {
            background: #fbfdff;
            text-align: center;
            vertical-align: middle;
        }

        .class-cell {
            background: #fbfdff;
            text-align: center;
            vertical-align: middle;
        }

        .member-item {
            padding: 4px 0;
            border-bottom: 1px solid #d1d5db;
        }

        .member-item:last-child {
            border-bottom: 0;
        }
    </style>
</head>

<body>
    <div class="report-header">
        <h2>Daftar Kelompok Bimbingan</h2>
        <p class="report-subtitle">Format rekap kelompok per kelas</p>
        <div class="meta">Dicetak: {{ $generatedAt }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 11%;">Nama Kelompok</th>
                <th style="width: 7%;">Metode</th>
                <th style="width: 14%;">Pembimbing</th>
                <th style="width: 6%;">Jumlah Siswa</th>
                <th style="width: 14%;">Siswa per Kelas</th>
                <th style="width: 20%;">Daftar Anggota</th>
                <th style="width: 12%;">No HP Siswa</th>
                <th style="width: 12%;">No HP Orang Tua</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($groups as $index => $group)
                @foreach ($group['kelas_rows'] as $kelasIndex => $kelasRow)
                    <tr class="{{ $kelasIndex === 0 ? 'group-start' : '' }}">
                        @if ($kelasIndex === 0)
                            <td rowspan="{{ $group['rowspan'] }}" class="main-cell">
                                {{ $index + 1 }}</td>
                            <td rowspan="{{ $group['rowspan'] }}" class="main-cell">
                                {{ $group['nama_kelompok'] }}</td>
                            <td rowspan="{{ $group['rowspan'] }}" class="main-cell">
                                {{ $group['metode'] }}</td>
                            <td rowspan="{{ $group['rowspan'] }}" class="main-cell">
                                {{ $group['pembimbing'] }}</td>
                            <td rowspan="{{ $group['rowspan'] }}" class="main-cell">
                                {{ $group['jumlah_siswa'] }}</td>
                        @endif
                        <td class="class-cell">{{ $kelasRow['siswa_per_kelas'] }}</td>
                        <td>
                            @foreach ($kelasRow['daftar_anggota'] as $anggota)
                                <div class="member-item">{{ $anggota }}</div>
                            @endforeach
                        </td>
                        <td>
                            @foreach ($kelasRow['daftar_no_hp_siswa'] as $noHpSiswa)
                                <div class="member-item">{{ $noHpSiswa }}</div>
                            @endforeach
                        </td>
                        <td>
                            @foreach ($kelasRow['daftar_no_hp_ortu'] as $noHpOrtu)
                                <div class="member-item">{{ $noHpOrtu }}</div>
                            @endforeach
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="9">Tidak ada data kelompok bimbingan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
