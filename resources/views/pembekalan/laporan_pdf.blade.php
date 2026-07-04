<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Pembekalan</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            color: #222;
        }

        h2,
        h3 {
            margin: 0 0 8px;
        }

        .mb-16 {
            margin-bottom: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        th,
        td {
            border: 1px solid #777;
            padding: 6px;
            vertical-align: top;
        }

        th {
            background: #efefef;
        }
    </style>
</head>

<body>
    @include('partials.kop_surat_default', ['kopLogo' => $logoBase64 ?? null])

    <h2 style="margin: 0 0 6px;">Laporan Pembekalan PKL</h2>
    <div style="margin-bottom: 8px;"><strong>Nomor:</strong> {{ $nomorDokumen ?? '-' }}</div>
    <div class="mb-16">
        <strong>Filter:</strong>
        Pembimbing {{ $filters['pembimbing_id'] ?: 'Semua' }},
        Tanggal {{ $filters['tanggal_awal'] ?: '-' }} s/d {{ $filters['tanggal_akhir'] ?: '-' }}
    </div>

    <h3>Ringkasan</h3>
    <table>
        <tr>
            <th>Total Sesi</th>
            <th>Total Siswa</th>
            <th>Total Pembimbing</th>
            <th>Hadir</th>
            <th>Izin</th>
            <th>Alpa</th>
            <th>Rata Nilai</th>
        </tr>
        <tr>
            <td>{{ $summary['total_sesi'] }}</td>
            <td>{{ $summary['total_siswa'] }}</td>
            <td>{{ $summary['total_pembimbing'] }}</td>
            <td>{{ $summary['hadir'] }}</td>
            <td>{{ $summary['izin'] }}</td>
            <td>{{ $summary['alpa'] }}</td>
            <td>{{ $summary['rata_nilai'] }}</td>
        </tr>
    </table>

    <h3>Rekap Pembimbing</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Pembimbing</th>
                <th>Total Sesi</th>
                <th>Total Hadir</th>
                <th>Rata Nilai</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rekapPembimbing as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row->nama_pembimbing ?? '-' }}</td>
                    <td>{{ $row->total_sesi }}</td>
                    <td>{{ $row->total_hadir }}</td>
                    <td>{{ $row->rata_nilai }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h3>Detail Sesi Pembekalan</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Pembimbing</th>
                <th>Siswa</th>
                <th>Kelas</th>
                <th>Materi</th>
                <th>Tugas</th>
                <th>Absensi</th>
                <th>Nilai</th>
                <th>Sikap</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->tanggal_bimbingan ? \Carbon\Carbon::parse($item->tanggal_bimbingan)->format('d-m-Y') : '-' }}
                    </td>
                    <td>{{ $item->pembimbing->nama_pembimbing ?? '-' }}</td>
                    <td>{{ $item->siswa->nama_siswa ?? '-' }}</td>
                    <td>{{ $item->siswa->kelas->nama_kelas ?? '-' }}</td>
                    <td>{{ $item->topik_pembekalan ?? '-' }}</td>
                    <td>{{ $item->tugas ?? '-' }}</td>
                    <td>{{ $item->status_absensi ?? '-' }}</td>
                    <td>{{ $item->nilai_tugas ?? '-' }}</td>
                    <td>{{ $item->penilaian_sikap ? ucwords(str_replace('_', ' ', $item->penilaian_sikap)) : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center;">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @include('partials.ttd_default', [
        'ttdTanggal' => now()->translatedFormat('d F Y'),
        'ttdLabel' => 'Kepala Sekolah',
        'ttdNama' => $sekolah->nama_kepala_sekolah ?? 'Kepala Sekolah',
        'ttdNip' => $sekolah->nip_kepala_sekolah ?? '-',
        'ttdImage' => $ttdKepalaSekolahBase64 ?? null,
        'ttdContainerStyle' => 'width: 34%; margin-left: auto; margin-top: 8px;',
        'ttdAlign' => 'center',
    ])
</body>

</html>
