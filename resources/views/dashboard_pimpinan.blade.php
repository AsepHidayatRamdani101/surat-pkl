@extends('adminlte::page')

@section('title', 'Dashboard ' . $roleLabel)

@section('content_header')
    <div class="container-fluid">
        <h1 class="m-0">Dashboard {{ $roleLabel }}</h1>
        <small class="text-muted">
            @switch($role)
                @case('kepala_sekolah') Ringkasan keseluruhan pelaksanaan PKL @break
                @case('waka_kurikulum') Pembimbing, kelompok bimbingan, dan jadwal bimbingan/sidang @break
                @case('waka_hubin') Mitra industri, penempatan siswa, dan monitoring PKL @break
                @case('waka_kesiswaan') Kehadiran, sikap, kelengkapan, dan pembinaan siswa @break
                @case('waka_sarpras') Penggunaan ruangan bimbingan dan sidang @break
                @case('waka_bina_program') Program keahlian, kurikulum pembekalan, dan capaian nilai @break
            @endswitch
        </small>
    </div>
@endsection

@php
    $tanggalId = fn ($tgl) => $tgl ? $tgl->locale('id')->translatedFormat('d M Y') : '-';
    $jam = fn ($row) => substr($row->jam_mulai, 0, 5) . ' - ' . substr($row->jam_selesai, 0, 5);
@endphp

