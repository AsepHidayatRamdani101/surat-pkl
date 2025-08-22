@extends('adminlte::page')

@section('title', 'Upload Surat Kesediaan')

@section('content_header')
    <h1>Upload Surat Kesediaan</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Upload Surat Kesediaan</h3>
                </div>
                <div class="card-body">
                    <form id="formKesediaan" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="file_upload">Surat Kesediaan</label>
                            <input type="file" name="file_upload" id="file_upload" class="form-control @error('file_upload') is-invalid @enderror">
                            @error('file_upload')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
