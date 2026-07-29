@extends('adminlte::page')

@section('title', 'Template Informasi')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>Template Informasi Siswa</h2>
                    <div>
                        <button type="button" class="btn btn-primary" id="btnTambahTemplate">
                            <i class="fas fa-plus"></i> Buat Template Baru
                        </button>
                        <a href="{{ route('message-template.logs') }}" class="btn btn-info">
                            <i class="fas fa-history"></i> Riwayat Pengiriman
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {{ session('success') }}
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Template</h3>
            </div>
            <div class="card-body">
                <table id="templatesTable" class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 20%;">Nama Template</th>
                            <th style="width: 12%;">Tipe</th>
                            <th style="width: 25%;">Konten</th>
                            <th style="width: 12%;">Pembuat</th>
                            <th style="width: 10%;">Tanggal</th>
                            <th style="width: 16%;">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Form Template -->
    <div class="modal fade" id="modalFormTemplate" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Buat Template Baru</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="formTemplate" method="POST">
                    <div class="modal-body">
                        @csrf
                        <input type="hidden" id="templateId">

                        <div class="form-group">
                            <label for="nama_template">Nama Template <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_template" name="nama_template" required>
                            <span class="error-nama_template text-danger"></span>
                        </div>

                        <div class="form-group">
                            <label for="tipe_template">Tipe Template <span class="text-danger">*</span></label>
                            <select class="form-control" id="tipe_template" name="tipe_template" required>
                                <option value="">-- Pilih Tipe --</option>
                                <option value="informasi">Informasi</option>
                                <option value="pengumuman">Pengumuman</option>
                                <option value="undangan">Undangan</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                            <span class="error-tipe_template text-danger"></span>
                        </div>

                        <div class="form-group">
                            <label for="isi_template">Isi Template <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="isi_template" name="isi_template" rows="6" maxlength="1000" required></textarea>
                            <small class="form-text text-muted">
                                Karakter: <span id="char_count">0</span>/1000
                            </small>
                            <span class="error-isi_template text-danger"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Template</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Pengiriman Pesan -->
    <div class="modal fade" id="modalSend" role="dialog" size="lg">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Kirim Pesan</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="currentTemplateId">

                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="personal-tab" data-toggle="pill" href="#personal"
                                role="tab">
                                <i class="fas fa-user"></i> Kirim Personal
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="mass-tab" data-toggle="pill" href="#mass" role="tab">
                                <i class="fas fa-users"></i> Kirim Masal
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content mt-3">
                        <!-- Personal -->
                        <div class="tab-pane fade show active" id="personal" role="tabpanel">
                            <form id="formSendPersonal">
                                @csrf
                                <div class="form-group">
                                    <label for="siswa_id_personal">Pilih Siswa <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="siswa_id_personal" name="siswa_id" required>
                                        <option value="">-- Pilih Siswa --</option>
                                    </select>
                                </div>

                                <div class="alert alert-light border">
                                    <strong>Preview Pesan:</strong><br>
                                    <p id="previewPersonal" class="text-break mb-0"></p>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-paper-plane"></i> Kirim Pesan
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Mass -->
                        <div class="tab-pane fade" id="mass" role="tabpanel">
                            <form id="formSendMass">
                                @csrf
                                <div class="form-group">
                                    <label class="mb-3">
                                        <input type="checkbox" id="select-all">
                                        <strong>Pilih Semua Siswa</strong>
                                    </label>
                                    <div class="border rounded p-3"
                                        style="max-height: 300px; overflow-y: auto; background-color: #f9f9f9;"
                                        id="siswaListMass">
                                        <!-- Will be populated by JavaScript -->
                                    </div>
                                </div>

                                <div class="form-group">
                                    <p class="text-muted">
                                        Total dipilih: <span id="selected-count">0</span>
                                    </p>
                                </div>

                                <div class="alert alert-light border">
                                    <strong>Preview Pesan:</strong><br>
                                    <p id="previewMass" class="text-break mb-0"></p>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-success" id="btnSendMass">
                                        <i class="fas fa-paper-plane"></i> Kirim ke Semua
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        var currentTemplate = null;

        $(function() {
            console.log('DOM Ready - Template Page Initialized');
            console.log('Button exists:', $('#btnTambahTemplate').length > 0);

            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap',
                allowClear: true,
                placeholder: '-- Pilih Siswa --'
            });

            // Initialize DataTable
            var table = $('#templatesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('message-template.data') }}',
                    type: 'GET'
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'nama_template',
                        name: 'nama_template'
                    },
                    {
                        data: 'tipe',
                        name: 'tipe',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'isi_template',
                        name: 'isi_template',
                        render: function(data) {
                            return data.substring(0, 50) + (data.length > 50 ? '...' : '');
                        }
                    },
                    {
                        data: 'pembuat',
                        name: 'pembuat',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'tanggal',
                        name: 'tanggal'
                    },
                    {
                        data: 'aksi',
                        name: 'aksi',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // Tombol Buat Template Baru
            $(document).on('click', '#btnTambahTemplate', function() {
                console.log('Tombol Buat Template Baru diklik');
                resetForm();
                $('#modalTitle').text('Buat Template Baru');
                $('#formTemplate').attr('action', '{{ route('message-template.store') }}').attr('method',
                    'POST');
                $('#modalFormTemplate').modal('show');
            });

            // Tombol Edit
            $(document).on('click', '.btnEdit', function() {
                var id = $(this).data('id');
                $.get('{{ url('template-informasi') }}/' + id + '/edit', function(data) {
                    $('#templateId').val(data.id);
                    $('#nama_template').val(data.nama_template);
                    $('#tipe_template').val(data.tipe_template);
                    $('#isi_template').val(data.isi_template);
                    $('#char_count').text(data.isi_template.length);

                    $('#modalTitle').text('Edit Template');
                    $('#formTemplate')
                        .attr('action', '{{ url('template-informasi') }}/' + data.id)
                        .attr('method', 'POST')
                        .find('input[name="_method"]').remove().end()
                        .append('<input type="hidden" name="_method" value="PUT">');

                    $('#modalFormTemplate').modal('show');
                });
            });

            // Tombol Kirim
            $(document).on('click', '.btnKirim', function() {
                var id = $(this).data('id');
                var nama = $(this).data('nama');
                var isi = $(this).data('isi');

                $('#currentTemplateId').val(id);
                $('#previewPersonal').text(isi);
                $('#previewMass').text(isi);

                // Load siswa data
                $.get('{{ url('api/siswa-list') }}', function(response) {
                    var siswasPersonal = '';
                    var siswasMass = '';

                    response.forEach(function(siswa) {
                        if (siswa.no_hp_siswa) {
                            var option = '<option value="' + siswa.id + '">' + siswa.nis +
                                ' - ' + siswa.nama_siswa + ' (' + siswa.no_hp_siswa +
                                ')</option>';
                            siswasPersonal += option;

                            var checkbox =
                                '<div class="custom-control custom-checkbox mb-2">' +
                                '<input type="checkbox" class="custom-control-input siswa-checkbox" id="siswa_' +
                                siswa.id + '" name="siswa_ids[]" value="' + siswa.id +
                                '">' +
                                '<label class="custom-control-label" for="siswa_' + siswa
                                .id + '">' + siswa.nis + ' - ' + siswa.nama_siswa + ' (' +
                                siswa.no_hp_siswa + ')</label>' +
                                '</div>';
                            siswasMass += checkbox;
                        }
                    });

                    $('#siswa_id_personal').html(siswasPersonal).val('').trigger('change');
                    $('#siswaListMass').html(siswasMass);
                    $('#select-all').prop('checked', false);
                    updateSelectedCount();
                });

                $('#modalSend').modal('show');
            });

            // Tombol Hapus
            $(document).on('click', '.btnHapus', function() {
                var id = $(this).data('id');
                if (confirm('Apakah Anda yakin ingin menghapus template ini?')) {
                    $.ajax({
                        url: '{{ url('template-informasi') }}/' + id,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                table.ajax.reload();
                                alert('Template berhasil dihapus.');
                            }
                        },
                        error: function(xhr) {
                            alert('Terjadi kesalahan saat menghapus template.');
                        }
                    });
                }
            });

            // Form Submit - Create/Update Template
            $('#formTemplate').on('submit', function(e) {
                e.preventDefault();
                var url = $(this).attr('action');
                var method = $(this).attr('method');
                var data = {
                    nama_template: $('#nama_template').val(),
                    tipe_template: $('#tipe_template').val(),
                    isi_template: $('#isi_template').val(),
                    _token: $('meta[name="csrf-token"]').attr('content')
                };

                if ($('#templateId').val()) {
                    data._method = 'PUT';
                }

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        if (response.success || response.message) {
                            $('#modalFormTemplate').modal('hide');
                            table.ajax.reload();
                            alert('Template berhasil disimpan.');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            Object.keys(xhr.responseJSON.errors).forEach(function(key) {
                                $('.error-' + key).text(xhr.responseJSON.errors[key][
                                    0
                                ]);
                            });
                        } else {
                            alert('Terjadi kesalahan saat menyimpan template.');
                        }
                    }
                });
            });

            // Character counter
            $('#isi_template').on('input', function() {
                $('#char_count').text($(this).val().length);
            });

            // Select all checkbox
            $(document).on('change', '#select-all', function() {
                var isChecked = $(this).is(':checked');
                $('.siswa-checkbox:not(:disabled)').prop('checked', isChecked);
                updateSelectedCount();
            });

            // Update count when individual checkbox changes
            $(document).on('change', '.siswa-checkbox', function() {
                updateSelectedCount();
            });

            // Form Submit - Send Personal
            $('#formSendPersonal').on('submit', function(e) {
                e.preventDefault();
                var siswaId = $('#siswa_id_personal').val();
                if (!siswaId) {
                    alert('Pilih siswa terlebih dahulu.');
                    return;
                }

                $.ajax({
                    url: '{{ url('template-informasi') }}/' + $('#currentTemplateId').val() +
                        '/send-personal',
                    type: 'POST',
                    data: {
                        siswa_id: siswaId,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#modalSend').modal('hide');
                        table.ajax.reload();
                        alert('Pesan berhasil dikirim.');
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON
                            .message : 'Gagal mengirim pesan.');
                    }
                });
            });

            // Form Submit - Send Mass
            $('#formSendMass').on('submit', function(e) {
                e.preventDefault();
                var siswaIds = $('[name="siswa_ids[]"]:checked').map(function() {
                    return $(this).val();
                }).get();

                if (siswaIds.length === 0) {
                    alert('Pilih minimal satu siswa.');
                    return;
                }

                $.ajax({
                    url: '{{ url('template-informasi') }}/' + $('#currentTemplateId').val() +
                        '/send-mass',
                    type: 'POST',
                    data: {
                        siswa_ids: siswaIds,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#modalSend').modal('hide');
                        table.ajax.reload();
                        alert('Pesan berhasil dikirim ke ' + siswaIds.length + ' siswa.');
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON
                            .message : 'Gagal mengirim pesan.');
                    }
                });
            });

            function resetForm() {
                $('#formTemplate')[0].reset();
                $('#templateId').val('');
                $('.error-*').text('');
                $('#char_count').text(0);
            }

            function updateSelectedCount() {
                var count = $('.siswa-checkbox:checked').length;
                $('#selected-count').text(count);
            }
        });
    </script>
@endsection
