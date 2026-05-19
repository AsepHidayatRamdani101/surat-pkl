@extends('adminlte::page')

@section('title', 'Data Perusahaan')


@section('content')
    <div class="container pt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="d-inline">Data Perusahaan</h4>
                        <div class="float-right">
                            <button class="btn btn-sm btn-primary" id="btnTambah">Tambah Data</button>
                            <button class="btn btn-sm btn-danger" id="btnHapusMultiple" style="display: none;">Hapus Pilihan</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="perusahaanTable" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 30px;"><input type="checkbox" id="checkAll" class="form-check"></th>
                                    <th>No</th>
                                    <th>Nama Perusahaan</th>
                                    <th>Nama Pemilik</th>
                                    <th>No. Telp Pemilik</th>
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
            <div class="modal-dialog modal-lg">
                <form id="formPerusahaan" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalFormLabel">Form Perusahaan</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">

                            <input type="hidden" name="id" id="id">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nama_perusahaan">Nama Perusahaan</label>
                                        <input type="text" name="nama_perusahaan" id="nama_perusahaan" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="nama_pemilik_perusahaan">Nama Pemilik Perusahaan</label>
                                        <input type="text" name="nama_pemilik_perusahaan" id="nama_pemilik_perusahaan" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="telepon_pemilik_perusahaan">No. Telp Pemilik Perusahaan</label>
                                        <input type="text" name="telepon_pemilik_perusahaan" id="telepon_pemilik_perusahaan" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="alamat">Alamat</label>
                                        <input type="text" name="alamat" id="alamat" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="provinsi_id">Provinsi</label>
                                        <select name="provinsi_id" id="provinsi_id" class="form-control select2" required>
                                            <option value="">Pilih Provinsi</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="kabupaten_kota_id">Kabupaten/Kota</label>
                                        <select name="kabupaten_kota_id" id="kabupaten_kota_id" class="form-control select2" required disabled>
                                            <option value="">Pilih Kabupaten/Kota</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="kecamatan_id">Kecamatan</label>
                                        <select name="kecamatan_id" id="kecamatan_id" class="form-control select2" required disabled>
                                            <option value="">Pilih Kecamatan</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="desa_id">Desa</label>
                                        <select name="desa_id" id="desa_id" class="form-control select2" required disabled>
                                            <option value="">Pilih Desa</option>
                                        </select>
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
    </div>
    @endsection

    @section('css')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
        <style>
            .select2-container .select2-selection--single {
                height: calc(2.25rem + 2px);
                border: 1px solid #ced4da;
                padding: 0.375rem 0.75rem;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 1.5;
                padding-left: 0;
                margin-top: -2px;
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 100%;
                right: 6px;
            }
        </style>
    @endsection

    @section('js')
        @include('sweetalert::alert')
        <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script>
            $(document).ready(function() {
                const wilayahRoutes = {
                    provinsi: '{{ route('wilayah.provinsi') }}',
                    kabupaten: '{{ route('wilayah.kabupaten', ['provinceId' => '__PROVINCE__']) }}',
                    kecamatan: '{{ route('wilayah.kecamatan', ['regencyId' => '__REGENCY__']) }}',
                    desa: '{{ route('wilayah.desa', ['districtId' => '__DISTRICT__']) }}',
                };

                function resetSelect($select, placeholder, disabled = true) {
                    $select.empty().append(new Option(placeholder, ''));
                    $select.prop('disabled', disabled);
                }

                function initSelect2($select) {
                    if ($select.hasClass('select2-hidden-accessible')) {
                        $select.select2('destroy');
                    }

                    $select.select2({
                        width: '100%',
                        dropdownParent: $('#modalForm')
                    });
                }

                function loadProvinsi(selectedId = '') {
                    return $.getJSON(wilayahRoutes.provinsi, function(data) {
                        const $provinsi = $('#provinsi_id');
                        resetSelect($provinsi, 'Pilih Provinsi', false);

                        data.forEach(function(item) {
                            const option = new Option(item.name, item.id);
                            if (selectedId && String(item.id) === String(selectedId)) {
                                option.selected = true;
                            }
                            $provinsi.append(option);
                        });
                    });
                }

                function loadKabupaten(provinceId, selectedId = '') {
                    const $kabupaten = $('#kabupaten_kota_id');
                    resetSelect($kabupaten, 'Pilih Kabupaten/Kota', true);
                    resetSelect($('#kecamatan_id'), 'Pilih Kecamatan', true);
                    resetSelect($('#desa_id'), 'Pilih Desa', true);

                    if (!provinceId) {
                        return $.Deferred().resolve().promise();
                    }

                    return $.getJSON(wilayahRoutes.kabupaten.replace('__PROVINCE__', provinceId), function(data) {
                        $kabupaten.prop('disabled', false);

                        data.forEach(function(item) {
                            const option = new Option(item.name, item.id);
                            if (selectedId && String(item.id) === String(selectedId)) {
                                option.selected = true;
                            }
                            $kabupaten.append(option);
                        });
                    });
                }

                function loadKecamatan(regencyId, selectedId = '') {
                    const $kecamatan = $('#kecamatan_id');
                    resetSelect($kecamatan, 'Pilih Kecamatan', true);
                    resetSelect($('#desa_id'), 'Pilih Desa', true);

                    if (!regencyId) {
                        return $.Deferred().resolve().promise();
                    }

                    return $.getJSON(wilayahRoutes.kecamatan.replace('__REGENCY__', regencyId), function(data) {
                        $kecamatan.prop('disabled', false);

                        data.forEach(function(item) {
                            const option = new Option(item.name, item.id);
                            if (selectedId && String(item.id) === String(selectedId)) {
                                option.selected = true;
                            }
                            $kecamatan.append(option);
                        });
                    });
                }

                function loadDesa(districtId, selectedId = '') {
                    const $desa = $('#desa_id');
                    resetSelect($desa, 'Pilih Desa', true);

                    if (!districtId) {
                        return $.Deferred().resolve().promise();
                    }

                    return $.getJSON(wilayahRoutes.desa.replace('__DISTRICT__', districtId), function(data) {
                        $desa.prop('disabled', false);

                        data.forEach(function(item) {
                            const option = new Option(item.name, item.id);
                            if (selectedId && String(item.id) === String(selectedId)) {
                                option.selected = true;
                            }
                            $desa.append(option);
                        });
                    });
                }

                let table = $('#perusahaanTable').DataTable({
                    ajax: '{{ route('perusahaan.data') }}',
                    columns: [{
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
                            data: 'nama_perusahaan',
                            name: 'nama_perusahaan'
                        },
                        {
                            data: 'nama_pemilik_perusahaan',
                            name: 'nama_pemilik_perusahaan'
                        },
                        {
                            data: 'telepon_pemilik_perusahaan',
                            name: 'telepon_pemilik_perusahaan'
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
                    resetSelect($('#provinsi_id'), 'Pilih Provinsi', true);
                    resetSelect($('#kabupaten_kota_id'), 'Pilih Kabupaten/Kota', true);
                    resetSelect($('#kecamatan_id'), 'Pilih Kecamatan', true);
                    resetSelect($('#desa_id'), 'Pilih Desa', true);
                    initSelect2($('#provinsi_id'));
                    initSelect2($('#kabupaten_kota_id'));
                    initSelect2($('#kecamatan_id'));
                    initSelect2($('#desa_id'));
                    loadProvinsi();
                });

                $(document).on('click', '.btnEdit', function() {
                    let data = $(this).data();
                    $('#formPerusahaan').trigger('reset');
                    $('#nama_perusahaan').val(data.nama);
                    $('#nama_pemilik_perusahaan').val(data.namaPemilik);
                    $('#telepon_pemilik_perusahaan').val(data.teleponPemilik);
                    $('#alamat').val(data.alamat);
                    $('#id').val(data.id);
                    $('#modalForm').modal('show');
                    $('#modalFormLabel').html('Edit Data Perusahaan');

                    initSelect2($('#provinsi_id'));
                    initSelect2($('#kabupaten_kota_id'));
                    initSelect2($('#kecamatan_id'));
                    initSelect2($('#desa_id'));

                    $.getJSON('{{ route('perusahaan.wilayah', ['id' => '__ID__']) }}'.replace('__ID__', data.id), function(response) {
                        const selected = response.selected || {};

                        resetSelect($('#provinsi_id'), 'Pilih Provinsi', false);
                        response.provinsi.forEach(function(item) {
                            $('#provinsi_id').append(new Option(item.name, item.id, false, String(item.id) === String(selected.provinsi_id)));
                        });

                        resetSelect($('#kabupaten_kota_id'), 'Pilih Kabupaten/Kota', false);
                        response.kabupaten.forEach(function(item) {
                            $('#kabupaten_kota_id').append(new Option(item.name, item.id, false, String(item.id) === String(selected.kabupaten_kota_id)));
                        });

                        resetSelect($('#kecamatan_id'), 'Pilih Kecamatan', false);
                        response.kecamatan.forEach(function(item) {
                            $('#kecamatan_id').append(new Option(item.name, item.id, false, String(item.id) === String(selected.kecamatan_id)));
                        });

                        resetSelect($('#desa_id'), 'Pilih Desa', false);
                        response.desa.forEach(function(item) {
                            $('#desa_id').append(new Option(item.name, item.id, false, String(item.id) === String(selected.desa_id)));
                        });

                        $('#provinsi_id').val(selected.provinsi_id).trigger('change.select2');
                        $('#kabupaten_kota_id').val(selected.kabupaten_kota_id).trigger('change.select2');
                        $('#kecamatan_id').val(selected.kecamatan_id).trigger('change.select2');
                        $('#desa_id').val(selected.desa_id).trigger('change.select2');
                    });
                });

                $(document).on('change', '#provinsi_id', function() {
                    const provinceId = $(this).val();
                    resetSelect($('#kabupaten_kota_id'), 'Pilih Kabupaten/Kota', !provinceId);
                    resetSelect($('#kecamatan_id'), 'Pilih Kecamatan', true);
                    resetSelect($('#desa_id'), 'Pilih Desa', true);

                    if (!provinceId) {
                        return;
                    }

                    loadKabupaten(provinceId);
                });

                $(document).on('change', '#kabupaten_kota_id', function() {
                    const regencyId = $(this).val();
                    resetSelect($('#kecamatan_id'), 'Pilih Kecamatan', !regencyId);
                    resetSelect($('#desa_id'), 'Pilih Desa', true);

                    if (!regencyId) {
                        return;
                    }

                    loadKecamatan(regencyId);
                });

                $(document).on('change', '#kecamatan_id', function() {
                    const districtId = $(this).val();
                    resetSelect($('#desa_id'), 'Pilih Desa', !districtId);

                    if (!districtId) {
                        return;
                    }

                    loadDesa(districtId);
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
                            nama_pemilik_perusahaan: $('#nama_pemilik_perusahaan').val(),
                            telepon_pemilik_perusahaan: $('#telepon_pemilik_perusahaan').val(),
                            alamat: $('#alamat').val(),
                            provinsi_id: $('#provinsi_id').val(),
                            kabupaten_kota_id: $('#kabupaten_kota_id').val(),
                            kecamatan_id: $('#kecamatan_id').val(),
                            desa_id: $('#desa_id').val(),
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
                })

                // Handle Check All
                $(document).on('click', '#checkAll', function() {
                    const isChecked = $(this).prop('checked');
                    $('.checkbox-perusahaan').prop('checked', isChecked);
                    updateButtonHapusMultiple();
                });

                // Handle Individual Checkbox
                $(document).on('click', '.checkbox-perusahaan', function() {
                    updateButtonHapusMultiple();
                    updateCheckAll();
                });

                // Update Button Delete Multiple
                function updateButtonHapusMultiple() {
                    const checkedCount = $('.checkbox-perusahaan:checked').length;
                    if (checkedCount > 0) {
                        $('#btnHapusMultiple').show();
                    } else {
                        $('#btnHapusMultiple').hide();
                    }
                }

                // Update Check All status
                function updateCheckAll() {
                    const totalCheckbox = $('.checkbox-perusahaan').length;
                    const checkedCheckbox = $('.checkbox-perusahaan:checked').length;
                    $('#checkAll').prop('checked', totalCheckbox === checkedCheckbox && totalCheckbox > 0);
                }

                // Handle Hapus Multiple
                $('#btnHapusMultiple').click(function() {
                    const selectedIds = [];
                    $('.checkbox-perusahaan:checked').each(function() {
                        selectedIds.push($(this).val());
                    });

                    if (selectedIds.length === 0) {
                        alert('Pilih minimal satu data untuk dihapus');
                        return;
                    }

                    Swal.fire({
                        title: 'Yakin hapus ' + selectedIds.length + ' data perusahaan ini?',
                        text: 'Tindakan ini tidak bisa dibatalkan!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '{{ route('perusahaan.destroyMultiple') }}',
                                type: 'DELETE',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    ids: selectedIds
                                },
                                success: function(response) {
                                    table.ajax.reload();
                                    $('#btnHapusMultiple').hide();
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil',
                                        text: response.message,
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                },
                                error: function(xhr) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        text: xhr.responseJSON?.message || 'Terjadi kesalahan.',
                                    });
                                }
                            });
                        }
                    });
                });
            });
        </script>
    @endsection
