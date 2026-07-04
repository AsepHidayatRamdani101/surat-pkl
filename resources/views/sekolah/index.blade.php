@extends('adminlte::page')

@section('title', 'Manajemen Sekolah')

@section('content')
    <div class="container pt-4">
        <div class="card">
            <div class="card-header">
                <h4 class="m-0">Manajemen Sekolah</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-7">
                        <form id="formSekolah" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="id" id="id" value="{{ $sekolah->id ?? '' }}">

                            <div class="form-group">
                                <label for="nama_kepala_sekolah">Nama Kepala Sekolah</label>
                                <input type="text" name="nama_kepala_sekolah" id="nama_kepala_sekolah"
                                    class="form-control" value="{{ $sekolah->nama_kepala_sekolah ?? '' }}" required>
                            </div>

                            <div class="form-group">
                                <label for="nip_kepala_sekolah">NIP Kepala Sekolah</label>
                                <input type="text" name="nip_kepala_sekolah" id="nip_kepala_sekolah" class="form-control"
                                    value="{{ $sekolah->nip_kepala_sekolah ?? '' }}" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="tanggal_mulai_pkl">Tanggal Mulai PKL</label>
                                    <input type="date" name="tanggal_mulai_pkl" id="tanggal_mulai_pkl"
                                        class="form-control"
                                        value="{{ optional($sekolah?->tanggal_mulai_pkl)->format('Y-m-d') }}" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="tanggal_selesai_pkl">Tanggal Selesai PKL</label>
                                    <input type="date" name="tanggal_selesai_pkl" id="tanggal_selesai_pkl"
                                        class="form-control"
                                        value="{{ optional($sekolah?->tanggal_selesai_pkl)->format('Y-m-d') }}" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="cap_sekolah">Upload Cap Sekolah</label>
                                <input type="file" name="cap_sekolah" id="cap_sekolah" class="form-control"
                                    accept="image/*">
                            </div>

                            <div class="form-group">
                                <label for="ttd_kepala_sekolah">Upload TTD Kepala Sekolah</label>
                                <input type="file" name="ttd_kepala_sekolah" id="ttd_kepala_sekolah" class="form-control"
                                    accept="image/*">
                            </div>

                            <button type="button" class="btn btn-success btn-simpan">Simpan</button>
                        </form>
                    </div>

                    <div class="col-md-5 mt-4 mt-md-0">
                        <div class="border rounded p-3 h-100">
                            <h5 class="mb-3">Gambar Tersimpan</h5>

                            <div class="mb-3">
                                <label class="font-weight-bold d-block">Cap Sekolah</label>
                                <img id="capPreviewImage" src="{{ $sekolah?->cap_sekolah_url ?? '' }}" alt="Cap Sekolah"
                                    class="img-fluid border rounded {{ $sekolah?->cap_sekolah_url ? '' : 'd-none' }}"
                                    style="max-height: 220px; object-fit: contain;">
                                <p id="capPreviewText" class="text-muted {{ $sekolah?->cap_sekolah_url ? 'd-none' : '' }}">
                                    Belum ada cap sekolah.</p>
                            </div>

                            <div>
                                <label class="font-weight-bold d-block">TTD Kepala Sekolah</label>
                                <img id="ttdPreviewImage" src="{{ $sekolah?->ttd_kepala_sekolah_url ?? '' }}"
                                    alt="TTD Kepala Sekolah"
                                    class="img-fluid border rounded {{ $sekolah?->ttd_kepala_sekolah_url ? '' : 'd-none' }}"
                                    style="max-height: 220px; object-fit: contain;">
                                <p id="ttdPreviewText"
                                    class="text-muted {{ $sekolah?->ttd_kepala_sekolah_url ? 'd-none' : '' }}">Belum ada
                                    TTD kepala sekolah.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
@endsection

@section('js')
    @include('sweetalert::alert')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            const originalSwalFire = Swal.fire.bind(Swal);
            Swal.fire = function(options, ...args) {
                if (typeof options === 'object' && options !== null) {
                    const merged = {
                        confirmButtonColor: '#0d6efd',
                        cancelButtonColor: '#6c757d',
                        ...options,
                    };

                    if (merged.showCancelButton) {
                        merged.confirmButtonText = merged.confirmButtonText || 'Ya, lanjut';
                        merged.cancelButtonText = merged.cancelButtonText || 'Batal';
                    } else {
                        merged.confirmButtonText = merged.confirmButtonText || 'OK';
                    }

                    if (merged.icon === 'success' && merged.timer === undefined && merged.showConfirmButton ===
                        undefined) {
                        merged.timer = 1800;
                        merged.showConfirmButton = false;
                    }

                    return originalSwalFire(merged, ...args);
                }

                return originalSwalFire(options, ...args);
            };

            function showImagePreview(inputId, imageId, textId) {
                const input = document.getElementById(inputId);
                const imageEl = document.getElementById(imageId);
                const textEl = document.getElementById(textId);

                if (!input || !input.files || !input.files[0]) {
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    imageEl.src = e.target.result;
                    imageEl.classList.remove('d-none');
                    textEl.classList.add('d-none');
                };
                reader.readAsDataURL(input.files[0]);
            }

            $('#cap_sekolah').on('change', function() {
                showImagePreview('cap_sekolah', 'capPreviewImage', 'capPreviewText');
            });

            $('#ttd_kepala_sekolah').on('change', function() {
                showImagePreview('ttd_kepala_sekolah', 'ttdPreviewImage', 'ttdPreviewText');
            });

            $(document).on('click', '.btn-simpan', function() {
                let id = $('#id').val();
                let url = id ? `/sekolah/${id}` : '{{ route('sekolah.store') }}';
                let formData = new FormData($('#formSekolah')[0]);

                if (id) {
                    formData.append('_method', 'PUT');
                }

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    cache: false,
                    success: function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Data manajemen sekolah berhasil disimpan.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let message = 'Terjadi kesalahan.';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            message = Object.values(xhr.responseJSON.errors).flat().join('\n');
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: message,
                        });
                    }
                });
            });
        });
    </script>
@endsection
