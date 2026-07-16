@php
    $tanggalFormulir = optional($record->tanggal_formulir)?->locale('id');
    $tanggalBerlaku = now()->locale('id')->addWeek()->translatedFormat('d F Y');
    $siswa = $record->siswa;
    $kelas = optional($siswa)->kelas;
    $jurusan = optional($kelas)->jurusan;
    $pembimbing = $record->pembimbing;

    $selectedJenis = collect($record->jenis_pembinaan ?? []);
    $selectedTindakan = collect($record->tindakan_pembinaan ?? []);
    $selectedHasil = collect($record->hasil_pembinaan ?? []);

    $namaKoordinator = 'Nurlaela Yulianti, S.Pd.';
    $nipKoordinator = '198807082020122007';

    $check = function ($state) {
        return $state ? 'frm-checkbox checked' : 'frm-checkbox';
    };
@endphp

@include('partials.kop_surat_default', [
    'kopOuterStyle' => 'margin-top: 0;',
    'kopLogoWidth' => 82,
    'kopHrStyle' => 'margin-top: 8px; border: 1px solid #111827;',
])

<div class="frm-title-block">
    <div class="frm-title">FORMULIR PEMBINAAN PESERTA</div>
    <div class="frm-title">PEMBEKALAN PRAKTIK KERJA LAPANGAN (PKL)</div>
</div>

<table class="frm-meta-table">
    <colgroup>
        <col style="width: 24.37%;">
        <col style="width: 75.63%;">
    </colgroup>
    <tr>
        <td class="frm-meta-label">Kode Formulir</td>
        <td>: FRM-PKL-02</td>
    </tr>
    <tr>
        <td class="frm-meta-label">Revisi</td>
        <td>: 00</td>
    </tr>
    <tr>
        <td class="frm-meta-label">Tanggal Berlaku</td>
        <td>: {{ $tanggalBerlaku }}</td>
    </tr>
</table>

<h4 class="frm-section-title">A. IDENTITAS PESERTA</h4>
<table class="frm-identity-table">
    <colgroup>
        <col style="width: 30.50%;">
        <col style="width: 69.50%;">
    </colgroup>
    <tr>
        <th>Nama Peserta</th>
        <td>{{ $siswa->nama_siswa ?? '....................................' }}</td>
    </tr>
    <tr>
        <th>NIS</th>
        <td>{{ $siswa->nis ?? '....................................' }}</td>
    </tr>
    <tr>
        <th>Kelas</th>
        <td>{{ $kelas->nama_kelas ?? '....................................' }}</td>
    </tr>
    <tr>
        <th>Jurusan</th>
        <td>{{ $jurusan->nama_jurusan ?? '....................................' }}</td>
    </tr>
    <tr>
        <th>Guru Pembimbing</th>
        <td>{{ $pembimbing->nama_pembimbing ?? '....................................' }}</td>
    </tr>
    <tr>
        <th>Hari / Tanggal</th>
        <td>{{ $tanggalFormulir ? $tanggalFormulir->translatedFormat('l, d F Y') : '....................................' }}
        </td>
    </tr>
    <tr>
        <th>Waktu</th>
        <td>{{ $record->waktu_formulir ?? '....................................' }}</td>
    </tr>
    <tr>
        <th>Tempat</th>
        <td>{{ $record->tempat ?? '....................................' }}</td>
    </tr>
</table>

<h4 class="frm-section-title">B. JENIS PEMBINAAN</h4>
<div class="frm-subtitle">Beri tanda (✓) sesuai kondisi.</div>
<table class="frm-check-table">
    <colgroup>
        <col style="width: 8.00%;">
        <col style="width: 82.00%;">
        <col style="width: 10.00%;">
    </colgroup>
    <thead>
        <tr>
            <th style="width: 48px;">No</th>
            <th>Uraian</th>
            <th style="width: 70px;">✓</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($jenisPembinaanOptions as $key => $label)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>
                    @if ($key === 'lainnya')
                        {{ $label }} :
                        {{ $record->jenis_pembinaan_lainnya ?: '....................................' }}
                    @else
                        {{ $label }}
                    @endif
                </td>
                <td class="text-center"><span class="{{ $check($selectedJenis->contains($key)) }}"></span></td>
            </tr>
        @endforeach
    </tbody>
