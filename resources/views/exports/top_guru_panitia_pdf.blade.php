<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Top 10 Guru Terbaik</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1f2937;
        }

        h2 {
            margin: 0 0 6px;
            font-size: 18px;
        }

        .meta {
            margin-bottom: 10px;
            color: #4b5563;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 6px;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
            text-align: left;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>
    <h2>Top 10 Guru Terbaik - Dashboard Panitia</h2>
    <div class="meta">
        <div>Periode: {{ $tanggalAwal ?: '-' }} s/d {{ $tanggalAkhir ?: '-' }}</div>
        <div>Bobot (Total {{ number_format($totalWeight, 2) }}): Absensi={{ $weights['absensi'] }},
            Sikap={{ $weights['sikap'] }}, Kelengkapan={{ $weights['kelengkapan'] }}, Nilai={{ $weights['nilai'] }}
        </div>
        <div>Dicetak: {{ $generatedAt->format('d-m-Y H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Nama Guru</th>
                <th style="width: 65px;">Skor</th>
                <th style="width: 85px;">Absensi</th>
                <th style="width: 85px;">Sikap</th>
                <th style="width: 95px;">Kelengkapan</th>
                <th style="width: 85px;">Nilai</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($topGuru as $index => $guru)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $guru->nama_pembimbing }}</strong><br>
                        NIP: {{ $guru->nip_pembimbing ?? '-' }} | Siswa: {{ $guru->total_siswa_bimbingan }}
                    </td>
                    <td class="text-right">{{ $guru->skor_akhir }}</td>
                    <td>
                        {{ $guru->score_absensi }}%<br>
                        {{ $guru->absensi_hari_lengkap }}/{{ $guru->absensi_hari_total }} hari
                    </td>
                    <td>
                        {{ $guru->score_sikap }}%<br>
                        {{ $guru->sikap_siswa_hari_tercatat }}/{{ $guru->sikap_siswa_hari_target }} siswa-hari
                    </td>
                    <td>
                        {{ $guru->score_kelengkapan }}%<br>
                        {{ $guru->kelengkapan_siswa_hari_tercatat }}/{{ $guru->kelengkapan_siswa_hari_target }}
                        siswa-hari
                    </td>
                    <td>
                        {{ $guru->score_nilai }}%<br>
                        {{ $guru->nilai_tugas_terisi }}/{{ $guru->nilai_tugas_submitted }} tugas
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">Belum ada data pembimbing yang cukup untuk dinilai.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