@section('content')
    <div class="container-fluid">
        <div class="row">
            @foreach ($stats as $stat)
                <div class="col-lg-4 col-md-6">
                    <div class="small-box bg-{{ $stat['color'] }}">
                        <div class="inner">
                            <h3>{{ $stat['value'] }}</h3>
                            <p class="mb-0">{{ $stat['label'] }}</p>
                            @if (!empty($stat['note']))
                                <small>{{ $stat['note'] }}</small>
                            @endif
                        </div>
                        <div class="icon"><i class="{{ $stat['icon'] }}"></i></div>
                        <a href="{{ $stat['url'] }}" class="small-box-footer">Lihat detail <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            @endforeach
        </div>

        @if (in_array($role, ['kepala_sekolah', 'waka_hubin']))
            <div class="row">
                <div class="{{ $role === 'waka_hubin' ? 'col-lg-6' : 'col-lg-7' }}">
                    <div class="card card-outline card-primary">
                        <div class="card-header"><h3 class="card-title">Penempatan PKL per Jurusan</h3></div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-sm table-striped mb-0">
                                <thead><tr><th>Jurusan</th><th class="text-center">Siswa</th><th class="text-center">Ditempatkan</th><th class="text-center">Belum</th><th style="width:30%">Capaian</th></tr></thead>
                                <tbody>
                                    @forelse ($perJurusan as $row)
                                        <tr>
                                            <td>{{ $row['nama'] }}</td>
                                            <td class="text-center">{{ $row['siswa'] }}</td>
                                            <td class="text-center">{{ $row['ditempatkan'] }}</td>
                                            <td class="text-center">{{ $row['belum'] }}</td>
                                            <td>
                                                <div class="progress progress-sm"><div class="progress-bar bg-success" style="width: {{ $row['persen'] }}"></div></div>
                                                <small>{{ $row['persen'] }}</small>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted">Belum ada data.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                @if ($role === 'kepala_sekolah')
                    <div class="col-lg-5">
                        <div class="card card-outline card-danger">
                            <div class="card-header"><h3 class="card-title">Sidang Mendatang</h3></div>
                            <div class="card-body table-responsive p-0">
                                <table class="table table-sm table-striped mb-0">
                                    <thead><tr><th>Tanggal</th><th>Siswa</th><th>Ruangan</th></tr></thead>
                                    <tbody>
                                        @forelse ($jadwalMendatang as $row)
                                            <tr>
                                                <td>{{ $tanggalId($row->tanggal) }}<br><small class="text-muted">{{ $jam($row) }}</small></td>
                                                <td>{{ optional($row->siswa)->nama_siswa }}<br><small class="text-muted">{{ optional(optional($row->siswa)->kelas)->nama_kelas }}</small></td>
                                                <td>{{ optional($row->ruangan)->nama_ruangan ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center text-muted">Belum ada jadwal sidang.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="col-lg-6">
                        <div class="card card-outline card-warning">
                            <div class="card-header"><h3 class="card-title">Perusahaan dengan Siswa Terbanyak</h3></div>
                            <div class="card-body table-responsive p-0">
                                <table class="table table-sm table-striped mb-0">
                                    <thead><tr><th>Perusahaan</th><th>Alamat</th><th class="text-center">Siswa</th></tr></thead>
                                    <tbody>
                                        @forelse ($topPerusahaan as $row)
                                            <tr>
                                                <td>{{ $row->nama_perusahaan }}</td>
                                                <td class="text-muted">{{ \Illuminate\Support\Str::limit($row->alamat, 40) }}</td>
                                                <td class="text-center"><span class="badge badge-primary">{{ $row->total }}</span></td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center text-muted">Belum ada penempatan.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer">
                                @foreach ($statusPkl as $status => $total)
                                    <span class="badge badge-info mr-1">{{ $status }}: {{ $total }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        @if ($role === 'waka_kurikulum')
            <div class="row">
                <div class="col-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Daftar Pembimbing & Beban Bimbingan</h3>
                            <div class="card-tools"><a href="{{ url('pembimbing') }}" class="btn btn-tool"><i class="fas fa-external-link-alt"></i></a></div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-sm table-striped mb-0">
                                <thead>
                                    <tr><th>Pembimbing</th><th>Jenis Guru</th><th>Jurusan</th><th class="text-center">Kuota</th><th class="text-center">Siswa</th><th class="text-center">Kelompok</th><th class="text-center">Jadwal Bimbingan</th><th class="text-center">Jadwal Sidang</th></tr>
                                </thead>
                                <tbody>
                                    @forelse ($pembimbingRows as $row)
                                        <tr>
                                            <td>{{ $row['nama'] }}<br><small class="text-muted">{{ $row['nip'] }}</small></td>
                                            <td>{{ $row['jenis_guru'] }}</td>
                                            <td>{{ $row['jurusan'] }}</td>
                                            <td class="text-center">{{ $row['kuota'] ?: '-' }}</td>
                                            <td class="text-center">
                                                <span class="badge badge-{{ $row['kuota'] && $row['siswa'] > $row['kuota'] ? 'danger' : 'info' }}">{{ $row['siswa'] }}</span>
                                            </td>
                                            <td class="text-center">{{ $row['kelompok'] }}</td>
                                            <td class="text-center">{{ $row['bimbingan'] }}</td>
                                            <td class="text-center">{{ $row['sidang'] }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="8" class="text-center text-muted">Belum ada pembimbing.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card card-outline card-warning">
                        <div class="card-header"><h3 class="card-title">Bimbingan Mendatang</h3></div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-sm table-striped mb-0">
                                <thead><tr><th>Tanggal</th><th>Jam</th><th>Siswa</th><th>Pembimbing</th><th>Ruangan</th></tr></thead>
                                <tbody>
                                    @forelse ($jadwalMendatang as $row)
                                        <tr>
                                            <td>{{ $tanggalId($row->tanggal) }}</td>
                                            <td>{{ $jam($row) }}</td>
                                            <td>{{ optional($row->siswa)->nama_siswa }}</td>
                                            <td>{{ optional($row->pembimbing)->nama_pembimbing ?? '-' }}</td>
                                            <td>{{ optional($row->ruangan)->nama_ruangan ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted">Belum ada jadwal bimbingan.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($role === 'waka_kesiswaan')
            <div class="row">
                <div class="col-lg-4">
                    <div class="card card-outline card-success">
                        <div class="card-header"><h3 class="card-title">Absensi Pembekalan</h3></div>
                        <ul class="list-group list-group-flush">
                            @foreach (['hadir' => 'Hadir', 'terlambat' => 'Terlambat', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpa' => 'Alpa'] as $key => $label)
                                <li class="list-group-item d-flex justify-content-between">{{ $label }} <span class="badge badge-secondary">{{ $absensi[$key] ?? 0 }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="card card-outline card-info">
                        <div class="card-header"><h3 class="card-title">Catatan Sikap</h3></div>
                        <ul class="list-group list-group-flush">
                            @foreach (['sangat_baik' => 'Sangat Baik', 'baik' => 'Baik', 'cukup' => 'Cukup', 'perlu_bimbingan' => 'Perlu Bimbingan'] as $key => $label)
                                <li class="list-group-item d-flex justify-content-between">{{ $label }} <span class="badge badge-secondary">{{ $sikap[$key] ?? 0 }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card card-outline card-danger">
                        <div class="card-header"><h3 class="card-title">Siswa Perlu Perhatian</h3></div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-sm table-striped mb-0">
                                <thead><tr><th>Siswa</th><th>Kelas</th><th class="text-center">Alpa</th><th class="text-center">Izin/Sakit</th><th class="text-center">Sikap Kurang</th></tr></thead>
                                <tbody>
                                    @forelse ($perluPerhatian as $row)
                                        <tr>
                                            <td>{{ $row->nama_siswa }}</td>
                                            <td>{{ $row->nama_kelas }}</td>
                                            <td class="text-center">{{ $row->alpa }}</td>
                                            <td class="text-center">{{ $row->izin_sakit }}</td>
                                            <td class="text-center">{{ $row->sikap_kurang }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted">Tidak ada siswa yang perlu perhatian khusus.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($role === 'waka_sarpras')
            @if ($bentrok->isNotEmpty())
                <div class="alert alert-danger">
                    <h5><i class="icon fas fa-exclamation-triangle"></i> Ada {{ $bentrok->count() }} bentrok jadwal ruangan</h5>
                    <ul class="mb-0">
                        @foreach ($bentrok as $item)
                            <li>{{ $item['ruangan'] }} — {{ $item['tanggal'] }}: {{ $item['a'] }} bertabrakan dengan {{ $item['b'] }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="row">
                <div class="col-lg-6">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Penggunaan Ruangan</h3>
                            <div class="card-tools"><a href="{{ url('sidang/ruangan') }}" class="btn btn-tool"><i class="fas fa-edit"></i> Kelola</a></div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-sm table-striped mb-0">
                                <thead><tr><th>Ruangan</th><th>Lokasi</th><th class="text-center">Kap.</th><th class="text-center">Sidang</th><th class="text-center">Bimbingan</th><th class="text-center">Hari Ini</th></tr></thead>
                                <tbody>
                                    @forelse ($ruangan as $row)
                                        <tr>
                                            <td>{{ $row['nama'] }}</td>
                                            <td>{{ $row['lokasi'] }}</td>
                                            <td class="text-center">{{ $row['kapasitas'] }}</td>
                                            <td class="text-center">{{ $row['sidang'] }}</td>
                                            <td class="text-center">{{ $row['bimbingan'] }}</td>
                                            <td class="text-center"><span class="badge badge-{{ $row['hari_ini'] ? 'success' : 'secondary' }}">{{ $row['hari_ini'] }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center text-muted">Belum ada ruangan.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card card-outline card-success">
                        <div class="card-header"><h3 class="card-title">Ruangan Terpakai Hari Ini</h3></div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-sm table-striped mb-0">
                                <thead><tr><th>Jam</th><th>Ruangan</th><th>Jenis</th><th>Siswa</th></tr></thead>
                                <tbody>
                                    @forelse ($jadwalHariIni as $row)
                                        <tr>
                                            <td>{{ $jam($row) }}</td>
                                            <td>{{ optional($row->ruangan)->nama_ruangan }}</td>
                                            <td><span class="badge badge-{{ $row->jenis === 'sidang' ? 'primary' : 'info' }}">{{ ucfirst($row->jenis) }}</span></td>
                                            <td>{{ optional($row->siswa)->nama_siswa }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted">Tidak ada ruangan terpakai hari ini.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card card-outline card-danger">
                        <div class="card-header"><h3 class="card-title">Penentuan Ruangan Sidang Mendatang</h3></div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-sm table-striped mb-0">
                                <thead><tr><th>Tanggal</th><th>Jam</th><th>Ruangan</th><th>Siswa</th><th>Pembimbing</th></tr></thead>
                                <tbody>
                                    @forelse ($jadwalMendatang as $row)
                                        <tr>
                                            <td>{{ $tanggalId($row->tanggal) }}</td>
                                            <td>{{ $jam($row) }}</td>
                                            <td>{{ optional($row->ruangan)->nama_ruangan ?? '-' }}</td>
                                            <td>{{ optional($row->siswa)->nama_siswa }}</td>
                                            <td>{{ optional($row->pembimbing)->nama_pembimbing ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted">Belum ada jadwal sidang mendatang.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($role === 'waka_bina_program')
            <div class="row">
                <div class="col-lg-8">
                    <div class="card card-outline card-primary">
                        <div class="card-header"><h3 class="card-title">Rekap per Program Keahlian</h3></div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-sm table-striped mb-0">
                                <thead><tr><th>Program Keahlian</th><th class="text-center">Kelas</th><th class="text-center">Siswa</th><th class="text-center">Pembimbing</th><th class="text-center">Ditempatkan</th><th class="text-center">Nilai Tugas</th><th class="text-center">Nilai Sidang</th></tr></thead>
                                <tbody>
                                    @forelse ($jurusanRows as $row)
                                        <tr>
                                            <td>{{ $row['nama'] }} <small class="text-muted">{{ $row['kode'] }}</small></td>
                                            <td class="text-center">{{ $row['kelas'] }}</td>
                                            <td class="text-center">{{ $row['siswa'] }}</td>
                                            <td class="text-center">{{ $row['pembimbing'] }}</td>
                                            <td class="text-center">{{ $row['penempatan'] }}</td>
                                            <td class="text-center">{{ $row['nilai_tugas'] }}</td>
                                            <td class="text-center">{{ $row['nilai_sidang'] }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" class="text-center text-muted">Belum ada jurusan.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card card-outline card-danger">
                        <div class="card-header"><h3 class="card-title">Kriteria Penilaian Sidang</h3></div>
                        <ul class="list-group list-group-flush">
                            @forelse ($kriteria as $item)
                                <li class="list-group-item d-flex justify-content-between">{{ $item->nama_kriteria }} <span class="badge badge-primary">{{ $item->bobot }}%</span></li>
                            @empty
                                <li class="list-group-item text-muted">Belum ada kriteria.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('css')
    <style>
        .small-box .inner h3 { font-size: 2rem; }
        .small-box .icon { font-size: 60px; }
    </style>
@endsection
