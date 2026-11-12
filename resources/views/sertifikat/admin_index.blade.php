@extends('adminlte::page')

@section('title', 'Cetak Sertifikat PKL')

@section('content')
    <div class="container-fluid pt-4">
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-12">
                <div class="card app-card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                        <div class="d-flex align-items-center my-1">
                            <span class="card-title-icon"><i class="fas fa-certificate"></i></span>
                            <div>
                                <h4>Cetak Sertifikat PKL</h4>
                                <small>Cetak sertifikat pembekalan dan sertifikat pelaksanaan PKL per siswa</small>
                            </div>
                        </div>
                        <form method="GET" action="{{ route('sertifikat.admin.index') }}" class="form-inline my-1">
                            <input type="text" name="q" value="{{ $search }}" class="form-control form-control-sm mr-2"
                                placeholder="Cari nama / NIS siswa...">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-search mr-1"></i> Cari
                            </button>
                        </form>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>NIS</th>
                                        <th>Nama Siswa</th>
                                        <th>Kelas</th>
                                        <th>Tempat PKL</th>
                                        <th class="text-center">Penilaian</th>
                                        <th class="text-center">Sertifikat Pembekalan</th>
                                        <th class="text-center">Sertifikat Pelaksanaan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($siswaList as $siswa)
                                        <tr>
                                            <td>{{ $siswaList->firstItem() + $loop->index }}</td>
                                            <td>{{ $siswa->nis }}</td>
                                            <td>{{ $siswa->nama_siswa }}</td>
                                            <td>{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                                            <td>{{ $siswa->suratIzin->perusahaan->nama_perusahaan ?? '-' }}</td>
                                            <td class="text-center"><a href="{{ route('sertifikat.admin.penilaian.edit', $siswa) }}" class="btn btn-xs btn-outline-warning"><i class="fas fa-star mr-1"></i>Input Nilai</a></td>
                                            <td class="text-center">
                                                <a href="{{ route('sertifikat.admin.pembekalan.cetak', $siswa->id) }}"
                                                    class="btn btn-xs btn-outline-primary" target="_blank">
                                                    <i class="fas fa-eye mr-1"></i> Lihat
                                                </a>
                                                <a href="{{ route('sertifikat.admin.pembekalan.download', $siswa->id) }}"
                                                    class="btn btn-xs btn-primary">
                                                    <i class="fas fa-download mr-1"></i> PDF
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                @if ($siswa->suratIzin)
                                                    <a href="{{ route('sertifikat.admin.pelaksanaan.cetak', $siswa->id) }}"
                                                        class="btn btn-xs btn-outline-success" target="_blank">
                                                        <i class="fas fa-eye mr-1"></i> Lihat
                                                    </a>
                                                    <a href="{{ route('sertifikat.admin.pelaksanaan.download', $siswa->id) }}"
                                                        class="btn btn-xs btn-success">
                                                        <i class="fas fa-download mr-1"></i> PDF
                                                    </a>
                                                @else
                                                    <span class="badge badge-secondary">Belum ada tempat PKL</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">Tidak ada data siswa.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end">
                            {{ $siswaList->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
