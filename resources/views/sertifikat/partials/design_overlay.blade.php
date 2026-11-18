@php
    $layout = $designLayout ?? [];
    $isPembekalan = ($certificateType ?? 'pelaksanaan') === 'pembekalan';
    $canvasOffsetX = $layout['_canvas']['offset_x'] ?? -75;
    $companyName = isset($perusahaan) ? $perusahaan->nama_perusahaan : ($siswa->suratIzin?->perusahaan?->nama_perusahaan ?? '-');
    $companyAddress = $alamatPerusahaanLengkap
        ?? (isset($perusahaan) ? $perusahaan->alamat : ($siswa->suratIzin?->perusahaan?->alamat ?? '-'));
    $leaderName = $namaPemilik
        ?? $siswa->suratIzin?->nama_pemilik_instansi
        ?? $siswa->suratIzin?->perusahaan?->nama_pemilik_perusahaan
        ?? 'Pimpinan Perusahaan';
    $pklTempat = $tempatPkl ?? $siswa->suratIzin;
    $pklMulai = $pklTempat?->tanggal_mulai ? \Carbon\Carbon::parse($pklTempat->tanggal_mulai)->locale('id') : null;
    $pklSelesai = $pklTempat?->tanggal_selesai ? \Carbon\Carbon::parse($pklTempat->tanggal_selesai)->locale('id') : null;
    $pklBulan = $pklMulai && $pklSelesai
        ? (($pklSelesai->year - $pklMulai->year) * 12 + $pklSelesai->month - $pklMulai->month + 1)
        : null;
    $personTitleCase = fn (string $value) => collect(preg_split('/(\s+)/u', $value, -1, PREG_SPLIT_DELIM_CAPTURE))
        ->map(fn (string $part) => substr_count($part, '.') >= 2 ? $part : mb_convert_case($part, MB_CASE_TITLE, 'UTF-8'))
        ->implode('');
    $formatNip = function ($value): string {
        $nip = trim((string) $value);
        $nip = trim((string) preg_replace('/^NIP\.?\s*/i', '', $nip));

        return $nip !== '' && $nip !== '-' ? 'NIP. ' . $nip : '';
    };
    $signaturePath = !empty($ttdPath) ? public_path($ttdPath) : null;
    $signatureSrc = $signaturePath && is_file($signaturePath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($signaturePath))
        : null;
    $centeredKeys = $isPembekalan
        ? ['nomor_sertifikat', 'nama', 'identitas', 'pembekalan_intro', 'pembekalan_title', 'pembekalan_result']
        : ['nomor_sertifikat', 'nama', 'identitas', 'perusahaan', 'alamat_perusahaan', 'tanggal_cetak'];
    $signatureColumnKeys = ['pimpinan', 'pimpinan_nip', 'kepala_sekolah', 'kepala_nip'];
    $nomorInduk = $siswa->nisn ?? $siswa->nis;
    $numberTemplate = $layout['nomor_sertifikat']['text']
        ?? ($isPembekalan ? 'Nomor: SPB/{nis}/{tahun}' : 'Nomor: SPL/{nis}/{tahun}');
    $certificateNumber = strtr($numberTemplate, [
        '{nis}' => $nomorInduk,
        '{nisn}' => $siswa->nisn ?? $siswa->nis,
        '{tahun}' => $tanggalCetak->format('Y'),
    ]);
    $values = $isPembekalan ? [
        'nomor_sertifikat' => $certificateNumber,
        'nama' => mb_strtoupper($siswa->nama_siswa, 'UTF-8'),
         'identitas' => 'NISN: ' . $nomorInduk . ' | ' . ($siswa->kelas->jurusan->nama_jurusan ?? '-'),
        'pembekalan_intro' => 'atas partisipasi dan keberhasilannya dalam mengikuti kegiatan',
        'pembekalan_title' => 'PEMBEKALAN PRAKTIK KERJA LAPANGAN (PKL)',
        'pembekalan_result' => 'yang diselenggarakan oleh SMK Negeri 8 Garut',
        'tanggal_cetak' => 'Garut, ' . $tanggalCetak->translatedFormat('d F Y'),
        'kepala_title' => 'Kepala SMK Negeri 8 Garut',
        'kepala_sekolah' => $kepalaSekolah ?? 'Kepala Sekolah',
        'kepala_nip' => $formatNip($nipKepalaSekolah ?? null),
    ] : [
        'nomor_sertifikat' => $certificateNumber,
        'nama' => mb_strtoupper($siswa->nama_siswa, 'UTF-8'),
        'identitas' => 'NISN: ' . $nomorInduk . ' | ' . ($siswa->kelas->jurusan->nama_jurusan ?? '-'),
        'pkl_intro' => 'Telah melakukan Praktek Kerja Lapangan (PKL) di',
        'perusahaan' => mb_strtoupper($companyName, 'UTF-8'),
        'pkl_period' => $pklMulai && $pklSelesai
            ? 'selama ' . $pklBulan . ' bulan terhitung pada tanggal ' . $pklMulai->translatedFormat('d F Y') . ' - ' . $pklSelesai->translatedFormat('d F Y')
            : 'selama - bulan terhitung pada tanggal -',
        'alamat_perusahaan' => mb_convert_case($companyAddress, MB_CASE_TITLE, 'UTF-8'),
        'tanggal_cetak' => 'Garut, ' . $tanggalCetak->translatedFormat('d F Y'),
        'pimpinan_title' => 'Pimpinan / Pembimbing DUDIKA',
        'kepala_title' => 'Kepala SMK Negeri 8 Garut',
        'pimpinan' => $personTitleCase($leaderName),
        'pimpinan_nip' => $formatNip($nipPemilik ?? null),
        'kepala_sekolah' => $kepalaSekolah ?? 'Kepala Sekolah',
        'kepala_nip' => $formatNip($nipKepalaSekolah ?? null),
    ];
