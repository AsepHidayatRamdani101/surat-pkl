@php
    $rows = collect($assessmentRows ?? []);
    $isPembekalan = ($certificateType ?? 'pelaksanaan') === 'pembekalan';
    $personTitleCase = fn (string $value) => collect(preg_split('/(\s+)/u', $value, -1, PREG_SPLIT_DELIM_CAPTURE))
        ->map(fn (string $part) => substr_count($part, '.') >= 2 ? $part : mb_convert_case($part, MB_CASE_TITLE, 'UTF-8'))
        ->implode('');
    $formatNip = function ($value): string {
        $nip = trim((string) $value);
        $nip = trim((string) preg_replace('/^NIP\.?\s*/i', '', $nip));

        return $nip !== '' && $nip !== '-' ? 'NIP. ' . $nip : '';
    };
    $defaultBackText = ['font' => $fontFamily ?? 'Georgia, serif', 'weight' => 400, 'italic' => false, 'color' => '#1f2937'];
    $backLayout = array_replace_recursive([
        '_table' => [
            'header_color' => '#e5e7eb',
            'header' => array_merge($defaultBackText, ['size' => 11, 'weight' => 700]),
            'body' => array_merge($defaultBackText, ['size' => 11, 'weight' => 400]),
        ],
        'title' => array_merge($defaultBackText, ['x' => 50, 'y' => 18, 'size' => 20, 'weight' => 700]),
        'legend' => array_merge($defaultBackText, ['x' => 12, 'y' => 72, 'size' => 10]),
        'date' => array_merge($defaultBackText, ['x' => 72, 'y' => 74, 'size' => 10]),
        'signature_title' => array_merge($defaultBackText, ['x' => 72, 'y' => 78, 'size' => 10]),
        'signature_name' => array_merge($defaultBackText, ['x' => 72, 'y' => 88, 'size' => 10, 'weight' => 600]),
        'signature_nip' => array_merge($defaultBackText, ['x' => 72, 'y' => 92, 'size' => 10]),
    ], $backDesignLayout ?? []);
    $backTexts = [
        'title' => $isPembekalan
            ? 'NILAI PEMBEKALAN PRAKTIK KERJA LAPANGAN (PKL)'
            : 'NILAI PRAKTIK KERJA LAPANGAN (PKL)',
        'legend' => "Keterangan :\nA - Baik Sekali 86 - 100\nB - Baik 70 - 85\nC - Cukup 60 - 69\nD - Kurang 50 - 59\nE - Kurang Sekali < 49",
        'date' => 'Garut, ' . $tanggalCetak->translatedFormat('d F Y'),
        'signature_title' => $isPembekalan ? 'Kepala SMK Negeri 8 Garut' : 'Pembimbing DUDIKA',
        'signature_name' => $isPembekalan
            ? ($kepalaSekolah ?? '-')
            : $personTitleCase(($namaPembimbing ?? null) ?: '-'),
        'signature_nip' => $formatNip($isPembekalan
            ? ($nipKepalaSekolah ?? null)
            : ($nipPembimbing ?? null)),
    ];
    $aspekHeader = $isPembekalan ? 'Aspek Penilaian Kegiatan Pembekalan' : 'Aspek Penilaian';
    $hasTotalNilai = $rows->contains(fn ($row) => is_numeric($row['nilai'] ?? null));
    $totalNilai = $rows->sum(fn ($row) => is_numeric($row['nilai'] ?? null) ? (float) $row['nilai'] : 0);
    $totalNilai = fmod($totalNilai, 1) === 0.0 ? (int) $totalNilai : round($totalNilai, 2);
@endphp
<style>
    .certificate-back {
        page-break-before: always;
        position: relative;
        width: 297mm;
        height: 210mm;
        margin: 0 auto;
        font-family: {{ $fontFamily ?? 'Georgia, serif' }};
        overflow: hidden;
        background: #fff;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .certificate-back-background {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: fill;
        z-index: 0;
        pointer-events: none;
    }
    @media screen {
        .certificate-back {
            width: 1123px;
            height: 794px;
            margin: 24px auto 0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .25);
        }
    }
    @media print {
        .certificate-back {
            width: 297mm;
            height: 210mm;
            margin: 0;
            box-shadow: none;
        }
    }
