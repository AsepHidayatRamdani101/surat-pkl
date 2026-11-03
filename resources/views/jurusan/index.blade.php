@extends('adminlte::page')

@section('title', 'Data Jurusan')

@section('content')
    <div class="container-fluid pt-4">
        <div class="card app-card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center my-1">
                    <span class="card-title-icon"><i class="fas fa-graduation-cap"></i></span>
                    <div>
                        <h4>Data Jurusan</h4>
                        <small>Kelola kode & nama jurusan sekolah</small>
                    </div>
                </div>
                <div class="my-1">
                    <button class="btn btn-outline-success mr-2" id="btnImport">
                        <i class="fas fa-file-import mr-1"></i> Import Data
                    </button>
                    <button class="btn btn-primary" id="btnTambah">
                        <i class="fas fa-plus mr-1"></i> Tambah Data
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table id="jurusanTable" class="table table-striped table-hover" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Kode Jurusan</th>
                            <th>Nama Jurusan</th>
                            <th style="width: 100px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                </table>

                <div class="modal fade" id="modalImport" tabindex="-1" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Import Data Jurusan</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <p class="text-muted mb-3">
                                    <a href="{{ route('jurusan.downloadTemplate') }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-download"></i> Download Format Template
                                    </a>
                                </p>
                                <form id="formImportJurusan" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="file">File Excel (Format: Nama Jurusan | Kode Jurusan)</label>
                                        <input type="file" name="file" id="file" class="form-control"
                                            accept=".xlsx,.xls,.csv" required>
                                        <small class="form-text text-muted">Format kolom: Kolom A = Nama Jurusan, Kolom B =
                                            Kode Jurusan</small>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                                <button type="button" class="btn btn-primary" id="btnImportFile">
                                    <i class="fas fa-file-import mr-1"></i> Import
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="modalForm" tabindex="-1" role="dialog">
                    <div class="modal-dialog">
                        <form id="formJurusan">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalFormLabel">Form Jurusan</h5>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="id" id="id">
                                    <div class="form-group">
                                        <label for="kode_jurusan">Kode Jurusan</label>
                                        <input type="text" name="kode_jurusan" id="kode_jurusan" class="form-control"
                                            required>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label for="nama_jurusan">Nama Jurusan</label>
                                        <input type="text" name="nama_jurusan" id="nama_jurusan" class="form-control"
                                            required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary"
                                        data-dismiss="modal">Batal</button>
                                    <button type="button" class="btn btn-primary" id="btnSimpan">
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
            let table = $('#jurusanTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('jurusan.data') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'kode_jurusan',
                        name: 'kode_jurusan'
                    },
                    {
                        data: 'nama_jurusan',
                        name: 'nama_jurusan'
                    },
                    {
                        data: 'aksi',
                        name: 'aksi',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ]
            });

            $('#btnTambah').click(function() {
                $('#formJurusan')[0].reset();
                $('#id').val('');
                $('#modalFormLabel').text('Tambah Jurusan');
                $('#modalForm').modal('show');
            });

            $('#btnImport').click(function() {
                $('#formImportJurusan')[0].reset();
                $('#modalImport').modal('show');
            });

            $('#btnSimpan').click(function() {
                let id = $('#id').val();
                let url = id ? '{{ route('jurusan.update', '') }}/' + id : '{{ route('jurusan.store') }}';
                let method = id ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    type: method,
                    data: $('#formJurusan').serialize(),
                    success: function(response) {
                        $('#modalForm').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                        });
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON?.errors || {};
                        let message = xhr.responseJSON?.message ||
                            'Terjadi kesalahan saat menyimpan data.';

                        if (Object.keys(errors).length > 0) {
                            message = Object.values(errors).map(function(item) {
                                return item[0];
                            }).join('\n');
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: message,
                        });
                    }
                });
            });

            $('#btnImportFile').click(function() {
                let formData = new FormData($('#formImportJurusan')[0]);
                $.ajax({
                    url: '{{ route('jurusan.import') }}',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        $('#modalImport').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                        });
                    },
                    error: function(xhr) {
                        let response = xhr.responseJSON || {};
                        let message = response.message || 'Terjadi kesalahan saat import data.';

                        if (response.errors && response.errors.length > 0) {
                            message += '\n\nDetail Error:\n';
                            response.errors.forEach(function(error) {
                                message += error + '\n';
                            });
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal import',
                            text: message,
                        });
                    }
                });
            });

            $(document).on('click', '.btnEdit', function() {
                let id = $(this).data('id');
                $.ajax({
                    url: `jurusan/${id}/edit`,
                    type: 'GET',
                    success: function(data) {
                        $('#id').val(data.id);
                        $('#kode_jurusan').val(data.kode_jurusan);
                        $('#nama_jurusan').val(data.nama_jurusan);
                        $('#modalFormLabel').text('Edit Jurusan');
                        $('#modalForm').modal('show');
                    }
                });
            });

            $(document).on('click', '.btnHapus', function() {
                let id = $(this).data('id');
                konfirmasiHapus({
                    title: 'Hapus data ini?',
                    text: 'Data yang dihapus tidak bisa dikembalikan.'
                }, () => {
                    $.ajax({
                        url: '{{ route('jurusan.destroy', '') }}/' + id,
                        type: 'DELETE',
                        success: function(response) {
                            table.ajax.reload(null, false);
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
                                    'Terjadi kesalahan saat menghapus data.',
                            });
                        }
                    });
                });
            });
        });
    </script>
@endsection