@endphp
<style>
    @foreach(($customFontFaces ?? []) as $fontFace)
        @font-face { font-family:'{{ $fontFace['name'] }}'; src:url('{{ $fontFace['src'] }}'); font-style:normal; font-weight:100 900; }
    @endforeach
    /* Desain lama dinonaktifkan agar tidak bertumpuk dengan template/editor baru. */
    body > .frame, body > .content, .sheet > .frame { display:none !important; }
    .sheet { width:1123px !important; height:794px !important; padding:0 !important; position:relative !important; overflow:hidden !important; background:none !important; }
    .certificate-background-image { position:absolute; inset:0; width:100%; height:100%; object-fit:fill; z-index:0; pointer-events:none; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    .certificate-design-overlay { position:absolute; inset:0; z-index:20; pointer-events:none; font-family:{{ $fontFamily ?? 'Georgia, serif' }}; }
    .certificate-design-overlay .dynamic-element { position:absolute; white-space:nowrap; font-weight:600; }
    .sheet > .frame .nomor { display:none !important; }
    @media print {
        .sheet { width:297mm !important; height:210mm !important; margin:0 !important; }
        .certificate-design-overlay [data-key="nomor_sertifikat"] {
            color:#ffffff !important;
            -webkit-print-color-adjust:exact !important;
            print-color-adjust:exact !important;
        }
    }
</style>
@php
    $frontCertificateBackground = $isPembekalan
        ? ($backgroundFrontPembekalanDataUri ?? $backgroundFrontDataUri ?? null)
        : ($backgroundFrontDataUri ?? null);
@endphp
@if(!empty($frontCertificateBackground))
    <img class="certificate-background-image" src="{{ $frontCertificateBackground }}" alt="">
@endif
<div class="certificate-design-overlay">
    @if($signatureSrc && isset($layout['kepala_sekolah']))
        <img src="{{ $signatureSrc }}" alt="" style="position:absolute;left:{{ $layout['kepala_sekolah']['x'] }}%;top:{{ max(0, $layout['kepala_sekolah']['y'] - 10) }}%;width:95px;max-height:65px;object-fit:contain;">
    @endif
    @foreach($values as $key => $value)
        @php
            $position = $layout[$key] ?? ['x'=>10,'y'=>10,'size'=>14];
            $displayValue = !empty($position['text_custom']) ? ($position['text'] ?? '') : $value;
            if ($key === 'nomor_sertifikat') {
                $displayValue = strtr($displayValue, ['{nis}' => $siswa->nis, '{nisn}' => $nomorInduk, '{tahun}' => $tanggalCetak->format('Y')]);
            }
            $horizontalStyle = in_array($key, $centeredKeys, true)
                ? 'left:0;width:100%;text-align:center;'
                : (in_array($key, $signatureColumnKeys, true)
                    ? 'left:calc(' . $position['x'] . '% + ' . $canvasOffsetX . 'px);width:28%;text-align:center;'
                    : 'left:calc(' . $position['x'] . '% + ' . $canvasOffsetX . 'px);');
        @endphp
        <div class="dynamic-element" data-key="{{ $key }}" style="{!! $horizontalStyle !!}top:{{ $position['y'] }}%;font-size:{{ $position['size'] }}px;font-family:{!! $position['font'] ?? $fontFamily ?? 'Georgia, serif' !!};font-weight:{{ $position['weight'] ?? 400 }};font-style:{{ !empty($position['italic']) ? 'italic' : 'normal' }};color:{{ $position['color'] ?? '#1f2937' }};">{{ $displayValue }}</div>
    @endforeach
</div>
