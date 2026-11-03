@extends('adminlte::page')

@section('title', 'Manajemen Role')

@section('content')
    <div class="container-fluid pt-4">
        <div class="card app-card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center my-1">
                    <span class="card-title-icon"><i class="fas fa-user-shield"></i></span>
                    <div>
                        <h4>Manajemen Role</h4>
                        <small>Kelola role / hak akses pengguna aplikasi</small>
                    </div>
                </div>
                <div class="my-1">
                    <button class="btn btn-primary" id="btnTambah">
                        <i class="fas fa-plus mr-1"></i> Tambah Data
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table id="roleTable" class="table table-striped table-hover" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Nama Role</th>
                            <th style="width: 100px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalForm" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form id="formRole">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalFormLabel">Form Role</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="id">
                        <div class="form-group mb-0">
                            <label for="name">Nama Role</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="btnSimpan">
                            <i class="fas fa-save mr-1"></i> Simpan
                        </button>
                    </div>
                </div>
            </form>
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
            const table = $('#roleTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('role-management.data') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name'
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
                $('#formRole')[0].reset();
                $('#id').val('');
                $('#modalFormLabel').text('Tambah Role');
                $('#modalForm').modal('show');
            });

            $('#btnSimpan').click(function() {
                const id = $('#id').val();
                const url = id ? '{{ route('role-management.update', '') }}/' + id :
                    '{{ route('role-management.store') }}';
                const method = id ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    type: method,
                    data: $('#formRole').serialize(),
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
                        let message = 'Terjadi kesalahan';
                        if (xhr.responseJSON?.errors) {
                            message = Object.values(xhr.responseJSON.errors).map(v => v[0])
                                .join('\n');
                        } else if (xhr.responseJSON?.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: message
                        });
                    }
                });
            });

            $(document).on('click', '.btnEdit', function() {
                const id = $(this).data('id');
                const url = '{{ route('role-management.edit', ':id') }}'.replace(':id', id);

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(data) {
                        $('#id').val(data.id);
                        $('#name').val(data.name);
                        $('#modalFormLabel').text('Edit Role');
                        $('#modalForm').modal('show');
                    }
                });
            });

            $(document).on('click', '.btnHapus', function() {
                const id = $(this).data('id');
                const url = '{{ route('role-management.destroy', ':id') }}'.replace(':id', id);

                konfirmasiHapus({
                    title: 'Hapus data ini?',
                    text: 'Data yang dihapus tidak bisa dikembalikan.'
                }, () => {
                    $.ajax({
                        url: url,
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
                            const message = xhr.responseJSON?.message ||
                                'Gagal menghapus data';
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: message
                            });
                        }
                    });
                });
            });
        });
    </script>
@endsection
