@extends('adminlte::page')

@section('title', 'Upload Laporan PKL')

@section('content')
    <div class="container pt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        {{ session('success') }}
                    </div>
                @endif

                <div class="card sidang-card">
                    <div class="card-header d-flex align-items-center">
                        <span class="card-title-icon"><i class="fas fa-file-upload"></i></span>
                        <div>
                            <h4>Upload Laporan PKL untuk Sidang</h4>
                            <small>Unggah laporan PKL Anda dalam format PDF atau PPT</small>
                        </div>
                    </div>
                    <div class="card-body">
                        @if ($laporan)
                            <div class="alert alert-info">
                                <i class="fas fa-file-{{ $laporan->tipe_file === 'pdf' ? 'pdf' : 'powerpoint' }} mr-1"></i>
                                Laporan sudah diupload: <strong>{{ $laporan->judul }}</strong>
                                ({{ strtoupper($laporan->tipe_file) }},
                                {{ $laporan->updated_at->locale('id')->translatedFormat('d F Y H:i') }})
                                <a href="{{ asset('storage/' . $laporan->file_path) }}" target="_blank"
                                    class="btn btn-sm btn-outline-primary ml-2">
                                    <i class="fas fa-download mr-1"></i> Lihat / Unduh
                                </a>
                                <div class="mt-1 text-muted">Upload ulang untuk mengganti berkas lama.</div>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('sidang.laporan.store') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label>Judul Laporan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('judul') is-invalid @enderror"
                                    name="judul" value="{{ old('judul', $laporan->judul ?? '') }}" required>
                                @error('judul')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Berkas Laporan (PDF / PPT) <span class="text-danger">*</span></label>
                                <input type="file" class="form-control-file @error('file') is-invalid @enderror"
                                    name="file" accept=".pdf,.ppt,.pptx" required>
                                <small class="form-text text-muted">Format: PDF, PPT, atau PPTX. Maksimal 20 MB.</small>
                                @error('file')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Keterangan</label>
                                <textarea class="form-control" name="keterangan" rows="2">{{ old('keterangan', $laporan->keterangan ?? '') }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload mr-1"></i>
                                {{ $laporan ? 'Upload Ulang Laporan' : 'Upload Laporan' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    @include('sidang.partials.table_styles')
@endsection

@section('js')
    @include('sweetalert::alert')
@endsection
