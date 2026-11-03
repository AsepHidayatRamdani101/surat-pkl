@extends('adminlte::page')

@section('title', 'Data Sertifikat Pelaksanaan PKL')

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary mr-3">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
                <h1 class="m-0">Data Sertifikat Pelaksanaan PKL</h1>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid pt-3">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-7">
                <div class="card app-card">
                    <div class="card-header d-flex align-items-center">
                        <span class="card-title-icon"><i class="fas fa-user-tie"></i></span>
                        <div>
                            <h4 class="mb-0">Pemilik & Pembimbing Perusahaan/Instansi</h4>
                            <small>Data ini dijadikan rujukan pada sertifikat pelaksanaan PKL</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-1"></i>
                            Tempat PKL: <strong>{{ $tempatPkl->perusahaan->nama_perusahaan ?? '-' }}</strong>.
                            @if ($pemilikSistem || $pembimbingSistem)
                                Data di bawah sudah terisi dari sistem dan masih bisa Anda ubah.
                            @else
                                Silakan lengkapi data pemilik dan pembimbing perusahaan/instansi.
                            @endif
                        </div>

                        <form method="POST" action="{{ route('sertifikat.pelaksanaan.form.store') }}">
                            @csrf

                            <div class="form-group">
                                <label for="nama_pemilik_instansi">Nama Pemilik Perusahaan/Instansi <span class="text-danger">*</span></label>
                                <input type="text" name="nama_pemilik_instansi" id="nama_pemilik_instansi"
                                    class="form-control @error('nama_pemilik_instansi') is-invalid @enderror"
                                    value="{{ old('nama_pemilik_instansi', $namaPemilik) }}"
                                    placeholder="Contoh: H. Asep Saepudin" required>
                                @error('nama_pemilik_instansi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if ($pemilikSistem)
                                    <small class="form-text text-muted">
                                        <i class="fas fa-database mr-1"></i>Data dari sistem (menu Perusahaan): <strong>{{ $pemilikSistem }}</strong>
                                    </small>
                                @endif
                            </div>

                            <div class="form-group">
                                <label class="d-block">Apakah pemilik perusahaan/instansi sama dengan pembimbing perusahaan/instansi?</label>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="samaYa" name="pemilik_sama_pembimbing" value="1"
                                        class="custom-control-input"
                                        {{ old('pemilik_sama_pembimbing', $tempatPkl->pemilik_sama_pembimbing ? '1' : '0') === '1' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="samaYa">Ya, sama</label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="samaTidak" name="pemilik_sama_pembimbing" value="0"
                                        class="custom-control-input"
                                        {{ old('pemilik_sama_pembimbing', $tempatPkl->pemilik_sama_pembimbing ? '1' : '0') === '0' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="samaTidak">Tidak, berbeda</label>
                                </div>
                                <small class="form-text text-muted">Jika sama, cukup isi 1 nama pemilik saja.</small>
                            </div>

                            <div class="form-group" id="groupPembimbing">
                                <label for="nama_pembimbing_instansi">Nama Pembimbing Perusahaan/Instansi <span class="text-danger">*</span></label>
                                <input type="text" name="nama_pembimbing_instansi" id="nama_pembimbing_instansi"
                                    class="form-control @error('nama_pembimbing_instansi') is-invalid @enderror"
                                    value="{{ old('nama_pembimbing_instansi', $namaPembimbing) }}"
                                    placeholder="Contoh: Rina Marlina, S.Kom.">
                                @error('nama_pembimbing_instansi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if ($pembimbingSistem)
                                    <small class="form-text text-muted">
                                        <i class="fas fa-database mr-1"></i>Data dari sistem (menu Pembimbing Perusahaan): <strong>{{ $pembimbingSistem }}</strong>
                                    </small>
                                @endif
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Simpan Data
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card app-card">
                    <div class="card-header d-flex align-items-center">
                        <span class="card-title-icon"><i class="fas fa-certificate"></i></span>
                        <div>
                            <h4 class="mb-0">Sertifikat Saya</h4>
                            <small>Lihat dan unduh sertifikat PKL Anda</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <i class="fas fa-award text-warning mr-2"></i>
                                    <strong>Sertifikat Pembekalan PKL</strong>
                                </div>
                                <a href="{{ route('dashboard.siswa.cetak-sertifikat') }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye mr-1"></i> Lihat
                                </a>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <i class="fas fa-award text-success mr-2"></i>
                                    <strong>Sertifikat Pelaksanaan PKL</strong><br>
                                    <small class="text-muted">Pastikan data pemilik & pembimbing sudah diisi</small>
                                </div>
                                <a href="{{ route('sertifikat.pelaksanaan.cetak') }}" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-eye mr-1"></i> Lihat
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(function () {
            function togglePembimbing() {
                const sama = $('input[name="pemilik_sama_pembimbing"]:checked').val() === '1';
                if (sama) {
                    $('#groupPembimbing').hide();
                    $('#nama_pembimbing_instansi').prop('required', false);
                } else {
                    $('#groupPembimbing').show();
                    $('#nama_pembimbing_instansi').prop('required', true);
                }
            }

            $('input[name="pemilik_sama_pembimbing"]').on('change', togglePembimbing);
            togglePembimbing();
        });
    </script>
@endsection
