@php
    $tanggalFormulir = \Carbon\Carbon::parse($filters['tanggal_formulir'])->locale('id');
    $tanggalBerlaku = now()->locale('id')->addWeek()->translatedFormat('d F Y');
    $namaPembimbing =
        optional($selectedKelompok?->pembimbing)->nama_pembimbing ??
        (optional($selectedPembimbing)->nama_pembimbing ?? '....................................');
    $nipPembimbing =
        optional($selectedKelompok?->pembimbing)->nip_pembimbing ??
        (optional($selectedPembimbing)->nip_pembimbing ?? '....................................');
    $namaKoordinator = 'Nurlaela Yulianti, S.Pd.';
    $nipKoordinator = '198807082020122007';
    $namaJurusan =
        optional(optional($students->first())->kelas)->jurusan->nama_jurusan ?? '....................................';
    $rowsToShow = max($students->count(), 12);
@endphp

@include('partials.kop_surat_default', [
    'kopOuterStyle' => 'margin-top: 0;',
    'kopLogoWidth' => 82,
    'kopHrStyle' => 'margin-top: 8px; border: 1px solid #111827;',
])

<div class="frm-title-block">
    <div class="frm-title">FORMULIR KEHADIRAN PESERTA</div>
    <div class="frm-title">PEMBEKALAN PRAKTIK KERJA LAPANGAN (PKL)</div>
</div>

<table class="frm-meta-table">
    <colgroup>
        <col style="width: 24.37%;">
        <col style="width: 75.63%;">
    </colgroup>
    <tr>
        <td class="frm-meta-label">Kode Formulir</td>
        <td class="frm-meta-value">: FRM-PKL-01</td>
    </tr>
    <tr>
        <td class="frm-meta-label">Revisi</td>
        <td class="frm-meta-value">: 00</td>
    </tr>
    <tr>
        <td class="frm-meta-label">Tanggal Berlaku</td>
        <td class="frm-meta-value">: {{ $tanggalBerlaku }}</td>
    </tr>
</table>

<h4 class="frm-section-title section-identitas">IDENTITAS KEGIATAN</h4>
<table class="frm-identity-table">
    <colgroup>
        <col style="width: 26.59%;">
        <col style="width: 73.41%;">
    </colgroup>
    <tr>
        <th>Hari / Tanggal</th>
        <td>{{ $tanggalFormulir->translatedFormat('l, d F Y') }}</td>
    </tr>
    <tr>
        <th>Waktu</th>
        <td>..........................................</td>
    </tr>
    <tr>
        <th>Tempat</th>
        <td>
            <span class="frm-checkbox" aria-hidden="true"></span> Lapangan Upacara
            &nbsp;&nbsp;&nbsp;&nbsp;
            <span class="frm-checkbox" aria-hidden="true"></span> GOR
        </td>
    </tr>
    <tr>
        <th>Jenis Kegiatan</th>
        <td>
            <span class="frm-checkbox" aria-hidden="true"></span> Pembekalan Ruangan
            &nbsp;&nbsp;&nbsp;&nbsp;
            <span class="frm-checkbox" aria-hidden="true"></span> Pembekalan Lapangan
        </td>
    </tr>
    <tr>
        <th>Guru Pembimbing</th>
        <td>{{ $namaPembimbing }}</td>
    </tr>
    <tr>
        <th>Jurusan</th>
        <td>{{ $namaJurusan }}</td>
    </tr>
</table>