</table>

<h4 class="frm-section-title">C. KRONOLOGI KEJADIAN</h4>
<div class="frm-subtitle">Uraikan secara singkat dan objektif.</div>
<div class="frm-box frm-box-tall">
    {{ $record->kronologi ?: '........................................................................................................' }}
</div>

<h4 class="frm-section-title">D. TINDAKAN PEMBINAAN</h4>
<div class="frm-subtitle">Beri tanda (✓).</div>
<table class="frm-check-table">
    <colgroup>
        <col style="width: 8.00%;">
        <col style="width: 82.00%;">
        <col style="width: 10.00%;">
    </colgroup>
    <thead>
        <tr>
            <th style="width: 48px;">No</th>
            <th>Uraian</th>
            <th style="width: 70px;">✓</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($tindakanPembinaanOptions as $key => $label)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>
                    @if ($key === 'lainnya')
                        {{ $label }} :
                        {{ $record->tindakan_pembinaan_lainnya ?: '....................................' }}
                    @else
                        {{ $label }}
                    @endif
                </td>
                <td class="text-center"><span class="{{ $check($selectedTindakan->contains($key)) }}"></span></td>
            </tr>
        @endforeach
    </tbody>
</table>

<h4 class="frm-section-title">E. KOMITMEN PESERTA</h4>
<div class="frm-box">
    {{ $record->komitmen_peserta ?: 'Saya menyadari bahwa tindakan yang saya lakukan tidak sesuai aturan pembekalan PKL dan bersedia memperbaiki perilaku.' }}
</div>
<div class="signature-inline">Tanda Tangan Peserta,</div>
<div class="signature-line">{{ $siswa->nama_siswa ?? '....................................' }}</div>

<h4 class="frm-section-title">F. HASIL PEMBINAAN</h4>
@foreach ($hasilPembinaanOptions as $key => $label)
    <div class="frm-check-line"><span class="{{ $check($selectedHasil->contains($key)) }}"></span> {{ $label }}
    </div>
@endforeach
<div class="frm-subtitle" style="margin-top:6px;">Catatan Guru Pembimbing</div>
<div class="frm-box frm-box-tall">
    {{ $record->catatan_guru ?: '........................................................................................................' }}
</div>

<h4 class="frm-section-title">G. VERIFIKASI</h4>
<table class="frm-verify-table">
    <colgroup>
        <col style="width: 33.33%;">
        <col style="width: 33.33%;">
        <col style="width: 33.34%;">
    </colgroup>
    <tr>
        <td>
            <div class="verify-title">Peserta</div>
            <div class="verify-spacer"></div>
            <div>{{ $siswa->nama_siswa ?? '....................................' }}</div>
            <div>NIS. {{ $siswa->nis ?? '....................................' }}</div>
        </td>
        <td>
            <div class="verify-title">Guru Pembimbing</div>
            <div class="verify-spacer"></div>
            <div>{{ $pembimbing->nama_pembimbing ?? '....................................' }}</div>
            <div>NIP. {{ $pembimbing->nip_pembimbing ?? '....................................' }}</div>
        </td>
        <td>
            <div class="verify-title">Koordinator Pembekalan</div>
            <div class="verify-spacer"></div>
            <div>{{ $namaKoordinator ?? '....................................' }}</div>
            <div>NIP. {{ $nipKoordinator ?? '....................................' }}</div>
        </td>
    </tr>
</table>

<h4 class="frm-section-title">H. REKAP TINGKAT PEMBINAAN</h4>
<table class="frm-rekap-table">
    <colgroup>
        <col style="width: 16.20%;">
        <col style="width: 37.90%;">
        <col style="width: 45.90%;">
    </colgroup>
    <thead>
        <tr>
            <th style="width: 90px;">Tahap</th>
            <th>Kriteria</th>
            <th>Tindak Lanjut</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($tingkatPembinaanOptions as $key => $item)
            <tr class="{{ $record->tingkat_pembinaan === $key ? 'row-highlight' : '' }}">
                <td>{{ $item['label'] }}</td>
                <td>{{ $item['kriteria'] }}</td>
                <td>{{ $item['tindak_lanjut'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
