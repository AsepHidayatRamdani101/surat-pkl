@extends('adminlte::page')

@section('title', 'Data Perusahaan')

@section('content')
    <div class="container pt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Data Perusahaan</h4>
                        <button class="btn btn-sm btn-primary ms-auto" id="btnTambah">Tambah Data</button>
                    </div>
                    <div class="card-body">
                        <table id="perusahaanTable" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Perusahaan</th>
                                    <th>Alamat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalForm" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <form id="formPerusahaan" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalFormLabel">Form Perusahaan</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">

                            <input type="hidden" name="id" id="id">
                            <div class="form-group">
                                <label for="nama_perusahaan">Nama Perusahaan</label>
                                <input type="text" name="nama_perusahaan" id="nama_perusahaan" class="form-control"
                                    required>
                            </div>
                            <div class="form-group">
                                <label for="alamat">Alamat</label>
                                <input type="text" name="alamat" id="alamat" class="form-control" required>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success btn-simpan">Simpan</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('js')
    @include('sweetalert::alert')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            let table = $('#perusahaanTable').DataTable({
                ajax: '{{ route('perusahaan.data') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'nama_perusahaan',
                        name: 'nama_perusahaan'
                    },
                    {
                        data: 'alamat',
                        name: 'alamat'
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
                $('#modalForm').modal('show');
                $('#modalFormLabel').html('Tambah Data Perusahaan');
                $('#formPerusahaan').trigger('reset');
            });

            $(document).on('click', '.btnEdit', function() {
                let data = $(this).data();
                $('#nama_perusahaan').val(data.nama);
                $('#alamat').val(data.alamat);
                $('#id').val(data.id);
                $('#modalForm').modal('show');
                $('#modalFormLabel').html('Edit Data Perusahaan');
            });

            $(document).on('click', '.btn-simpan', function() {
                let id = $('#id').val();
                let url = id ? `/perusahaan/${id}` : '{{ route('perusahaan.store') }}';
                let method = id ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    type: method,
                    data: {
                        _token: '{{ csrf_token() }}',
                        nama_perusahaan: $('#nama_perusahaan').val(),
                        alamat: $('#alamat').val(),
                    },
                    success: function() {
                        $('#modalForm').modal('hide');
                        table.ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Data telah disimpan.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Terjadi kesalahan.',
                        });
                    }
                });
            });

            //btnHapus
            $(document).on('click', '.btnHapus', function() {
                let id = $(this).data('id');
                let url = '{{ route('perusahaan.destroy', ':id') }}';
                url = url.replace(':id', id);
                Swal.fire({
                    title: 'Yakin hapus data ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}',
                            },
                            success: function() {
                                table.ajax.reload();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: 'Data telah dihapus.',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            },
                            error: function(xhr) {
                                console.log(xhr.responseText);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'Terjadi kesalahan.',
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection

