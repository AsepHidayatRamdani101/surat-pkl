@extends('adminlte::page')

@section('title', 'Manajemen Sekolah')

@section('content')
    <div class="container pt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="d-inline">Manajemen Sekolah</h4>
                        <div class="float-right">
                            <button class="btn btn-sm btn-primary" id="btnTambah">Tambah Data</button>
                            <button class="btn btn-sm btn-danger" id="btnHapusMultiple" style="display: none;">Hapus Pilihan</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="sekolahTable" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 30px;"><input type="checkbox" id="checkAll" class="form-check"></th>
                                    <th>No</th>
                                    <th>Nama Kepala Sekolah</th>
                                    <th>NIP Kepala Sekolah</th>
                                    <th>Tanggal Mulai PKL</th>
                                    <th>Tanggal Selesai PKL</th>
                                    <th>Cap Sekolah</th>
                                    <th>TTD KS</th>
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
                <form id="formSekolah" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalFormLabel">Form Manajemen Sekolah</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id" id="id">

                            <div class="form-group">
                                <label for="nama_kepala_sekolah">Nama Kepala Sekolah</label>
                                <input type="text" name="nama_kepala_sekolah" id="nama_kepala_sekolah" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="nip_kepala_sekolah">NIP Kepala Sekolah</label>
                                <input type="text" name="nip_kepala_sekolah" id="nip_kepala_sekolah" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="tanggal_mulai_pkl">Tanggal Mulai PKL</label>
                                <input type="date" name="tanggal_mulai_pkl" id="tanggal_mulai_pkl" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="tanggal_selesai_pkl">Tanggal Selesai PKL</label>
                                <input type="date" name="tanggal_selesai_pkl" id="tanggal_selesai_pkl" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="cap_sekolah">Upload Cap Sekolah</label>
                                <input type="file" name="cap_sekolah" id="cap_sekolah" class="form-control">
                                <p id="capPreview" class="mt-2"></p>
                            </div>
                            <div class="form-group">
                                <label for="ttd_kepala_sekolah">Upload TTD Kepala Sekolah</label>
                                <input type="file" name="ttd_kepala_sekolah" id="ttd_kepala_sekolah" class="form-control">
                                <p id="ttdPreview" class="mt-2"></p>
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
                let table = $('#sekolahTable').DataTable({
                    ajax: '{{ route('sekolah.data') }}',
                    columns: [
                        {
                            data: 'checkbox',
                            name: 'checkbox',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'nama_kepala_sekolah',
                            name: 'nama_kepala_sekolah'
                        },
                        {
                            data: 'nip_kepala_sekolah',
                            name: 'nip_kepala_sekolah'
                        },
                        {
                            data: 'tanggal_mulai_pkl',
                            name: 'tanggal_mulai_pkl'
                        },
                        {
                            data: 'tanggal_selesai_pkl',
                            name: 'tanggal_selesai_pkl'
                        },
                        {
                            data: 'cap_sekolah',
                            name: 'cap_sekolah',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'ttd_kepala_sekolah',
                            name: 'ttd_kepala_sekolah',
                            orderable: false,
                            searchable: false
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
                    $('#modalFormLabel').html('Tambah Data Sekolah');
                    $('#formSekolah')[0].reset();
                    $('#id').val('');
                    $('#capPreview').text('');
                    $('#ttdPreview').text('');
                });

                $(document).on('click', '.btnEdit', function() {
                    let id = $(this).data('id');

                    $.ajax({
                        url: `/sekolah/${id}/edit`,
                        type: 'GET',
                        success: function(data) {
                            $('#id').val(data.id);
                            $('#nama_kepala_sekolah').val(data.nama_kepala_sekolah);
                            $('#nip_kepala_sekolah').val(data.nip_kepala_sekolah);
                            $('#tanggal_mulai_pkl').val(data.tanggal_mulai_pkl);
                            $('#tanggal_selesai_pkl').val(data.tanggal_selesai_pkl);
                            $('#capPreview').html(data.cap_sekolah_url ? `<a href="${data.cap_sekolah_url}" target="_blank">Lihat cap sekolah saat ini</a>` : 'Belum ada cap sekolah');
                            $('#ttdPreview').html(data.ttd_kepala_sekolah_url ? `<a href="${data.ttd_kepala_sekolah_url}" target="_blank">Lihat TTD KS saat ini</a>` : 'Belum ada TTD KS');
                            $('#modalFormLabel').html('Edit Data Sekolah');
                            $('#modalForm').modal('show');
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Gagal mengambil data.',
                            });
                        }
                    });
                });

                $(document).on('click', '.btn-simpan', function() {
                    let id = $('#id').val();
                    let url = id ? `/sekolah/${id}` : '{{ route('sekolah.store') }}';
                    let formData = new FormData($('#formSekolah')[0]);
                    if (id) {
                        formData.append('_method', 'PUT');
                    }

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        cache: false,
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
                            let message = 'Terjadi kesalahan.';
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                message = Object.values(xhr.responseJSON.errors).flat().join('\n');
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: message,
                            });
                        }
                    });
                });

                $(document).on('click', '.btnHapus', function() {
                    let id = $(this).data('id');
                    Swal.fire({
                        title: 'Yakin hapus data ini?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: `/sekolah/${id}`,
                                type: 'DELETE',
                                data: {
                                    _token: '{{ csrf_token() }}'
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

                $(document).on('click', '#checkAll', function() {
                    const isChecked = $(this).prop('checked');
                    $('.checkbox-sekolah').prop('checked', isChecked);
                    updateButtonHapusMultiple();
                });

                $(document).on('click', '.checkbox-sekolah', function() {
                    updateButtonHapusMultiple();
                    updateCheckAll();
                });

                function updateButtonHapusMultiple() {
                    const checkedCount = $('.checkbox-sekolah:checked').length;
                    if (checkedCount > 0) {
                        $('#btnHapusMultiple').show();
                    } else {
                        $('#btnHapusMultiple').hide();
                    }
                }

                function updateCheckAll() {
                    const totalCheckbox = $('.checkbox-sekolah').length;
                    const checkedCheckbox = $('.checkbox-sekolah:checked').length;
                    $('#checkAll').prop('checked', totalCheckbox === checkedCheckbox && totalCheckbox > 0);
                }

                $('#btnHapusMultiple').click(function() {
                    const selectedIds = [];
                    $('.checkbox-sekolah:checked').each(function() {
                        selectedIds.push($(this).val());
                    });

                    if (selectedIds.length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Peringatan',
                            text: 'Pilih minimal satu data untuk dihapus.'
                        });
                        return;
                    }

                    Swal.fire({
                        title: 'Yakin hapus ' + selectedIds.length + ' data ini?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '{{ route('sekolah.destroyMultiple') }}',
                                type: 'DELETE',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    ids: selectedIds
                                },
                                success: function() {
                                    table.ajax.reload();
                                    $('#checkAll').prop('checked', false);
                                    $('#btnHapusMultiple').hide();
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil',
                                        text: 'Data telah dihapus.',
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                },
                                error: function(xhr) {
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
