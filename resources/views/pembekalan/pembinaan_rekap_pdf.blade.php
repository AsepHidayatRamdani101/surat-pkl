<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Rekap Pembinaan Pembekalan</title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            color: #222;
        }

        h2,
        h3 {
            margin: 0 0 8px;
        }

        .mb-12 {
            margin-bottom: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        th,
        td {
            border: 1px solid #777;
            padding: 5px;
            vertical-align: top;
        }

        th {
            background: #efefef;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
    @include('partials.kop_surat_default')

    <h2>Rekap Pembinaan Peserta Pembekalan PKL</h2>
    <div class="mb-12">
        <strong>Filter:</strong>
        Tanggal {{ $filters['tanggal_awal'] ?: '-' }} s/d {{ $filters['tanggal_akhir'] ?: '-' }},
        Pembimbing {{ $filters['pembimbing_id'] ?: 'Semua' }},
        Peserta {{ $filters['siswa_id'] ?: 'Semua' }}
    </div>

    <h3>Ringkasan</h3>
    <table>
        <tr>
            <th>Total</th>
            <th>Tahap I</th>
            <th>Tahap II</th>
            <th>Tahap III</th>
            <th>Tahap IV</th>
        </tr>
        <tr>
            <td class="text-center">{{ $summary['total'] }}</td>
            <td class="text-center">{{ $summary['tahap_1'] }}</td>
            <td class="text-center">{{ $summary['tahap_2'] }}</td>
            <td class="text-center">{{ $summary['tahap_3'] }}</td>
            <td class="text-center">{{ $summary['tahap_4'] }}</td>
        </tr>
    </table>

    <h3>Detail Rekap</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 34px;">No</th>
                <th style="width: 70px;">Tanggal</th>
                <th style="width: 160px;">Peserta</th>
                <th style="width: 80px;">NIS</th>
                <th style="width: 70px;">Kelas</th>
                <th style="width: 140px;">Pembimbing</th>
                <th style="width: 85px;">Tingkat</th>
                <th>Kronologi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($records as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ optional($item->tanggal_formulir)->format('d-m-Y') ?? '-' }}</td>
                    <td>{{ $item->siswa->nama_siswa ?? '-' }}</td>
                    <td class="text-center">{{ $item->siswa->nis ?? '-' }}</td>
                    <td class="text-center">{{ optional($item->siswa->kelas)->nama_kelas ?? '-' }}</td>
                    <td>{{ $item->pembimbing->nama_pembimbing ?? '-' }}</td>
                    <td class="text-center">{{ $tingkatPembinaanOptions[$item->tingkat_pembinaan]['label'] ?? '-' }}
                    </td>
                    <td>{{ $item->kronologi ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data pembinaan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
