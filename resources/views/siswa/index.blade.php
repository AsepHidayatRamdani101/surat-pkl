@extends('adminlte::page')

@section('title', 'Data Siswa')

@section('content')
    <div class="container-fluid pt-4">
        @if (($statusFilter ?? null) === 'belum_terdaftar')
            <div class="alert alert-warning">
                <strong>Jumlah siswa belum mendaftar:</strong> {{ $jumlahBelumMendaftar }} siswa
            </div>
        @endif

        <div class="card app-card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center my-1">
                    <span class="card-title-icon"><i class="fas fa-user-graduate"></i></span>
                    <div>
                        <h4>Data Siswa</h4>
                        <small>Kelola data siswa, akun login, import & export</small>
                    </div>
                </div>
                <div class="my-1 d-flex align-items-center flex-wrap">
                    <select id="filterStatusAkunSiswa" class="form-control form-control-sm mr-2" style="width: 200px;">
                        <option value="">Semua Status Akun</option>
                        <option value="without" selected>Belum Punya Akun</option>
                        <option value="with">Sudah Punya Akun</option>
                    </select>
                    <button class="btn btn-outline-info btn-sm mr-2" id="btnGenerateAkunSiswa">
                        <i class="fas fa-user-plus mr-1"></i> Generate Akun
                    </button>
                    <a href="{{ route('siswa.export-pdf') }}" class="btn btn-outline-danger btn-sm mr-2">
                        <i class="fas fa-file-pdf mr-1"></i> Export PDF
                    </a>
                    <button class="btn btn-outline-success btn-sm mr-2" id="btnImport">
                        <i class="fas fa-file-excel mr-1"></i> Import
                    </button>
                    <button class="btn btn-danger btn-sm mr-2" id="btnHapusMultiple" style="display: none;">
                        <i class="fas fa-trash mr-1"></i> Hapus Pilihan
                    </button>
                    <button class="btn btn-primary btn-sm" id="btnTambah">
                        <i class="fas fa-plus mr-1"></i> Tambah Data
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table id="siswaTable" class="table table-striped table-hover" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 30px;"><input type="checkbox" id="checkAll" class="form-check"></th>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Jurusan</th>
                            <th>Status</th>
                            <th>Status Akun</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                </table>

                <!-- Modal Import -->
                <div class="modal fade" id="modalImport" tabindex="-1" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalImportLabel">Import Data Siswa</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <p class="text-muted mb-3">
                                    <a href="{{ route('siswa.downloadTemplate') }}" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-download"></i> Download Format Template
                                    </a>
                                </p>
                                <form id="formImportSiswa" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="file">File Excel</label>
                                        <input type="file" name="file" id="file" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <button type="button" class="btn btn-primary" id="btnImportSiswa">Import</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Form -->
                <div class="modal fade" id="modalForm" tabindex="-1" role="dialog">
                    <div class="modal-dialog">
                        <form id="formSiswa" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalFormLabel">Form Siswa</h5>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="id" id="id">
                                    <div class="form-group">
                                        <label for="nis">NIS</label>
                                        <input type="text" name="nis" id="nis" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="nama_siswa">Nama Siswa</label>
                                        <input type="text" name="nama_siswa" id="nama_siswa" class="form-control"
                                            required>
                                    </div>
                                    <div class="form-group">
                                        <label for="kelas_id">Kelas</label>
                                        <select name="kelas_id" id="kelas_id" class="form-control" required>
                                            <option value="">Pilih Kelas</option>
                                            @foreach ($kelas as $row)
                                                <option value="{{ $row->id }}">{{ $row->nama_kelas }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                                    <button type="button" class="btn btn-primary btn-simpan">
                                        <i class="fas fa-save mr-1"></i> Simpan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    @include('partials.table_styles')
@endsection

@section('js')
    @include('partials.table_scripts')

    <script>
        $(document).ready(function() {
            const dataSiswaUrl = @json(route('siswa.data') . (($statusFilter ?? null) === 'belum_terdaftar' ? '?status=belum_terdaftar' : ''));

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

            function showGenerateLoading(titleText) {
                Swal.fire({
                    title: titleText,
                    text: 'Mohon tunggu, proses sedang berjalan...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            }

            let table = $('#siswaTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: dataSiswaUrl,
                    data: function(d) {
                        d.account_status = $('#filterStatusAkunSiswa').val();
                    }
                },
                columns: [{
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'nis',
                        name: 'nis'
                    },
                    {
                        data: 'nama_siswa',
                        name: 'nama_siswa'
                    },
                    {
                        data: 'kelas.nama_kelas',
                        name: 'kelas.nama_kelas'
                    },
                    {
                        data: 'kelas.jurusan.nama_jurusan',
                        name: 'kelas.jurusan.nama_jurusan'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'status_akun',
                        name: 'status_akun',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'aksi',
                        name: 'aksi',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                ]
            });

            $('#filterStatusAkunSiswa').change(function() {
                table.ajax.reload();
            });

            $('#btnTambah').click(function() {
                $('#modalForm').modal('show');
                $('#modalFormLabel').html('Tambah Data Siswa');
                $('#formSiswa').attr('action', '{{ route('siswa.store') }}');
                $('#formSiswa').trigger('reset');
            });

            $(document).on('click', '.btnEdit', function() {
                let id = $(this).data('id');
                let url = '{{ route('siswa.edit', ':id') }}';
                url = url.replace(':id', id);

                $.get(url, function(response) {
                    $('#modalForm').modal('show');
                    $('#modalFormLabel').html('Edit Data Siswa');
                    $('#formSiswa').attr('action', '{{ route('siswa.update', ':id') }}');
                    $('#formSiswa').attr('action', $('#formSiswa').attr('action').replace(':id',
                        response.id));
                    $('#nis').val(response.nis);
                    $('#nama_siswa').val(response.nama_siswa);
                    $('#kelas_id').val(response.kelas_id);
                    $('#id').val(response.id);
                });
            });

            $('#btnImport').click(function() {
                $('#modalImport').modal('show');
            });

            $('#btnGenerateAkunSiswa').click(function() {
                Swal.fire({
                    title: 'Generate akun siswa?',
                    text: 'Username akan menggunakan NIS dan password default siswa12345.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, generate',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    showGenerateLoading('Sedang generate akun siswa');

                    $.ajax({
                        url: '{{ route('siswa.generate-accounts') }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON?.message ||
                                    'Terjadi kesalahan saat generate akun siswa.',
                            });
                        }
                    });
                });
            });

            $('#btnImportSiswa').click(function() {
                let formData = new FormData($('#formImportSiswa')[0]);
                $.ajax({
                    url: '{{ route('siswa.import') }}',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function() {
                        $('#modalImport').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Data siswa berhasil diimport.',
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal import',
                            text: xhr.responseJSON?.message ||
                                'Terjadi kesalahan saat import data siswa.',
                        });
                    }
                });
            });

            $(document).on('click', '.btnHapus', function() {
                let id = $(this).data('id');
                let url = '{{ route('siswa.destroy', ':id') }}';
                url = url.replace(':id', id);

                konfirmasiHapus({
                    title: 'Hapus data ini?',
                    text: 'Data yang dihapus tidak bisa dikembalikan.'
                }, () => {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        success: function() {
                            table.ajax.reload(null, false);
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Data siswa berhasil dihapus.',
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON?.message ||
                                    'Gagal menghapus data siswa.',
                            });
                        }
                    });
                });
            });

            $(document).on('click', '.btn-simpan', function() {
                let id = $('#id').val();
                let url = id ? '{{ route('siswa.update', ':id') }}'.replace(':id', id) :
                    '{{ route('siswa.store') }}';
                let method = id ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    type: method,
                    data: {
                        _token: '{{ csrf_token() }}',
                        nis: $('#nis').val(),
                        nama_siswa: $('#nama_siswa').val(),
                        kelas_id: $('#kelas_id').val(),
                    },
                    success: function() {
                        $('#modalForm').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Data siswa berhasil disimpan.',
                        });
                    },
                    error: function(xhr) {
                        let message = xhr.responseJSON?.message ||
                            'Terjadi kesalahan saat menyimpan data siswa.';
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

            $(document).on('click', '#checkAll', function() {
                const isChecked = $(this).prop('checked');
                $('.checkbox-siswa').prop('checked', isChecked);
                updateButtonHapusMultiple();
            });

            $(document).on('click', '.checkbox-siswa', function() {
                updateButtonHapusMultiple();
                updateCheckAll();
            });

            function updateButtonHapusMultiple() {
                const checkedCount = $('.checkbox-siswa:checked').length;
                if (checkedCount > 0) {
                    $('#btnHapusMultiple').show();
                } else {
                    $('#btnHapusMultiple').hide();
                }
            }

            function updateCheckAll() {
                const totalCheckbox = $('.checkbox-siswa').length;
                const checkedCheckbox = $('.checkbox-siswa:checked').length;
                $('#checkAll').prop('checked', totalCheckbox === checkedCheckbox && totalCheckbox > 0);
            }

            $('#btnHapusMultiple').click(function() {
                const selectedIds = [];
                $('.checkbox-siswa:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian',
                        text: 'Pilih minimal satu data untuk dihapus',
                    });
                    return;
                }

                konfirmasiHapus({
                    title: 'Hapus data terpilih?',
                    text: 'Yakin hapus ' + selectedIds.length + ' data siswa ini?'
                }, () => {
                    $.ajax({
                        url: '{{ route('siswa.destroyMultiple') }}',
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}',
                            ids: selectedIds
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                            });
                            table.ajax.reload(null, false);
                            $('#btnHapusMultiple').hide();
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON?.message ||
                                    'Terjadi kesalahan',
                            });
                        }
                    });
                });
            });
        });
    </script>
@endsection
