@php($rows = collect($assessmentRows ?? []))
<div class="certificate-back" style="page-break-before:always;position:relative;width:297mm;height:210mm;margin:0;background:url('{{ $backgroundBackDataUri ?? '' }}') center/cover no-repeat;font-family:{{ $fontFamily ?? 'Georgia, serif' }};overflow:hidden;">
    <div style="position:absolute;left:12%;right:12%;top:18%;bottom:12%;">
        <h2 style="text-align:center;margin:0 0 14px;font-size:20pt;">DAFTAR NILAI</h2>
        <div style="font-size:10pt;margin-bottom:12px;"><strong>Nama:</strong> {{ $siswa->nama_siswa }} &nbsp;&nbsp; <strong>NIS:</strong> {{ $siswa->nis }} &nbsp;&nbsp; <strong>Jurusan:</strong> {{ $siswa->kelas->jurusan->nama_jurusan ?? '-' }}</div>
        <table style="width:100%;border-collapse:collapse;background:rgba(255,255,255,.88);font-size:11pt;">
            <thead><tr><th style="border:1px solid #333;padding:7px;width:8%;">No.</th><th style="border:1px solid #333;padding:7px;">Aspek Penilaian</th><th style="border:1px solid #333;padding:7px;width:16%;">Nilai</th><th style="border:1px solid #333;padding:7px;width:24%;">Predikat</th></tr></thead>
            <tbody>@forelse($rows as $i=>$row)<tr><td style="border:1px solid #333;padding:7px;text-align:center;">{{ $i+1 }}</td><td style="border:1px solid #333;padding:7px;">{{ $row['aspek'] }}</td><td style="border:1px solid #333;padding:7px;text-align:center;">{{ $row['nilai'] ?? '-' }}</td><td style="border:1px solid #333;padding:7px;text-align:center;">{{ $row['predikat'] ?? '-' }}</td></tr>@empty<tr><td colspan="4" style="border:1px solid #333;padding:18px;text-align:center;">Aspek penilaian belum diatur.</td></tr>@endforelse</tbody>
        </table>
    </div>
    <div style="position:absolute;right:12%;bottom:5%;width:32%;text-align:center;font-size:10pt;line-height:1.35;">
        <div>Pembimbing DUDIKA</div>
        <div style="height:42px;"></div>
        <div style="font-weight:600;">{{ $namaPembimbing ?? '____________________' }}</div>
        <div>NIP. {{ $nipPembimbing ?? '-' }}</div>
    </div>
</div>
