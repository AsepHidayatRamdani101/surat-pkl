@extends('adminlte::page')

@section('title', 'Data Tempat PKL')

@section('content')
    <div class="container pt-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="m-0">Data Tempat PKL</h4>
                <div>
                    <button class="btn btn-sm btn-primary" id="btnTambah">Tambah Data</button>
                    <a href="{{ route('tempat-pkl.export-excel') }}" class="btn btn-sm btn-success">Export Excel</a>
                </div>
            </div>
            <div class="card-body">
                <table id="tempatPklTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th>Perusahaan</th>
                            <th>Tgl Mulai</th>
                            <th>Tgl Selesai</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        <div class="modal fade" id="modalUploadKesediaan" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <form id="formUploadKesediaanSiswa" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalUploadKesediaanLabel">Upload Surat Kesediaan</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="id_kesediaan" name="id_kesediaan">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="file_upload_kesediaan">Upload Surat Kesediaan</label>
                                        <input type="file" name="file_upload_kesediaan" id="file_upload_kesediaan"
                                            class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nama_pembimbing">Nama Pembimbing</label>
                                        <input type="text" name="nama_pembimbing" id="nama_pembimbing"
                                            class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="jabatan_pembimbing">Jabatan Pembimbing</label>
                                        <input type="text" name="jabatan_pembimbing" id="jabatan_pembimbing"
                                            class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="no_hp_pembimbing">No HP Pembimbing</label>
                                        <input type="text" name="no_hp_pembimbing" id="no_hp_pembimbing"
                                            class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label for="tugas_siswa">Tugas Siswa</label>
                                        <input type="text" name="tugas_siswa" id="tugas_siswa" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label for="nip">NIP</label>
                                        <input type="text" name="nip" id="nip" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-primary btnSimpanKesediaan">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="modalForm" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <form id="formTempatPkl" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalFormLabel">Form Tempat PKL</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="id" name="id">

                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label for="siswa_id">Pilih Siswa</label>
                                    <select name="siswa_id[]" id="siswa_id" class="form-control select2" multiple
                                        style="width: 100%;">
                                        @foreach ($siswa as $item)
                                            <option value="{{ $item->id }}">{{ $item->nama_siswa }} -
                                                {{ $item->kelas->nama_kelas ?? '-' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label for="perusahaan_id">Perusahaan</label>
                                    <select name="perusahaan_id" id="perusahaan_id" class="form-control">
                                        <option value="" selected disabled>--Pilih Perusahaan--</option>
                                        @foreach ($perusahaan as $p)
                                            <option value="{{ $p->id }}">{{ $p->nama_perusahaan }}</option>
                                        @endforeach
                                        <option value="0">--Lainnya--</option>
                                    </select>
                                </div>
                            </div>

                            <div id="tambahan">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label for="nama_perusahaan">Nama Perusahaan</label>
                                        <input type="text" name="nama_perusahaan" id="nama_perusahaan"
                                            class="form-control">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label for="alamat_perusahaan">Alamat Perusahaan</label>
                                        <input type="text" name="alamat_perusahaan" id="alamat_perusahaan"
                                            class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label for="tanggal_mulai">Tanggal Mulai</label>
                                    <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label for="tanggal_selesai">Tanggal Selesai</label>
                                    <input type="date" name="tanggal_selesai" id="tanggal_selesai"
                                        class="form-control">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label for="file_upload">Upload File</label>
                                    <input type="file" name="file_upload" id="file_upload" class="form-control">
                                </div>
                                <div class="col-md-6 mb-2 d-flex align-items-end">
                                    <p id="tampilkan_file" class="mb-0"></p>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-success btn-simpan">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
@endsection

@section('js')
    @include('sweetalert::alert')

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('.select2').select2({
                theme: 'bootstrap-5',
                placeholder: 'Pilih satu atau lebih siswa...',
                allowClear: true,
                dropdownParent: $('#modalForm')
            });

            $('#tambahan').hide();

            const table = $('#tempatPklTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('tempat-pkl.index') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'siswa.nama_siswa',
                        name: 'siswa.nama_siswa'
                    },
                    {
                        data: 'perusahaan.nama_perusahaan',
                        name: 'perusahaan.nama_perusahaan'
                    },
                    {
                        data: 'tanggal_mulai',
                        name: 'tanggal_mulai'
                    },
                    {
                        data: 'tanggal_selesai',
                        name: 'tanggal_selesai'
                    },
                    {
                        data: 'siswa.status',
                        name: 'siswa.status',
                        defaultContent: '-'
                    },
                    {
                        data: 'aksi',
                        name: 'aksi',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $('#perusahaan_id').on('change', function() {
                const selected = $(this).val();
                if (String(selected) === '0') {
                    $('#tambahan').show();
                    $('#nama_perusahaan').prop('required', true);
                    $('#alamat_perusahaan').prop('required', true);
                } else {
                    $('#tambahan').hide();
                    $('#nama_perusahaan').prop('required', false).val('');
                    $('#alamat_perusahaan').prop('required', false).val('');
                }
            });

            $('#btnTambah').on('click', function() {
                $('#formTempatPkl')[0].reset();
                $('#id').val('');
                $('#siswa_id').val(null).trigger('change');
                $('#perusahaan_id').val('');
                $('#tampilkan_file').html('');
                $('#modalFormLabel').text('Tambah Data Tempat PKL');
                $('#tambahan').hide();
                $('#modalForm').modal('show');
            });

            $(document).on('click', '.btn-edit', function() {
                const id = $(this).data('id');

                $.ajax({
                    url: `/tempat-pkl/${id}/edit`,
                    type: 'GET',
                    success: function(data) {
                        $('#id').val(data.id);
                        $('#siswa_id').val([String(data.siswa_id)]).trigger('change');
                        $('#perusahaan_id').val(String(data.perusahaan_id)).trigger('change');
                        $('#tanggal_mulai').val(data.tanggal_mulai ? new Date(data
                            .tanggal_mulai).toISOString().slice(0, 10) : '');
                        $('#tanggal_selesai').val(data.tanggal_selesai ? new Date(data
                            .tanggal_selesai).toISOString().slice(0, 10) : '');

                        if (data.surat_izin_path) {
                            $('#tampilkan_file').html(
                                `<a href="/storage/${data.surat_izin_path}" target="_blank">Lihat File Saat Ini</a>`
                            );
                        } else {
                            $('#tampilkan_file').html('');
                        }

                        $('#modalFormLabel').text('Edit Data Tempat PKL');
                        $('#modalForm').modal('show');
                    }
                });
            });

            $(document).on('click', '.btn-simpan', function() {
                const id = $('#id').val();
                const url = id ? `/tempat-pkl/${id}` : '{{ route('tempat-pkl.store') }}';

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('perusahaan_id', $('#perusahaan_id').val() || '');
                formData.append('nama_perusahaan', $('#nama_perusahaan').val() || '');
                formData.append('alamat_perusahaan', $('#alamat_perusahaan').val() || '');
                formData.append('tanggal_mulai', $('#tanggal_mulai').val() || '');
                formData.append('tanggal_selesai', $('#tanggal_selesai').val() || '');
                formData.append('created_by', '{{ auth()->id() }}');

                const selectedSiswa = $('#siswa_id').val() || [];
                selectedSiswa.forEach(function(value, index) {
                    formData.append(`siswa_id[${index}]`, value);
                });

                const file = $('#file_upload')[0].files[0];
                if (file) {
                    formData.append('file_upload', file);
                }

                if (id) {
                    formData.append('_method', 'PUT');
                }

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        $('#modalForm').modal('hide');
                        table.ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message || 'Data telah disimpan.',
                        });
                    },
                    error: function(xhr) {
                        let message = xhr.responseJSON?.message ||
                            'Terjadi kesalahan saat menyimpan data.';
                        if (xhr.responseJSON?.errors) {
                            message = Object.values(xhr.responseJSON.errors).map(v => v[0])
                                .join('\n');
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: message,
                        });
                    }
                });
            });

            $(document).on('click', '.btnUpdateKesediaan', function() {
                const data = $(this).data();
                $('#formUploadKesediaanSiswa')[0].reset();
                $('#id_kesediaan').val(data.id);
                $('#modalUploadKesediaan').modal('show');
            });

            $(document).on('click', '.btnSimpanKesediaan', function() {
                const id = $('#id_kesediaan').val();
                let url = '{{ route('update-kesediaan', ':id') }}';
                url = url.replace(':id', id);

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('_method', 'PUT');
                formData.append('nama_pembimbing', $('#nama_pembimbing').val());
                formData.append('jabatan_pembimbing', $('#jabatan_pembimbing').val());
                formData.append('no_hp_pembimbing', $('#no_hp_pembimbing').val());
                formData.append('NIP_pembimbing', $('#nip').val());
                formData.append('tugas_siswa', $('#tugas_siswa').val());

                const file = $('#file_upload_kesediaan')[0].files[0];
                if (file) {
                    formData.append('file_upload_kesediaan', file);
                }

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        $('#modalUploadKesediaan').modal('hide');
                        table.ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message ||
                                'Data kesediaan berhasil disimpan.',
                        });
                    },
                    error: function(xhr) {
                        let message = xhr.responseJSON?.message ||
                            'Terjadi kesalahan saat menyimpan kesediaan.';
                        if (xhr.responseJSON?.errors) {
                            message = Object.values(xhr.responseJSON.errors).map(v => v[0])
                                .join('\n');
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: message,
                        });
                    }
                });
            });

            $(document).on('click', '.btn-hapus', function() {
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Yakin?',
                    text: 'Data akan dihapus.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    $.ajax({
                        url: `/tempat-pkl/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function() {
                            table.ajax.reload();
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Data telah dihapus.',
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON?.message ||
                                    'Terjadi kesalahan saat menghapus data.',
                            });
                        }
                    });
                });
            });
        });
    </script>
@endsection
