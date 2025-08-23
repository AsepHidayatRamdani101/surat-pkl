@extends('adminlte::page')

@section('title', 'Data Pembimbing Sekolah')

@section('content')
    <div class="container pt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Data Pembimbing</h4>
                        <button class="btn btn-sm btn-primary ms-auto" id="btnTambah">Tambah Data</button>
                    </div>
                    <div class="card-body">
                        <table id="perusahaanTable" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>NIP</th>
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
                <form id="formPembimbing" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalFormLabel">Form Pembimbing</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">

                            <input type="hidden" name="id" id="id">
                            <div class="form-group">
                                <label for="nama">Nama</label>
                                <input type="text" name="nama" id="nama" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="nip">NIP</label>
                                <input type="text" name="nip" id="nip" class="form-control" required>
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
            let table = $('#pembimbingTable').DataTable({
                ajax: '{{ route('pembimbing.data') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'nama',
                        name: 'nama'
                    },
                    {
                        data: 'nip',
                        name: 'nip'
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
                $('#modalFormLabel').html('Tambah Data Pembimbing');
                $('#formPembimbing').trigger('reset');
            });

            $(document).on('click', '.btnEdit', function() {
                let data = $(this).data();
                $('#nama').val(data.nama);
                $('#nip').val(data.nip);
                $('#id').val(data.id);
                $('#modalForm').modal('show');
                $('#modalFormLabel').html('Edit Data Pembimbing');
            });

            $(document).on('click', '.btn-simpan', function() {
                let id = $('#id').val();
                let url = id ? `/pembimbing/${id}` : '{{ route('pembimbing.store') }}';
                let method = id ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    type: method,
                    data: {
                        _token: '{{ csrf_token() }}',
                        nama: $('#nama').val(),
                        nip: $('#nip').val(),
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
                let url = '{{ route('pembimbing.destroy', ':id') }}';
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

