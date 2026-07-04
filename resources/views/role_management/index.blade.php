@extends('adminlte::page')

@section('title', 'Manajemen Role')

@section('content')
    <div class="container pt-4">
        <div class="card">
            <div class="card-header">
                <h4 class="m-0">Manajemen Role</h4>
                <div class="float-right">
                    <button class="btn btn-sm btn-primary" id="btnTambah">Tambah Data</button>
                </div>
            </div>
            <div class="card-body">
                <table id="roleTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Role</th>
                            <th>Aksi</th>
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
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-success" id="btnSimpan">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('js')
    @include('sweetalert::alert')

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
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
                        searchable: false
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
                        table.ajax.reload();
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

                Swal.fire({
                    title: 'Hapus data ini?',
                    text: 'Data yang dihapus tidak bisa dikembalikan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        success: function(response) {
                            table.ajax.reload();
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