</style>
<div class="certificate-back">
    @if(!empty($backgroundBackDataUri))
        <img class="certificate-back-background" src="{{ $backgroundBackDataUri }}" alt="">
    @endif
    @foreach($backTexts as $key => $text)
        @php
            $textLayout = $backLayout[$key];
        @endphp
        <div style="position:absolute;z-index:2;left:{{ $textLayout['x'] }}%;top:{{ $textLayout['y'] }}%;transform:{{ $key === 'legend' ? 'none' : 'translateX(-50%)' }};white-space:{{ $key === 'legend' || !empty($textLayout['text_custom']) ? 'pre-line' : 'nowrap' }};text-align:{{ $key === 'legend' ? 'left' : 'center' }};line-height:1.35;font-size:{{ $textLayout['size'] }}px;font-family:{!! $textLayout['font'] !!};font-weight:{{ $textLayout['weight'] }};font-style:{{ $textLayout['italic'] ? 'italic' : 'normal' }};color:{{ $textLayout['color'] }};">{{ !empty($textLayout['text_custom']) ? ($textLayout['text'] ?? '') : $text }}</div>
    @endforeach
    @php
    $tableLayout = $backLayout['_table'];
        $tableRight = max(0, 100 - $tableLayout['x'] - $tableLayout['width']);
        $tableBottom = max(0, 100 - $tableLayout['y'] - $tableLayout['height']);
    @endphp
    <div style="position:absolute;z-index:1;left:{{ $tableLayout['x'] }}%;right:{{ $tableRight }}%;top:{{ $tableLayout['y'] }}%;bottom:{{ $tableBottom }}%;">
        <table style="width:100%;border-collapse:collapse;background:rgba(255,255,255,.88);">
            <thead style="background:{{ $backLayout['_table']['header_color'] }};font-size:{{ $backLayout['_table']['header']['size'] }}px;font-family:{!! $backLayout['_table']['header']['font'] !!};font-weight:{{ $backLayout['_table']['header']['weight'] }};font-style:{{ $backLayout['_table']['header']['italic'] ? 'italic' : 'normal' }};color:{{ $backLayout['_table']['header']['color'] }};"><tr><th style="border:1px solid #333;padding:7px;width:8%;font-weight:inherit;">No.</th><th style="border:1px solid #333;padding:7px;text-align:center;font-weight:inherit;">{{ $aspekHeader }}</th><th style="border:1px solid #333;padding:7px;width:16%;font-weight:inherit;">Nilai (Angka)</th><th style="border:1px solid #333;padding:7px;width:24%;font-weight:inherit;">Predikat</th></tr></thead>
            <tbody style="font-size:{{ $backLayout['_table']['body']['size'] }}px;font-family:{!! $backLayout['_table']['body']['font'] !!};font-weight:{{ $backLayout['_table']['body']['weight'] }};font-style:{{ $backLayout['_table']['body']['italic'] ? 'italic' : 'normal' }};color:{{ $backLayout['_table']['body']['color'] }};">
                @forelse($rows as $i => $row)
                    <tr>
                        <td style="border:1px solid #333;padding:7px;text-align:center;font-weight:inherit;">{{ $i + 1 }}</td>
                        <td style="border:1px solid #333;padding:7px;text-align:center;font-weight:inherit;">{{ $row['aspek'] }}</td>
                        <td style="border:1px solid #333;padding:7px;text-align:center;font-weight:inherit;">{{ $row['nilai'] ?? '' }}</td>
                        <td style="border:1px solid #333;padding:7px;text-align:center;font-weight:inherit;">{{ $row['predikat'] ?? '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="border:1px solid #333;padding:18px;text-align:center;font-weight:inherit;">Aspek penilaian belum diatur.</td></tr>
                @endforelse
                <tr>
                    <td colspan="2" style="border:1px solid #333;padding:7px;text-align:center;font-weight:700;"><strong>Total Nilai</strong></td>
                    <td style="border:1px solid #333;padding:7px;text-align:center;font-weight:400;">{{ $hasTotalNilai ? $totalNilai : '' }}</td>
                    <td style="border:1px solid #333;padding:7px;text-align:center;font-weight:400;"></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
