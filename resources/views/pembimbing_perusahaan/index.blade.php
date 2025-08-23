@extends('adminlte::page')

@section('title', 'Data Pembimbing Perusahaan')

@section('content')
    <div class="container pt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Data Pembimbing Perusahaan</h4>
                        <button class="btn btn-sm btn-primary ms-auto" id="btnTambah">Tambah Data</button>
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
                            <div class="form-group">
                                <div>
                                    <label for="perusahaan_id">Perusahaan</label>
                                </div>

                                <select name="perusahaan_id" id="perusahaan_id" class="form-control select2" required>
                                    <option value="">Pilih Perusahaan</option>
                                    @foreach ($perusahaan as $pr)
                                        <option value="{{ $pr->id }}">{{ $pr->nama_perusahaan }}</option>
                                    @endforeach
                                </select>
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
                                <input type="text" name="jabatan_pembimbing" id="jabatan_pembimbing" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="jenis_kelamin">Jenis Kelamin</label>
                                <select name="jenis_kelamin" id="jenis_kelamin" class="form-control" required>
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="Laki-Laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="no_hp_pembimbing">No HP</label>
                                <input type="text" name="no_hp_pembimbing" id="no_hp_pembimbing" class="form-control"
                                    required>
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        /* Fix select2 search tidak bisa diklik di dalam modal */
        .select2-container {
            z-index: 99999 !important;
        }

         /* Biar select2 presisi seperti input bootstrap */
        .select2-container .select2-selection--single {
            height: calc(2.25rem + 2px); /* sama seperti form-control bootstrap */
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 2.25rem; /* bikin text di tengah kotak */
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(2.25rem + 2px);
            right: 10px;
        }
    </style>
@endsection


@section('js')
    @include('sweetalert::alert')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.full.min.js"></script>

    <script>
        $(document).ready(function() {
            console.log("Select2 found:", $('.select2').length);

           $('#modalForm').on('shown.bs.modal', function () {
            $(this).find('.select2').select2({
                placeholder: "Pilih Perusahaan",
                allowClear: true,
                width: '100%',
                dropdownParent: $('#modalForm')
            });
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
                        data: 'perusahaan.nama_perusahaan',
                        name: 'perusahaan.nama_perusahaan'
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

            $('#btnTambah').click(function() {
                $('#modalForm').modal('show');
                $('#modalFormLabel').html('Tambah Data Pembimbing');
                $('#formPembimbing').trigger('reset');
                $('#perusahaan_id').select2({
                    placeholder: 'Pilih Perusahaan',
                    allowClear: true
                });
            });

            $(document).on('click', '.btnEdit', function() {
                let data = $(this).data();
                alert(data.jabatan);
                $('#nama').val(data.nama);
                $('#nip').val(data.nip);
                $('#id').val(data.id);
                $('#jabatan_pembimbing').val(data.jabatan);
                $('#jenis_kelamin').val(data.jenis);
                $('#no_hp_pembimbing').val(data.nohp);
                $('#perusahaan_id').val(data.perusahaan_id).trigger('change');
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
