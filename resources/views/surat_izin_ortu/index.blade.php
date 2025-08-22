@extends('adminlte::page')

@section('title', 'Surat Izin Orang Tua')


@section('content')
    <div class="container pt-4">
        <div class="card">
            <div class="card-header">
                <h4>Surat Izin Orang Tua</h4>
                <button class="btn btn-sm btn-primary mb-3" id="btnTambah">Tambah Surat Izin</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="izin-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Siswa</th>
                                <th>Nama Ortu</th>
                                <th>Alamat Ortu</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        {{-- Modal --}}
        <div class="modal fade" id="modalIzin" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg">
                <form id="formIzin">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Form Surat Izin</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>

                        <div class="modal-body">
                            <input type="hidden" name="id" id="izin_id">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Siswa</label>
                                        <select name="siswa_id" id="siswa_id" class="form-control select2" required
                                            style="width: 100%">
                                            <option value="">Pilih Siswa</option>
                                            @foreach ($siswa as $row)
                                                <option value="{{ $row->id }}">{{ $row->nama_siswa }} -
                                                    {{ $row->kelas->nama_kelas }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nama Ortu</label>
                                        <input type="text" name="nama_ortu" id="nama_ortu" class="form-control" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>No HP Ortu</label>
                                        <input type="text" name="no_hp_ortu" id="no_hp_ortu" class="form-control"
                                            required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>No HP Siswa</label>
                                        <input type="text" name="no_hp_siswa" id="no_hp_siswa" class="form-control"
                                            required>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Alamat Ortu</label>
                                        <textarea name="alamat_ortu" id="alamat_ortu" class="form-control" required></textarea>
                                    </div>
                                </div>

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
    @stop

    @section('css')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            /* Menyamakan tinggi select2 dengan input form */
            .select2-container .select2-selection--single {
                height: calc(2.25rem + 2px);
                /* sama seperti .form-control */
                padding: 0.5rem 0.7rem;
                border: 1px solid #ced4da;
                border-radius: 0.25rem;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 1.5;
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 100%;
                right: 10px;
            }
        </style>
    @endsection

    @section('js')
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        @include('sweetalert::alert')
        <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>


        <script>
            $(document).ready(function() {



                let table = $('#izin-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('izin-ortu.data') }}",
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
                            data: 'nama_ortu',
                            name: 'nama_ortu'
                        },
                        {
                            data: 'alamat_ortu',
                            name: 'alamat_ortu'
                        },
                        {
                            data: 'updated_at',
                            name: 'updated_at'
                        },
                        {
                            data: 'aksi',
                            name: 'aksi',
                            orderable: false,
                            searchable: false
                        }
                    ]
                });

                $('#btnTambah').on('click', function() {
                    $('#formIzin')[0].reset();
                    $('#izin_id').val('');
                    $('#modalIzinLabel').html('Tambah Data');
                    $('#modalIzin').modal('show');
                });


                // Edit
                $(document).on('click', '.btn-edit', function() {
                    let data = $(this).data();
                    // console.log(data);

                    $('#izin_id').val(data.id);
                    $('#siswa_id').val(data.siswa).trigger('change.select2');
                    $('#nama_ortu').val(data.ortu);
                    $('#no_hp_ortu').val(data.nohp_ortu);
                    $('#no_hp_siswa').val(data.nohp_siswa);
                    $('#alamat_ortu').val(data.alamat);
                    $('#modalIzin').modal('show');
                });

                // Delete
                $(document).on('click', '.btn-hapus', function() {
                    let siswa = $(this).data('siswa');
                    console.log(siswa);




                    if (confirm('Yakin hapus data ini?')) {
                        $.ajax({
                            url: `/izin-ortu/${$(this).data('id')}`,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}',
                                siswa_id: siswa
                            },
                            success: function(data) {
                                // console.log(data);

                                table.ajax.reload();
                            }
                        });
                    }
                });

                $(document).on('click', '.btn-simpan', function() {
                    let url = '';
                    let method = '';
                    let id = $('#izin_id').val();
                    if (id == '') {
                        url = '{{ route('izin-ortu.store') }}';
                        method = 'POST';
                    } else {
                        url = `/izin-ortu/${id}`;
                        method = 'PUT';
                    }
                    $.ajax({
                        url: url,
                        type: method,
                        data: {
                            _token: '{{ csrf_token() }}',
                            siswa_id: $('#siswa_id').val(),
                            nama_ortu: $('#nama_ortu').val(),
                            alamat_ortu: $('#alamat_ortu').val(),
                            no_hp_ortu: $('#no_hp_ortu').val(),
                            no_hp_siswa: $('#no_hp_siswa').val(),
                        },
                        success: function() {
                            $('#modalIzin').modal('hide');
                            $('#izin-table').DataTable().ajax.reload();
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

                $('#modalIzin').on('shown.bs.modal', function() {
                    $('#siswa_id').select2({
                        placeholder: "-- Pilih Siswa --",
                        dropdownParent: $('#modalIzin'),
                        tags: true,
                        allowClear: true,

                    });
                });

            });
        </script>
    @stop
