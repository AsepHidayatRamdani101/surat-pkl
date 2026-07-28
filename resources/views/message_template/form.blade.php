@extends('adminlte::page')

@section('title', isset($messageTemplate) ? 'Edit Template' : 'Buat Template')

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ isset($messageTemplate) ? 'Edit Template' : 'Buat Template Baru' }}</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('message-template.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <form
                    action="{{ isset($messageTemplate) ? route('message-template.update', $messageTemplate) : route('message-template.store') }}"
                    method="POST">
                    @csrf
                    @if (isset($messageTemplate))
                        @method('PUT')
                    @endif

                    <div class="form-group">
                        <label for="nama_template">Nama Template <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_template') is-invalid @enderror"
                            id="nama_template" name="nama_template"
                            value="{{ isset($messageTemplate) ? $messageTemplate->nama_template : old('nama_template') }}"
                            placeholder="Contoh: Pengumuman PKL" required>
                        @error('nama_template')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="tipe_template">Tipe Template <span class="text-danger">*</span></label>
                        <select class="form-control @error('tipe_template') is-invalid @enderror" id="tipe_template"
                            name="tipe_template" required>
                            <option value="">-- Pilih Tipe --</option>
                            <option value="informasi"
                                {{ (isset($messageTemplate) && $messageTemplate->tipe_template == 'informasi') || old('tipe_template') == 'informasi' ? 'selected' : '' }}>
                                Informasi</option>
                            <option value="pengumuman"
                                {{ (isset($messageTemplate) && $messageTemplate->tipe_template == 'pengumuman') || old('tipe_template') == 'pengumuman' ? 'selected' : '' }}>
                                Pengumuman</option>
                            <option value="undangan"
                                {{ (isset($messageTemplate) && $messageTemplate->tipe_template == 'undangan') || old('tipe_template') == 'undangan' ? 'selected' : '' }}>
                                Undangan</option>
                            <option value="lainnya"
                                {{ (isset($messageTemplate) && $messageTemplate->tipe_template == 'lainnya') || old('tipe_template') == 'lainnya' ? 'selected' : '' }}>
                                Lainnya</option>
                        </select>
                        @error('tipe_template')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="isi_template">Isi Template <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('isi_template') is-invalid @enderror" id="isi_template" name="isi_template"
                            rows="8" placeholder="Tulis konten pesan di sini (maksimal 1000 karakter)..." maxlength="1000" required>{{ isset($messageTemplate) ? $messageTemplate->isi_template : old('isi_template') }}</textarea>
                        <small class="form-text text-muted">
                            Karakter: <span id="char_count">0</span>/1000
                        </small>
                        @error('isi_template')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> {{ isset($messageTemplate) ? 'Perbarui' : 'Simpan' }} Template
                        </button>
                        <a href="{{ route('message-template.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(function() {
            var textarea = $('#isi_template');

            // Update character count on load
            $('#char_count').text(textarea.val().length);

            // Update character count on input
            textarea.on('input', function() {
                $('#char_count').text($(this).val().length);
            });
        });
    </script>
@endsection
