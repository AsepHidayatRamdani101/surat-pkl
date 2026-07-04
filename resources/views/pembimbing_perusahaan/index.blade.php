@extends('adminlte::page')

@section('title', 'Data Pembimbing Perusahaan')

@section('content')
    <div class="container pt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Data Pembimbing Perusahaan</h4>
                        <a href="{{ route('pembimbing-perusahaan.export-excel') }}" class="btn btn-sm btn-success">Export
                            Excel</a>
                        <button class="btn btn-sm btn-secondary" id="btnImport">Import Excel</button>

                    </div>
                    <div class="card-body">
                        <table id="pembimbingTable" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Perusahaan</th>
                                    <th>Nama</th>
                                    <th>NIP</th>
                                    <th>Jabatan</th>
                                    <th>No HP</th>
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
                            <input type="hidden" name="perusahaan_id" id="perusahaan_id">
                            <div class="form-group">
                                <label for="nama_perusahaan_display">Perusahaan</label>
                                <input type="text" id="nama_perusahaan_display" class="form-control" readonly>
                            </div>
                            <div class="form-group">
                                <label for="nama">Nama</label>
                                <input type="text" name="nama" id="nama" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="nip">NIP</label>
                                <input type="text" name="nip" id="nip" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="jabatan_pembimbing">Jabatan</label>
                                <input type="text" name="jabatan_pembimbing" id="jabatan_pembimbing" class="form-control"
                                    required>
                            </div>
                            <div class="form-group">
                                
                                <label for="jenis_kelamin">Jenis Kelamin</label>
                                <select name="jenis_kelamin" id="jenis_kelamin" class="form-control" required>
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="Laki-Laki">Laki-laki</option>
                                    <span aria-hidden="true">&times;</span>
                                    </button>
                            </div>
                            <div class="modal-body">
                                <form action="{{ route('pembimbing-perusahaan.import') }}" method="post"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group">
                                        <label for="file">File Excel</label>
                                        <input type="file" name="file" id="file" class="form-control" required>
                                    </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Import</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                </form>
            </div>
        </div>
    </div>
    </div>
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <style>
        #pembimbingTable .btn {
            margin-right: 4px;
            margin-bottom: 4px;
        }
    </style>
@endsection


@section('js')
    @include('sweetalert::alert')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

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

            $('#btnImport').click(function() {
                $('#modalImport').modal('show');
            });


            let table = $('#pembimbingTable').DataTable({
                ajax: '{{ route('pembimbing-perusahaan.data') }}',
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
                        data: 'nama_pembimbing',
                        name: 'nama_pembimbing'
                    },
                    {
                        data: 'NIP',
                        name: 'NIP'
                    },
                    {
                        data: 'jabatan',
                        name: 'jabatan'
                    },
                    {
                        data: 'nohp',
                        name: 'nohp'
                    },
                    {
                        data: 'aksi',
                        name: 'aksi',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $(document).on('click', '.btnTambahPerusahaan', function() {
                let data = $(this).data();
                $('#modalForm').modal('show');
                $('#modalFormLabel').html('Tambah Data Pembimbing');
                $('#formPembimbing').trigger('reset');
                $('#id').val('');
                $('#perusahaan_id').val(data.perusahaan_id);
                $('#nama_perusahaan_display').val(data.nama_perusahaan);
            });

            $(document).on('click', '.btnEdit', function() {
                let data = $(this).data();
                $('#nama').val(data.nama);
                $('#nip').val(data.nip);
                $('#id').val(data.id);
                $('#jabatan_pembimbing').val(data.jabatan);
                $('#jenis_kelamin').val(data.jenis);
                $('#no_hp_pembimbing').val(data.nohp);
                $('#perusahaan_id').val(data.perusahaan_id);
                $('#nama_perusahaan_display').val(data.nama_perusahaan);
                $('#modalForm').modal('show');
                $('#modalFormLabel').html('Edit Data Pembimbing');
            });

            $('#no_hp_pembimbing').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    Simpan();
                }
            });

            $(document).on('click', '.btn-simpan', function() {

                Simpan();
            });

            function Simpan() {
                let id = $('#id').val();
                let url = id ? `/pembimbing-perusahaan/${id}` : '{{ route('pembimbing-perusahaan.store') }}';
                let method = id ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    type: method,
                    data: {
                        _token: '{{ csrf_token() }}',
                        nama_pembimbing: $('#nama').val(),
                        perusahaan_id: $('#perusahaan_id').val(),
                        NIP: $('#nip').val(),
                        jabatan: $('#jabatan_pembimbing').val(),
                        jenis_kelamin: $('#jenis_kelamin').val(),
                        nohp: $('#no_hp_pembimbing').val(),
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
            }

            //btnHapus
            $(document).on('click', '.btnHapus', function() {
                let id = $(this).data('id');
                let url = '{{ route('pembimbing-perusahaan.destroy', ':id') }}';
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
