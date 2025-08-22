@extends('adminlte::page')

@section('title', 'Surat Izin Orang Tua')

@section('content')
    <div class="container pt-4">
        <div class="card">
            <div class="card-header">
                <h4>Data Tempat PKL</h4>
                <button class="btn btn-sm btn-primary ms-auto" id="btnTambah">Tambah Data</button>
                <a href="{{ route('tempat-pkl.export-excel') }}" class="btn btn-sm btn-success">Export Excel</a>
            </div>
            <div class="card-body">
                <table id="tempatPklTable" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th>Perusahaan</th>
                            <th>Tgl Mulai</th>
                            <th>Tgl Selesai</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>

                <!-- Modal Upload Surat Kesediaan -->
                <div class="modal fade" id="modalUploadKesediaan" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-lg">

                        <form id="formUploadKesediaanSiswa" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalUploadKesediaanLabel">Upload Surat Kesediaan</h5>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" id="id_kesediaan" name="id_kesediaan">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="file_upload">Upload Surat Kesediaan</label>
                                                <input type="file" name="file_upload_kesediaan"
                                                    id="file_upload_kesediaan"
                                                    class="form-control @error('file_upload') is-invalid @enderror">
                                                @error('file_upload')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="nama_pembimbing">Nama Pembimbing</label>
                                                <input type="text" name="nama_pembimbing" id="nama_pembimbing"
                                                    class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="jabatan_pembimbing">Jabatan Pembimbing</label>
                                                <input type="text" name="jabatan_pembimbing" id="jabatan_pembimbing"
                                                    class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="no_hp_pembimbing">No HP Pembimbing</label>
                                                <input type="text" name="no_hp_pembimbing" id="no_hp_pembimbing"
                                                    class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="tugas_siswa">Tugas Siswa</label>
                                                <input type="text" name="tugas_siswa" id="tugas_siswa"
                                                    class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="nip">NIP</label>
                                                <input type="text" name="nip" id="nip" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                    <button type="button" class="btn btn-primary btnSimpanKesediaan">Simpan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal Form -->
                <div class="modal fade" id="modalForm" tabindex="-1" role="dialog">

                    <div class="modal-dialog modal-lg">
                        <form id="formTempatPkl" enctype="multipart/form-data">
                            @csrf

                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalFormLabel">Form Tempat PKL</h5>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <input type="hidden" id="id" name="id">
                                <div class="modal-body">
                                    <input type="hidden" name="id" id="id">
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <label for="siswa_id">Pilih Siswa</label>
                                            <select name="siswa_id[]" id="siswa_id" class="form-control select2"
                                                multiple style="width: 100%;">
                                                @foreach ($siswa as $siswa)
                                                    <option value="{{ $siswa->id }}">{{ $siswa->nama_siswa }} -
                                                        {{ $siswa->kelas->nama_kelas }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label for="perusahaan_id" class="form-label">Perusahaan</label>
                                            <select name="perusahaan_id" id="perusahaan_id" class="form-control">
                                                <option value="" selected disabled>--Pilih Perusahaan--</option>
                                                @foreach ($perusahaan as $p)
                                                    <option value="{{ $p->id }}">{{ $p->nama_perusahaan }}</option>
                                                @endforeach
                                                <option value="0" selected>--Lainnya--</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div id="tambahan">
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <label for="nama_perusahaan" class="form-label">Nama Perusahaan</label>
                                                <input type="text" name="nama_perusahaan" id="nama_perusahaan"
                                                    class="form-control" required>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label for="alamat_perusahaan" class="form-label">Alamat
                                                    Perusahaan</label>
                                                <input type="text" name="alamat_perusahaan" id="alamat_perusahaan"
                                                    class="form-control" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                                            <input type="date" name="tanggal_mulai" id="tanggal_mulai"
                                                class="form-control">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                                            <input type="date" name="tanggal_selesai" id="tanggal_selesai"
                                                class="form-control">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <label for="file_upload" class="form-label">Upload File</label>
                                            <input type="file" name="file_upload" id="file_upload"
                                                class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <p id="tampilkan_file"></p>
                                        </div>
                                    </div>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-success btn-simpan">Simpan</button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    <style>
        .select2-container--bootstrap-5 .select2-selection--single {
            height: 38px;
            /* padding: 6px 12px; */
            border-radius: 0.375rem;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple {
            min-height: 38px;
            /* padding: 6px 12px; */
            border-radius: 0.375rem;
        }

        .select2-selection__choice {
            background-color: #FFC40175;
            color: white;
            /* padding: 3px 8px; */
            margin-top: 4px;
            font-size: 14px
        }
    </style>
@endsection

@section('datatable', true)
@section('sweetalert', true)

@section('js')
    @include('sweetalert::alert')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#tambahan').hide();
            // Trigger change event on page load to set initial state

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            })

            $('.select2').select2({
                theme: 'bootstrap-5',
                placeholder: 'Pilih salah satu atau lebih...',
                allowClear: true,
                dropdownParent: $('#modalForm') // jika select2 berada dalam modal
            });

            let table = $('#tempatPklTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('tempat-pkl.index') }}',
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
                        data: 'perusahaan.nama_perusahaan',
                        name: 'perusahaan.nama_perusahaan'
                    },
                    {
                        data: 'tanggal_mulai',
                        name: 'tanggal_mulai'
                    },
                    {
                        data: 'tanggal_selesai',
                        name: 'tanggal_selesai'
                    },
                    {
                        data: 'siswa.status',
                        name: 'siswa.status'
                    },
                    {
                        data: 'aksi',
                        name: 'aksi',
                        orderable: false,
                        searchable: false
                    },
                ]
            });

            $(document).on('change', '#perusahaan_id', function() {
                let data = $(this).val();
                console.log(data);
                if (data == 0) {
                    $('#tambahan').show();
                }

            })



            $(document).on('click', '.btnUpdateKesediaan', function() {
                let data = $(this).data();
                $('#id_kesediaan').val(data.id);
                console.log(data);

                $('#modalUploadKesediaan').modal('show');
            })

            //btnSimpanKesediaan
            $(document).on('click', '.btnSimpanKesediaan', function() {
                let id = $('#id_kesediaan').val();
                let url = '{{ route('update-kesediaan', ':id') }}';
                url = url.replace(':id', id);
                let formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('nama_pembimbing', $('#nama_pembimbing').val());
                formData.append('_method', 'PUT');
                formData.append('jabatan_pembimbing', $('#jabatan_pembimbing').val());
                formData.append('no_hp_pembimbing', $('#no_hp_pembimbing').val());
                formData.append('NIP_pembimbing', $('#nip').val());
                formData.append('tugas_siswa', $('#tugas_siswa').val());

                // ambil file dari input file
                const file = $('#file_upload_kesediaan')[0].files[0];
                if (file) {
                    formData.append('file_upload_kesediaan', file);
                }

                console.log(formData);


                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(data) {
                        console.log(data);

                        $('#modalUploadKesediaan').modal('hide');
                        $('#tempatPklTable').DataTable().ajax.reload();
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                    }
                });
            });

            //btn-edit
            $(document).on('click', '.btn-edit', function() {
                let id = $(this).data('id');
                // console.log(id);

                $.ajax({
                    url: `/tempat-pkl/${id}/edit`,
                    type: 'GET',
                    success: function(data) {
                        console.log(data);

                        $('#id').val(data.id);
                        $('#siswa_id').val(data.siswa_id).trigger('change.select2');
                        $('#perusahaan_id').val(data.perusahaan_id).trigger('change');
                        $('#tanggal_mulai').val(new Date(data.tanggal_mulai)
                            .toISOString().slice(0, 10));
                        $('#tanggal_selesai').val(new Date(data.tanggal_selesai)
                            .toISOString().slice(0, 10));
                        $('#tampilkan_file').html(
                            `<a href="/storage/${data.surat_izin_path}" target="_blank">File Kesediaan</a>`
                        );
                        // $('#modalFormLabel').html('Edit Data Tempat PKL');
                    },
                });

                $('#modalForm').modal('show');
            });

            $('#btnTambah').on('click', function() {

                //clear semua form

                $('#siswa_id').val(null).trigger('change');
                $('#perusahaan_id').val('');
                $('#tanggal_mulai').val('');
                $('#tanggal_selesai').val('');
                $('#id').val('');
                $('#nama_perusahaan').val('');
                $('#alamat_perusahaan').val('');
                $('#modalFormLabel').html('Tambah Data Tempat PKL');
                $('#tambahan').hide();
                $('#modalForm').modal('show');
            });

            $(document).on('click', '.btn-simpan', function() {

                let formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('perusahaan_id', $('#perusahaan_id').val());
                formData.append('siswa_id', $('#siswa_id').val());
                formData.append('nama_perusahaan', $('#nama_perusahaan').val());
                formData.append('alamat_perusahaan', $('#alamat_perusahaan').val());
                formData.append('tanggal_mulai', $('#tanggal_mulai').val());
                formData.append('tanggal_selesai', $('#tanggal_selesai').val());
                formData.append('created_by', '{{ Auth::user()->id }}');

                // ambil file dari input file
                const file = $('#file_upload')[0].files[0];
                if (file) {
                    formData.append('file_upload', file);
                }

                console.log(file);


                let siswa_id = $('#siswa_id').val();
                if (siswa_id) {
                    siswa_id.forEach((id, index) => {
                        formData.append(`siswa_id[${index}]`, id);
                    });
                }

                let id = $('#id').val();
                let url = (id == '') ? '{{ route('tempat-pkl.store') }}' : `/tempat-pkl/${id}`;
                let method = (id == '') ? 'POST' :
                    'POST'; // tetap POST, Laravel akan terima _method PUT

                if (id != '') {
                    formData.append('_method', 'PUT');
                }

                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Data telah disimpan.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        $('#modalForm').modal('hide');
                        $('#tempatPklTable').DataTable().ajax.reload();
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




            $(document).on('click', '.btn-hapus', function() {
                Swal.fire({
                    title: 'Yakin?',
                    text: "Data akan dihapus!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, hapus!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/tempat-pkl/${$(this).data('id')}`,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function() {
                                Swal.fire(
                                    'Deleted!',
                                    'Data telah dihapus.',
                                    'success'
                                );
                                table.ajax.reload();
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
                })
            });
        });
    </script>
@endsection
