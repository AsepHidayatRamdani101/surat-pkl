@extends('adminlte::page')

@section('title', 'Nilai Siswa Kelas')

@section('content_header')
    <div class="container-fluid">
        <h1 class="m-0">Nilai Siswa Kelas</h1>
        <small class="text-muted">Rekap nilai keseluruhan siswa dari kelas yang Anda ampu sebagai wali kelas</small>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        @if ($kelasList->isEmpty())
            <div class="alert alert-warning">Anda belum ditunjuk sebagai wali kelas dari kelas mana pun.</div>
        @else
            <div class="card card-outline card-primary">
                <div class="card-body d-flex flex-wrap align-items-center justify-content-between">
                    <form method="GET" class="form-inline my-1">
                        <label for="kelas_id" class="mr-2">Kelas</label>
                        <select name="kelas_id" id="kelas_id" class="form-control mr-2" onchange="this.form.submit()">
                            @foreach ($kelasList as $item)
                                <option value="{{ $item->id }}" @selected($kelas && $kelas->id === $item->id)>
                                    {{ $item->nama_kelas }}{{ $item->jurusan ? ' - ' . $item->jurusan->nama_jurusan : '' }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                    <a href="{{ route('wali-kelas.export', ['kelas_id' => $kelas->id]) }}" class="btn btn-success my-1">
                        <i class="fas fa-file-excel mr-1"></i> Ekspor Nilai (Excel)
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 col-6">
                    <div class="info-box"><span class="info-box-icon bg-info"><i class="fas fa-user-graduate"></i></span>
                        <div class="info-box-content"><span class="info-box-text">Jumlah Siswa</span><span class="info-box-number">{{ $ringkasan['siswa'] }}</span></div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="info-box"><span class="info-box-icon bg-warning"><i class="fas fa-tasks"></i></span>
                        <div class="info-box-content"><span class="info-box-text">Rata-rata Tugas</span><span class="info-box-number">{{ $ringkasan['tugas'] ?? '-' }}</span></div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="info-box"><span class="info-box-icon bg-danger"><i class="fas fa-star"></i></span>
                        <div class="info-box-content"><span class="info-box-text">Rata-rata Sidang</span><span class="info-box-number">{{ $ringkasan['sidang'] ?? '-' }}</span></div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="info-box"><span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                        <div class="info-box-content"><span class="info-box-text">Sudah Sidang</span><span class="info-box-number">{{ $ringkasan['sidangSelesai'] }} / {{ $ringkasan['siswa'] }}</span></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body table-responsive p-0">
                    <table class="table table-sm table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th rowspan="2" class="align-middle">No</th>
                                <th rowspan="2" class="align-middle">NIS</th>
                                <th rowspan="2" class="align-middle">Nama Siswa</th>
                                <th colspan="4" class="text-center">Absensi Pembekalan</th>
                                <th colspan="2" class="text-center">Tugas</th>
                                <th rowspan="2" class="align-middle text-center">Sikap</th>
                                <th rowspan="2" class="align-middle text-center">Praktik PKL</th>
                                <th colspan="2" class="text-center">Sidang</th>
                            </tr>
                            <tr>
                                <th class="text-center">H</th><th class="text-center">I</th><th class="text-center">S</th><th class="text-center">A</th>
                                <th class="text-center">Dinilai</th><th class="text-center">Rata-rata</th>
                                <th class="text-center">Nilai</th><th>Predikat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $row['nis'] }}</td>
                                    <td>{{ $row['nama'] }}</td>
                                    <td class="text-center">{{ $row['hadir'] }}</td>
                                    <td class="text-center">{{ $row['izin'] }}</td>
                                    <td class="text-center">{{ $row['sakit'] }}</td>
                                    <td class="text-center">{{ $row['alpa'] }}</td>
                                    <td class="text-center">{{ $row['tugas_dinilai'] }}</td>
                                    <td class="text-center">{{ $row['tugas_rata'] ?? '-' }}</td>
                                    <td class="text-center">{{ $row['sikap'] ?? '-' }}</td>
                                    <td class="text-center">{{ $row['praktik'] ?? '-' }}</td>
                                    <td class="text-center"><strong>{{ $row['sidang'] ?? '-' }}</strong></td>
                                    <td>{{ $row['predikat'] ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="13" class="text-center text-muted">Belum ada siswa di kelas ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
