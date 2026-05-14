@extends('adminlte::page')

@section('title', 'Manajemen User')

@section('content')
    <div class="container pt-4">
        <div class="card">
            <div class="card-header">
                <h4 class="m-0">Manajemen User</h4>
                <div class="float-right">
                    <button class="btn btn-sm btn-primary" id="btnTambah">Tambah Data</button>
                </div>
            </div>
            <div class="card-body">
                <table id="userTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Jurusan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>

                <div class="modal fade" id="modalForm" tabindex="-1" role="dialog">
                    <div class="modal-dialog">
                        <form id="formUser">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalFormLabel">Form User</h5>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="id" id="id">
                                    <div class="form-group">
                                        <label for="name">Nama</label>
                                        <input type="text" name="name" id="name" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" name="email" id="email" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="password">Password <small class="text-muted" id="passwordHint">(wajib)</small></label>
                                        <input type="password" name="password" id="password" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="role">Role</label>
                                        <select name="role" id="role" class="form-control" required>
                                            <option value="">Pilih Role</option>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role }}">{{ $role }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="jurusan_id">Jurusan (opsional)</label>
                                        <select name="jurusan_id" id="jurusan_id" class="form-control">
                                            <option value="">Tanpa Jurusan</option>
                                            @foreach ($jurusan as $item)
                                                <option value="{{ $item->id }}">{{ $item->nama_jurusan }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-success" id="btnSimpan">Simpan</button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let table = $('#userTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('user-management.data') }}',
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'role', name: 'role' },
                    { data: 'jurusan_nama', name: 'jurusan_nama' },
                    { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
                ]
            });

            $('#btnTambah').click(function() {
                $('#formUser')[0].reset();
                $('#id').val('');
                $('#password').prop('required', true);
                $('#passwordHint').text('(wajib)');
                $('#modalFormLabel').text('Tambah User');
                $('#modalForm').modal('show');
            });

            $('#btnSimpan').click(function() {
                let id = $('#id').val();
                let url = id ? '{{ route('user-management.update', '') }}/' + id : '{{ route('user-management.store') }}';
                let method = id ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    type: method,
                    data: $('#formUser').serialize(),
                    success: function(response) {
                        $('#modalForm').modal('hide');
                        table.ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            timer: 1800,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        let message = 'Terjadi kesalahan';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            message = Object.values(xhr.responseJSON.errors).map(v => v[0]).join('\n');
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
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
                let id = $(this).data('id');
                let url = '{{ route('user-management.edit', ':id') }}'.replace(':id', id);

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(data) {
                        $('#id').val(data.id);
                        $('#name').val(data.name);
                        $('#email').val(data.email);
                        $('#role').val(data.role);
                        $('#jurusan_id').val(data.jurusan_id || '');
                        $('#password').val('');
                        $('#password').prop('required', false);
                        $('#passwordHint').text('(kosongkan jika tidak diubah)');
                        $('#modalFormLabel').text('Edit User');
                        $('#modalForm').modal('show');
                    }
                });
            });

            $(document).on('click', '.btnHapus', function() {
                let id = $(this).data('id');

                if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                    $.ajax({
                        url: '{{ route('user-management.destroy', '') }}/' + id,
                        type: 'DELETE',
                        success: function(response) {
                            table.ajax.reload();
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                timer: 1800,
                                showConfirmButton: false
                            });
                        },
                        error: function(xhr) {
                            let message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menghapus data';
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: message
                            });
                        }
                    });
                }
            });
        });
    </script>
@endsection
