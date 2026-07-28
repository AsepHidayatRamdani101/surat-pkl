@extends('adminlte::page')

@section('title', 'Kirim Pesan')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endpush

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Kirim Pesan ke Siswa</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('message-template.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {{ session('error') }}
            </div>
        @endif

        <div class="row">
            <!-- Kirim Personal -->
            <div class="col-md-6">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-user"></i> Kirim Pesan Personal
                        </h3>
                    </div>
                    <form action="" method="POST" id="formSendPersonal">
                        @csrf
                        <div class="card-body">
                            <div class="form-group">
                                <label for="siswa_personal">Pilih Siswa <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="siswa_personal" name="siswa_id" required>
                                    <option value="">-- Pilih Siswa --</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="template_personal">Template Pesan <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="template_personal" name="template_id" required>
                                    <option value="">-- Pilih Template --</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Pesan Preview:</label>
                                <div class="alert alert-light border">
                                    <p id="previewPersonalMsg" class="text-break mb-0 text-muted">
                                        (Pilih template untuk melihat preview)
                                    </p>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="message_personal">Atau Tulis Pesan Custom (Opsional):</label>
                                <textarea class="form-control" id="message_personal" name="message" rows="4" maxlength="1000"
                                    placeholder="Kosongkan untuk menggunakan template"></textarea>
                                <small class="form-text text-muted">
                                    Karakter: <span id="char_count_personal">0</span>/1000
                                </small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-paper-plane"></i> Kirim Sekarang
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Kirim Masal -->
            <div class="col-md-6">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-users"></i> Kirim Pesan Masal
                        </h3>
                    </div>
                    <form action="" method="POST" id="formSendMass">
                        @csrf
                        <div class="card-body">
                            <div class="form-group">
                                <label for="template_mass">Template Pesan <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="template_mass" name="template_id" required>
                                    <option value="">-- Pilih Template --</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Pesan Preview:</label>
                                <div class="alert alert-light border">
                                    <p id="previewMassMsg" class="text-break mb-0 text-muted">
                                        (Pilih template untuk melihat preview)
                                    </p>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="message_mass">Atau Tulis Pesan Custom (Opsional):</label>
                                <textarea class="form-control" id="message_mass" name="message" rows="4" maxlength="1000"
                                    placeholder="Kosongkan untuk menggunakan template"></textarea>
                                <small class="form-text text-muted">
                                    Karakter: <span id="char_count_mass">0</span>/1000
                                </small>
                            </div>

                            <div class="form-group">
                                <label class="mb-3">
                                    <input type="checkbox" id="select-all-mass">
                                    <strong>Pilih Semua Siswa</strong>
                                </label>
                                <div class="border rounded p-3"
                                    style="max-height: 300px; overflow-y: auto; background-color: #f9f9f9;">
                                    <div id="siswaListMass">
                                        <p class="text-muted text-center">Memuat data siswa...</p>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <p class="text-muted">
                                    Total dipilih: <span id="selected-count-mass">0</span> siswa
                                </p>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-paper-plane"></i> Kirim ke Semua yang Dipilih
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@section('js')
    <script>
        $(function() {
            // Load templates
            loadTemplates();
            loadSiswa();

            // Load templates dropdown
            function loadTemplates() {
                $.ajax({
                    url: '{{ route('message-template.data') }}',
                    type: 'GET',
                    data: {
                        draw: 1,
                        start: 0,
                        length: 100
                    },
                    success: function(response) {
                        let templates = response.data;
                        let options = '<option value="">-- Pilih Template --</option>';
                        templates.forEach(function(template) {
                            options += '<option value="' + template.id + '" data-message="' +
                                template.isi_template.replace(/"/g, '&quot;') + '">' +
                                template.nama_template + '</option>';
                        });
                        $('#template_personal').html(options);
                        $('#template_mass').html(options);
                        $('.select2').select2({
                            theme: 'bootstrap',
                            allowClear: true
                        });
                    }
                });
            }

            // Load siswa for mass send
            function loadSiswa() {
                $.ajax({
                    url: '{{ route('api.siswa-list') }}',
                    type: 'GET',
                    success: function(response) {
                        let siswas = response;
                        let options = '<option value="">-- Pilih Siswa --</option>';
                        siswas.forEach(function(siswa) {
                            options += '<option value="' + siswa.id + '">' + siswa.nis +
                                ' - ' + siswa.nama_siswa + ' (' + siswa.no_hp_siswa +
                                ')</option>';
                        });
                        $('#siswa_personal').html(options);

                        let checkboxes = '';
                        siswas.forEach(function(siswa) {
                            checkboxes += '<div class="custom-control custom-checkbox mb-2">' +
                                '<input type="checkbox" class="custom-control-input siswa-checkbox-mass" id="siswa_' +
                                siswa.id + '" name="siswa_ids" value="' + siswa.id + '">' +
                                '<label class="custom-control-label" for="siswa_' + siswa.id +
                                '"><span class="font-weight-bold">' + siswa.nis +
                                '</span> - ' + siswa.nama_siswa + ' (' + siswa.no_hp_siswa +
                                ')</label>' +
                                '</div>';
                        });
                        $('#siswaListMass').html(checkboxes);
                        $('.select2').select2({
                            theme: 'bootstrap',
                            allowClear: true
                        });
                    }
                });
            }

            // Character counter personal
            $('#message_personal').on('keyup', function() {
                $('#char_count_personal').text($(this).val().length);
            });

            // Character counter mass
            $('#message_mass').on('keyup', function() {
                $('#char_count_mass').text($(this).val().length);
            });

            // Template preview personal
            $('#template_personal').on('change', function() {
                let message = $(this).find('option:selected').data('message');
                if (message) {
                    $('#previewPersonalMsg').text(message).removeClass('text-muted');
                } else {
                    $('#previewPersonalMsg').text('(Pilih template untuk melihat preview)').addClass(
                        'text-muted');
                }
            });

            // Template preview mass
            $('#template_mass').on('change', function() {
                let message = $(this).find('option:selected').data('message');
                if (message) {
                    $('#previewMassMsg').text(message).removeClass('text-muted');
                } else {
                    $('#previewMassMsg').text('(Pilih template untuk melihat preview)').addClass(
                        'text-muted');
                }
            });

            // Select all for mass send
            $('#select-all-mass').on('change', function() {
                $('.siswa-checkbox-mass').prop('checked', this.checked);
                updateSelectedCount();
            });

            // Update selected count
            $(document).on('change', '.siswa-checkbox-mass', function() {
                updateSelectedCount();
            });

            function updateSelectedCount() {
                let count = $('.siswa-checkbox-mass:checked').length;
                $('#selected-count-mass').text(count);
            }

            // Submit personal form
            $('#formSendPersonal').on('submit', function(e) {
                e.preventDefault();

                let templateId = $('#template_personal').val();
                let siswaId = $('#siswa_personal').val();
                let customMessage = $('#message_personal').val();

                if (!siswaId) {
                    alert('Pilih siswa terlebih dahulu!');
                    return;
                }

                if (!templateId && !customMessage) {
                    alert('Pilih template atau tulis pesan custom!');
                    return;
                }

                $.ajax({
                    url: '{{ url('template-informasi') }}/' + templateId + '/send-personal',
                    type: 'POST',
                    data: {
                        siswa_id: siswaId,
                        message: customMessage,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        alert(response.message);
                        $('#formSendPersonal')[0].reset();
                        $('#previewPersonalMsg').text('(Pilih template untuk melihat preview)')
                            .addClass('text-muted');
                        $('#char_count_personal').text(0);
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON
                            .message : 'Gagal mengirim pesan.');
                    }
                });
            });

            // Submit mass form
            $('#formSendMass').on('submit', function(e) {
                e.preventDefault();

                let templateId = $('#template_mass').val();
                let siswaIds = [];
                $('.siswa-checkbox-mass:checked').each(function() {
                    siswaIds.push($(this).val());
                });
                let customMessage = $('#message_mass').val();

                if (siswaIds.length === 0) {
                    alert('Pilih minimal satu siswa!');
                    return;
                }

                if (!templateId && !customMessage) {
                    alert('Pilih template atau tulis pesan custom!');
                    return;
                }

                $.ajax({
                    url: '{{ url('template-informasi') }}/' + templateId + '/send-mass',
                    type: 'POST',
                    data: {
                        siswa_ids: siswaIds,
                        message: customMessage,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        alert(response.message);
                        $('#formSendMass')[0].reset();
                        $('.siswa-checkbox-mass').prop('checked', false);
                        $('#select-all-mass').prop('checked', false);
                        $('#previewMassMsg').text('(Pilih template untuk melihat preview)')
                            .addClass('text-muted');
                        $('#char_count_mass').text(0);
                        $('#selected-count-mass').text(0);
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON
                            .message : 'Gagal mengirim pesan.');
                    }
                });
            });
        });
    </script>
@endsection
@endsection
