@php
    $layout = $designLayout ?? [];
    $canvasOffsetX = $layout['_canvas']['offset_x'] ?? -75;
    $companyName = isset($perusahaan) ? $perusahaan->nama_perusahaan : ($siswa->suratIzin?->perusahaan?->nama_perusahaan ?? '-');
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
    $signaturePath = !empty($ttdPath) ? public_path($ttdPath) : null;
    $signatureSrc = $signaturePath && is_file($signaturePath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($signaturePath))
        : null;
    $values = [
        'nama' => strtoupper($siswa->nama_siswa),
        'identitas' => $siswa->nis . ' | ' . ($siswa->kelas->jurusan->nama_jurusan ?? '-'),
        'pkl_intro' => 'Telah melakukan Praktek Kerja Lapangan (PKL) di',
        'perusahaan' => $companyName,
        'pkl_period' => $pklMulai && $pklSelesai
            ? 'selama ' . $pklBulan . ' bulan terhitung pada tanggal ' . $pklMulai->translatedFormat('d F Y') . ' - ' . $pklSelesai->translatedFormat('d F Y')
            : 'selama - bulan terhitung pada tanggal -',
        'pimpinan_title' => 'Pimpinan / Pembimbing DUDIKA',
        'kepala_title' => 'Kepala SMK Negeri 8 Garut',
        'pimpinan' => $leaderName,
        'pimpinan_nip' => 'NIP. ' . ($nipPemilik ?? '-'),
        'kepala_sekolah' => $kepalaSekolah ?? 'Kepala Sekolah',
        'kepala_nip' => 'NIP. ' . ($nipKepalaSekolah ?? '-'),
    ];
@endphp
<style>
    @foreach(($customFontFaces ?? []) as $fontFace)
        @font-face { font-family:'{{ $fontFace['name'] }}'; src:url('{{ $fontFace['src'] }}'); font-style:normal; font-weight:100 900; }
    @endforeach
    /* Desain lama dinonaktifkan agar tidak bertumpuk dengan template/editor baru. */
    body > .frame, body > .content, .sheet > .frame { display:none !important; }
    .sheet { width:1123px !important; height:794px !important; padding:0 !important; position:relative !important; overflow:hidden !important; background-image:url('{{ $backgroundFrontDataUri ?? '' }}') !important; background-size:100% 100% !important; background-position:0 0 !important; background-repeat:no-repeat !important; }
    .certificate-design-overlay { position:absolute; inset:0; z-index:20; pointer-events:none; font-family:{{ $fontFamily ?? 'Georgia, serif' }}; }
    .certificate-design-overlay .dynamic-element { position:absolute; white-space:nowrap; font-weight:600; }
    @media print { .sheet { width:297mm !important; height:210mm !important; margin:0 !important; } }
</style>
<div class="certificate-design-overlay">
    @if($signatureSrc && isset($layout['kepala_sekolah']))
        <img src="{{ $signatureSrc }}" alt="" style="position:absolute;left:{{ $layout['kepala_sekolah']['x'] }}%;top:{{ max(0, $layout['kepala_sekolah']['y'] - 10) }}%;width:95px;max-height:65px;object-fit:contain;">
    @endif
    @foreach($values as $key => $value)
        @php
            $position = $layout[$key] ?? ['x'=>10,'y'=>10,'size'=>14];
            $horizontalStyle = in_array($key, ['nama', 'identitas', 'perusahaan'], true)
                ? 'left:0;width:100%;text-align:center;'
                : 'left:calc(' . $position['x'] . '% + ' . $canvasOffsetX . 'px);';
        @endphp
        <div class="dynamic-element" style="{!! $horizontalStyle !!}top:{{ $position['y'] }}%;font-size:{{ $position['size'] }}px;font-family:{!! $position['font'] ?? $fontFamily ?? 'Georgia, serif' !!};font-weight:{{ $position['weight'] ?? 400 }};font-style:{{ !empty($position['italic']) ? 'italic' : 'normal' }};color:{{ $position['color'] ?? '#1f2937' }};">{{ $value }}</div>
    @endforeach
</div>