<h4 class="frm-section-title section-daftar-hadir">DAFTAR HADIR PESERTA</h4>
<table class="frm-attendance-table">
    <thead>
        <tr>
            <th class="text-center" style="width: 5.50%;">No</th>
            <th style="width: 22.76%;">Nama Peserta</th>
            <th class="text-center" style="width: 12.34%;">NIS</th>
            <th class="text-center" style="width: 8.77%;">Hadir</th>
            <th class="text-center" style="width: 13.02%;">Terlambat</th>
            <th class="text-center" style="width: 7.58%;">Izin</th>
            <th class="text-center" style="width: 7.87%;">Sakit</th>
            <th class="text-center" style="width: 8.34%;">Alpha</th>
            <th class="text-center" style="width: 13.83%;">Tanda Tangan</th>
        </tr>
    </thead>
    <tbody>
        @for ($i = 0; $i < $rowsToShow; $i++)
            @php
                $siswa = $students->get($i);
                $isStriped = $i % 2 === 1;
                $nisValue = isset($siswa->nis) ? trim((string) $siswa->nis) : '';
            @endphp
            <tr class="{{ $isStriped ? 'row-striped' : '' }}">
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $siswa->nama_siswa ?? '' }}</td>
                <td class="text-center nis-cell">
                    <span class="nis-fixed">{{ $nisValue !== '' ? $nisValue : '__________' }}</span>
                </td>
                <td class="text-center"><span class="frm-checkbox" aria-hidden="true"></span></td>
                <td class="text-center"><span class="frm-checkbox" aria-hidden="true"></span></td>
                <td class="text-center"><span class="frm-checkbox" aria-hidden="true"></span></td>
                <td class="text-center"><span class="frm-checkbox" aria-hidden="true"></span></td>
                <td class="text-center"><span class="frm-checkbox" aria-hidden="true"></span></td>
                <td></td>
            </tr>
        @endfor
    </tbody>
</table>

<h4 class="frm-section-title section-catatan">CATATAN GURU PEMBIMBING</h4>
<div class="frm-lines">
    <div class="frm-line"></div>
    <div class="frm-line"></div>
    <div class="frm-line"></div>
</div>

<h4 class="frm-section-title section-pelanggaran">PELANGGARAN YANG DITEMUKAN</h4>
<table class="frm-violation-table">
    <colgroup>
        <col style="width: 31.02%;">
        <col style="width: 35.45%;">
        <col style="width: 33.53%;">
    </colgroup>
    <thead>
        <tr>
            <th>Nama Peserta</th>
            <th>Jenis Pelanggaran</th>
            <th>Tindak Lanjut</th>
        </tr>
    </thead>
    <tbody>
        @for ($i = 0; $i < 4; $i++)
            <tr class="{{ $i % 2 === 0 ? 'row-striped' : '' }}">
                <td></td>
                <td></td>
                <td></td>
            </tr>
        @endfor
    </tbody>
</table>

<h4 class="frm-section-title section-verifikasi">VERIFIKASI</h4>
<table class="frm-verify-table">
    <tr>
        <td>
            <div class="verify-title">Guru Pembimbing</div>
            <div class="verify-spacer"></div>
            <div><b><u>{{ $namaPembimbing }}</u></b></div>
            <div>NIP.{{ $nipPembimbing }}</div>
        </td>
        <td>
            <div class="verify-title">Koordinator Pembimbing</div>
            <div class="verify-spacer"></div>
            <div><b><u>{{ $namaKoordinator }}</u></b></div>
            <div>NIP.{{ $nipKoordinator }}</div>
        </td>
    </tr>
</table>

<h4 class="frm-section-title section-petunjuk">PETUNJUK PENGISIAN</h4>
<ul class="frm-note-list">
    <li>Beri tanda ✓ pada kolom status kehadiran yang sesuai.</li>
    <li>Status Hadir diberikan kepada peserta yang hadir sebelum registrasi ditutup.</li>
    <li>Status Terlambat diberikan kepada peserta yang hadir setelah registrasi ditutup namun masih diperkenankan
        mengikuti kegiatan sesuai ketentuan.</li>
    <li>Kolom Catatan Guru Pembimbing digunakan untuk mencatat informasi penting, seperti izin keluar, kondisi khusus,
        atau tindak lanjut pembinaan.</li>
    <li>Kode kelompok atau guru pembimbing dapat ditambahkan untuk mempercepat proses rekapitulasi oleh panitia.</li>
</ul>
