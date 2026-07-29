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
                    <h1 class="m-0">Kirim Pesan</h1>
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

        <!-- Nav Tabs untuk Tipe Penerima -->
        <div class="card card-primary">
            <div class="card-header">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="siswa-tab" data-toggle="pill" href="#siswa-content" role="tab">
                            <i class="fas fa-graduation-cap"></i> Kirim ke Siswa
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="guru-tab" data-toggle="pill" href="#guru-content" role="tab">
                            <i class="fas fa-chalkboard-user"></i> Kirim ke Guru
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="orangtua-tab" data-toggle="pill" href="#orangtua-content" role="tab">
                            <i class="fas fa-users"></i> Kirim ke Orangtua
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- TAB SISWA -->
                    <div class="tab-pane fade show active" id="siswa-content" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card card-primary">
                                    <div class="card-header">
                                        <h5 class="card-title"><i class="fas fa-user"></i> Kirim Personal ke Siswa</h5>
                                    </div>
                                    <form id="formSendPersonalSiswa">
                                        @csrf
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label for="siswa_personal">Pilih Siswa <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-control select2" id="siswa_personal" name="siswa_id"
                                                    required>
                                                    <option value="">-- Pilih Siswa --</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="template_personal_siswa">Template Pesan <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-control select2" id="template_personal_siswa"
                                                    name="template_id" required>
                                                    <option value="">-- Pilih Template --</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Pesan Preview:</label>
                                                <div class="alert alert-light border">
                                                    <p id="previewPersonalSiswaMsg" class="text-break mb-0 text-muted">
                                                        (Pilih template untuk melihat preview)</p>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="message_personal_siswa">Atau Tulis Pesan Custom
                                                    (Opsional):</label>
                                                <textarea class="form-control" id="message_personal_siswa" name="message" rows="4" maxlength="1000"></textarea>
                                                <small class="form-text text-muted">Karakter: <span
                                                        id="char_count_personal_siswa">0</span>/1000</small>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <button type="submit" class="btn btn-success"><i
                                                    class="fas fa-paper-plane"></i> Kirim Sekarang</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card card-success">
                                    <div class="card-header">
                                        <h5 class="card-title"><i class="fas fa-users"></i> Kirim Masal ke Siswa</h5>
                                    </div>
                                    <form id="formSendMassSiswa">
                                        @csrf
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label for="template_mass_siswa">Template Pesan <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-control select2" id="template_mass_siswa"
                                                    name="template_id" required>
                                                    <option value="">-- Pilih Template --</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Pesan Preview:</label>
                                                <div class="alert alert-light border">
                                                    <p id="previewMassSiswaMsg" class="text-break mb-0 text-muted">(Pilih
                                                        template untuk melihat preview)</p>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="message_mass_siswa">Atau Tulis Pesan Custom (Opsional):</label>
                                                <textarea class="form-control" id="message_mass_siswa" name="message" rows="4" maxlength="1000"></textarea>
                                                <small class="form-text text-muted">Karakter: <span
                                                        id="char_count_mass_siswa">0</span>/1000</small>
                                            </div>
                                            <div class="form-group">
                                                <label class="mb-3"><input type="checkbox"
                                                        id="select-all-siswa"><strong> Pilih Semua Siswa</strong></label>
                                                <div class="border rounded p-3"
                                                    style="max-height: 300px; overflow-y: auto; background-color: #f9f9f9;">
                                                    <div id="siswaListMass">
                                                        <p class="text-muted text-center">Memuat data siswa...</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <p class="text-muted">Total dipilih: <span
                                                        id="selected-count-siswa">0</span> siswa</p>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <button type="submit" class="btn btn-success"><i
                                                    class="fas fa-paper-plane"></i> Kirim ke Semua yang Dipilih</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB GURU -->
                    <div class="tab-pane fade" id="guru-content" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card card-primary">
                                    <div class="card-header">
                                        <h5 class="card-title"><i class="fas fa-user"></i> Kirim Personal ke Guru</h5>
                                    </div>
                                    <form id="formSendPersonalGuru">
                                        @csrf
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label for="guru_personal">Pilih Guru <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-control select2" id="guru_personal" name="guru_id"
                                                    required>
                                                    <option value="">-- Pilih Guru --</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="template_personal_guru">Template Pesan <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-control select2" id="template_personal_guru"
                                                    name="template_id" required>
                                                    <option value="">-- Pilih Template --</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Pesan Preview:</label>
                                                <div class="alert alert-light border">
                                                    <p id="previewPersonalGuruMsg" class="text-break mb-0 text-muted">
                                                        (Pilih template untuk melihat preview)</p>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="message_personal_guru">Atau Tulis Pesan Custom
                                                    (Opsional):</label>
                                                <textarea class="form-control" id="message_personal_guru" name="message" rows="4" maxlength="1000"></textarea>
                                                <small class="form-text text-muted">Karakter: <span
                                                        id="char_count_personal_guru">0</span>/1000</small>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <button type="submit" class="btn btn-success"><i
                                                    class="fas fa-paper-plane"></i> Kirim Sekarang</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card card-success">
                                    <div class="card-header">
                                        <h5 class="card-title"><i class="fas fa-users"></i> Kirim Masal ke Guru</h5>
                                    </div>
                                    <form id="formSendMassGuru">
                                        @csrf
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label for="template_mass_guru">Template Pesan <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-control select2" id="template_mass_guru"
                                                    name="template_id" required>
                                                    <option value="">-- Pilih Template --</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Pesan Preview:</label>
                                                <div class="alert alert-light border">
                                                    <p id="previewMassGuruMsg" class="text-break mb-0 text-muted">(Pilih
                                                        template untuk melihat preview)</p>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="message_mass_guru">Atau Tulis Pesan Custom (Opsional):</label>
                                                <textarea class="form-control" id="message_mass_guru" name="message" rows="4" maxlength="1000"></textarea>
                                                <small class="form-text text-muted">Karakter: <span
                                                        id="char_count_mass_guru">0</span>/1000</small>
                                            </div>
                                            <div class="form-group">
                                                <label class="mb-3"><input type="checkbox"
                                                        id="select-all-guru"><strong> Pilih Semua Guru</strong></label>
                                                <div class="border rounded p-3"
                                                    style="max-height: 300px; overflow-y: auto; background-color: #f9f9f9;">
                                                    <div id="guruListMass">
                                                        <p class="text-muted text-center">Memuat data guru...</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <p class="text-muted">Total dipilih: <span
                                                        id="selected-count-guru">0</span> guru</p>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <button type="submit" class="btn btn-success"><i
                                                    class="fas fa-paper-plane"></i> Kirim ke Semua yang Dipilih</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB ORANGTUA -->
                    <div class="tab-pane fade" id="orangtua-content" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card card-primary">
                                    <div class="card-header">
                                        <h5 class="card-title"><i class="fas fa-user"></i> Kirim Personal ke Orangtua</h5>
                                    </div>
                                    <form id="formSendPersonalOrangtua">
                                        @csrf
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label for="orangtua_personal">Pilih Orangtua <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-control select2" id="orangtua_personal"
                                                    name="siswa_id" required>
                                                    <option value="">-- Pilih Orangtua --</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="template_personal_orangtua">Template Pesan <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-control select2" id="template_personal_orangtua"
                                                    name="template_id" required>
                                                    <option value="">-- Pilih Template --</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Pesan Preview:</label>
                                                <div class="alert alert-light border">
                                                    <p id="previewPersonalOrangtuaMsg" class="text-break mb-0 text-muted">
                                                        (Pilih template untuk melihat preview)</p>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="message_personal_orangtua">Atau Tulis Pesan Custom
                                                    (Opsional):</label>
                                                <textarea class="form-control" id="message_personal_orangtua" name="message" rows="4" maxlength="1000"></textarea>
                                                <small class="form-text text-muted">Karakter: <span
                                                        id="char_count_personal_orangtua">0</span>/1000</small>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <button type="submit" class="btn btn-success"><i
                                                    class="fas fa-paper-plane"></i> Kirim Sekarang</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card card-success">
                                    <div class="card-header">
                                        <h5 class="card-title"><i class="fas fa-users"></i> Kirim Masal ke Orangtua</h5>
                                    </div>
                                    <form id="formSendMassOrangtua">
                                        @csrf
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label for="template_mass_orangtua">Template Pesan <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-control select2" id="template_mass_orangtua"
                                                    name="template_id" required>
                                                    <option value="">-- Pilih Template --</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Pesan Preview:</label>
                                                <div class="alert alert-light border">
                                                    <p id="previewMassOrangtuaMsg" class="text-break mb-0 text-muted">
                                                        (Pilih template untuk melihat preview)</p>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="message_mass_orangtua">Atau Tulis Pesan Custom
                                                    (Opsional):</label>
                                                <textarea class="form-control" id="message_mass_orangtua" name="message" rows="4" maxlength="1000"></textarea>
                                                <small class="form-text text-muted">Karakter: <span
                                                        id="char_count_mass_orangtua">0</span>/1000</small>
                                            </div>
                                            <div class="form-group">
                                                <label class="mb-3"><input type="checkbox"
                                                        id="select-all-orangtua"><strong> Pilih Semua
                                                        Orangtua</strong></label>
                                                <div class="border rounded p-3"
                                                    style="max-height: 300px; overflow-y: auto; background-color: #f9f9f9;">
                                                    <div id="orangtuaListMass">
                                                        <p class="text-muted text-center">Memuat data orangtua...</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <p class="text-muted">Total dipilih: <span
                                                        id="selected-count-orangtua">0</span> orangtua</p>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <button type="submit" class="btn btn-success"><i
                                                    class="fas fa-paper-plane"></i> Kirim ke Semua yang Dipilih</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(function() {
            loadTemplates();
            loadSiswa();
            loadGuru();
            loadOrangtua();

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
                        templates.forEach(function(t) {
                            options += '<option value="' + t.id + '" data-message="' + t
                                .isi_template.replace(/"/g, '&quot;') + '">' + t.nama_template +
                                '</option>';
                        });
                        $('#template_personal_siswa, #template_mass_siswa, #template_personal_guru, #template_mass_guru, #template_personal_orangtua, #template_mass_orangtua')
                            .html(options);
                        $('.select2').select2({
                            theme: 'bootstrap',
                            allowClear: true
                        });
                    }
                });
            }

            function loadSiswa() {
                $.ajax({
                    url: '{{ route('api.siswa-list') }}',
                    type: 'GET',
                    success: function(response) {
                        let opts = '<option value="">-- Pilih Siswa --</option>';
                        response.forEach(function(s) {
                            opts += '<option value="' + s.id + '">' + s.nis + ' - ' + s
                                .nama_siswa + ' (' + s.no_hp_siswa + ')</option>';
                        });
                        $('#siswa_personal').html(opts);
                        let boxes = '';
                        response.forEach(function(s) {
                            boxes +=
                                '<div class="custom-control custom-checkbox mb-2"><input type="checkbox" class="custom-control-input siswa-checkbox-mass" id="siswa_' +
                                s.id + '" name="siswa_ids" value="' + s.id +
                                '"><label class="custom-control-label" for="siswa_' + s.id +
                                '"><span class="font-weight-bold">' + s.nis + '</span> - ' + s
                                .nama_siswa + '</label></div>';
                        });
                        $('#siswaListMass').html(boxes);
                    }
                });
            }

            function loadGuru() {
                $.ajax({
                    url: '{{ route('api.guru-list') }}',
                    type: 'GET',
                    success: function(response) {
                        let opts = '<option value="">-- Pilih Guru --</option>';
                        response.forEach(function(g) {
                            opts += '<option value="' + g.id + '">' + g.nip_pembimbing + ' - ' +
                                g.nama_pembimbing + ' (' + g.no_hp_pembimbing + ')</option>';
                        });
                        $('#guru_personal').html(opts);
                        let boxes = '';
                        response.forEach(function(g) {
                            boxes +=
                                '<div class="custom-control custom-checkbox mb-2"><input type="checkbox" class="custom-control-input guru-checkbox-mass" id="guru_' +
                                g.id + '" name="guru_ids" value="' + g.id +
                                '"><label class="custom-control-label" for="guru_' + g.id +
                                '"><span class="font-weight-bold">' + g.nip_pembimbing +
                                '</span> - ' + g.nama_pembimbing + '</label></div>';
                        });
                        $('#guruListMass').html(boxes);
                    }
                });
            }

            function loadOrangtua() {
                $.ajax({
                    url: '{{ route('api.orangtua-list') }}',
                    type: 'GET',
                    success: function(response) {
                        let opts = '<option value="">-- Pilih Orangtua --</option>';
                        response.forEach(function(o) {
                            opts += '<option value="' + o.id + '">' + o.nis + ' - ' + o
                                .nama_ortu + ' (' + o.no_hp_ortu + ')</option>';
                        });
                        $('#orangtua_personal').html(opts);
                        let boxes = '';
                        response.forEach(function(o) {
                            boxes +=
                                '<div class="custom-control custom-checkbox mb-2"><input type="checkbox" class="custom-control-input orangtua-checkbox-mass" id="orangtua_' +
                                o.id + '" name="orangtua_ids" value="' + o.id +
                                '"><label class="custom-control-label" for="orangtua_' + o.id +
                                '"><span class="font-weight-bold">' + o.nis + '</span> - ' + o
                                .nama_ortu + '</label></div>';
                        });
                        $('#orangtuaListMass').html(boxes);
                    }
                });
            }

            // Character counters
            $(document).on('keyup',
                '#message_personal_siswa, #message_mass_siswa, #message_personal_guru, #message_mass_guru, #message_personal_orangtua, #message_mass_orangtua',
                function() {
                    let id = $(this).attr('id').replace('message', 'char_count');
                    $('#' + id).text($(this).val().length);
                });

            // Template previews
            $(document).on('change', '#template_personal_siswa, #template_mass_siswa', function() {
                let msg = $(this).find('option:selected').data('message') || '';
                let prevId = $(this).attr('id').includes('personal') ? 'previewPersonalSiswaMsg' :
                    'previewMassSiswaMsg';
                $('#' + prevId).text(msg || '(Pilih template untuk melihat preview)').toggleClass(
                    'text-muted', !msg);
            });

            $(document).on('change', '#template_personal_guru, #template_mass_guru', function() {
                let msg = $(this).find('option:selected').data('message') || '';
                let prevId = $(this).attr('id').includes('personal') ? 'previewPersonalGuruMsg' :
                    'previewMassGuruMsg';
                $('#' + prevId).text(msg || '(Pilih template untuk melihat preview)').toggleClass(
                    'text-muted', !msg);
            });

            $(document).on('change', '#template_personal_orangtua, #template_mass_orangtua', function() {
                let msg = $(this).find('option:selected').data('message') || '';
                let prevId = $(this).attr('id').includes('personal') ? 'previewPersonalOrangtuaMsg' :
                    'previewMassOrangtuaMsg';
                $('#' + prevId).text(msg || '(Pilih template untuk melihat preview)').toggleClass(
                    'text-muted', !msg);
            });

            // Select all checkboxes
            $('#select-all-siswa').on('change', function() {
                $('.siswa-checkbox-mass').prop('checked', this.checked);
                updateCount('siswa');
            });
            $('#select-all-guru').on('change', function() {
                $('.guru-checkbox-mass').prop('checked', this.checked);
                updateCount('guru');
            });
            $('#select-all-orangtua').on('change', function() {
                $('.orangtua-checkbox-mass').prop('checked', this.checked);
                updateCount('orangtua');
            });

            $(document).on('change', '.siswa-checkbox-mass', function() {
                updateCount('siswa');
            });
            $(document).on('change', '.guru-checkbox-mass', function() {
                updateCount('guru');
            });
            $(document).on('change', '.orangtua-checkbox-mass', function() {
                updateCount('orangtua');
            });

            function updateCount(type) {
                if (type === 'siswa') $('#selected-count-siswa').text($('.siswa-checkbox-mass:checked').length);
                else if (type === 'guru') $('#selected-count-guru').text($('.guru-checkbox-mass:checked').length);
                else if (type === 'orangtua') $('#selected-count-orangtua').text($(
                    '.orangtua-checkbox-mass:checked').length);
            }

            // SISWA FORMS
            $('#formSendPersonalSiswa').on('submit', function(e) {
                e.preventDefault();
                let siswaId = $('#siswa_personal').val();
                let templateId = $('#template_personal_siswa').val();
                let msg = $('#message_personal_siswa').val();
                if (!siswaId) {
                    alert('Pilih siswa!');
                    return;
                }
                if (!templateId && !msg) {
                    alert('Pilih template atau tulis pesan!');
                    return;
                }
                sendRequest('{{ url('template-informasi') }}/' + templateId + '/send-personal', {
                    recipient_type: 'siswa',
                    siswa_id: siswaId,
                    message: msg
                }, '#formSendPersonalSiswa', 'siswa', 'personal');
            });

            $('#formSendMassSiswa').on('submit', function(e) {
                e.preventDefault();
                let ids = [];
                $('.siswa-checkbox-mass:checked').each(function() {
                    ids.push($(this).val());
                });
                let templateId = $('#template_mass_siswa').val();
                let msg = $('#message_mass_siswa').val();
                if (ids.length === 0) {
                    alert('Pilih minimal satu siswa!');
                    return;
                }
                if (!templateId && !msg) {
                    alert('Pilih template atau tulis pesan!');
                    return;
                }
                sendRequest('{{ url('template-informasi') }}/' + templateId + '/send-mass', {
                    recipient_type: 'siswa',
                    siswa_ids: ids,
                    message: msg
                }, '#formSendMassSiswa', 'siswa', 'mass');
            });

            // GURU FORMS
            $('#formSendPersonalGuru').on('submit', function(e) {
                e.preventDefault();
                let guruId = $('#guru_personal').val();
                let templateId = $('#template_personal_guru').val();
                let msg = $('#message_personal_guru').val();
                if (!guruId) {
                    alert('Pilih guru!');
                    return;
                }
                if (!templateId && !msg) {
                    alert('Pilih template atau tulis pesan!');
                    return;
                }
                sendRequest('{{ url('template-informasi') }}/' + templateId + '/send-personal', {
                    recipient_type: 'guru',
                    guru_id: guruId,
                    message: msg
                }, '#formSendPersonalGuru', 'guru', 'personal');
            });

            $('#formSendMassGuru').on('submit', function(e) {
                e.preventDefault();
                let ids = [];
                $('.guru-checkbox-mass:checked').each(function() {
                    ids.push($(this).val());
                });
                let templateId = $('#template_mass_guru').val();
                let msg = $('#message_mass_guru').val();
                if (ids.length === 0) {
                    alert('Pilih minimal satu guru!');
                    return;
                }
                if (!templateId && !msg) {
                    alert('Pilih template atau tulis pesan!');
                    return;
                }
                sendRequest('{{ url('template-informasi') }}/' + templateId + '/send-mass', {
                    recipient_type: 'guru',
                    guru_ids: ids,
                    message: msg
                }, '#formSendMassGuru', 'guru', 'mass');
            });

            // ORANGTUA FORMS
            $('#formSendPersonalOrangtua').on('submit', function(e) {
                e.preventDefault();
                let siswaId = $('#orangtua_personal').val();
                let templateId = $('#template_personal_orangtua').val();
                let msg = $('#message_personal_orangtua').val();
                if (!siswaId) {
                    alert('Pilih orangtua!');
                    return;
                }
                if (!templateId && !msg) {
                    alert('Pilih template atau tulis pesan!');
                    return;
                }
                sendRequest('{{ url('template-informasi') }}/' + templateId + '/send-personal', {
                    recipient_type: 'orangtua',
                    siswa_id: siswaId,
                    message: msg
                }, '#formSendPersonalOrangtua', 'orangtua', 'personal');
            });

            $('#formSendMassOrangtua').on('submit', function(e) {
                e.preventDefault();
                let ids = [];
                $('.orangtua-checkbox-mass:checked').each(function() {
                    ids.push($(this).val());
                });
                let templateId = $('#template_mass_orangtua').val();
                let msg = $('#message_mass_orangtua').val();
                if (ids.length === 0) {
                    alert('Pilih minimal satu orangtua!');
                    return;
                }
                if (!templateId && !msg) {
                    alert('Pilih template atau tulis pesan!');
                    return;
                }
                sendRequest('{{ url('template-informasi') }}/' + templateId + '/send-mass', {
                    recipient_type: 'orangtua',
                    siswa_ids: ids,
                    message: msg
                }, '#formSendMassOrangtua', 'orangtua', 'mass');
            });

            function sendRequest(url, data, formId, type, mode) {
                data._token = $('meta[name="csrf-token"]').attr('content');
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        alert(response.message);
                        $(formId)[0].reset();
                        if (mode === 'personal') {
                            if (type === 'siswa') {
                                $('#previewPersonalSiswaMsg').text(
                                    '(Pilih template untuk melihat preview)').addClass('text-muted');
                                $('#char_count_personal_siswa').text(0);
                            } else if (type === 'guru') {
                                $('#previewPersonalGuruMsg').text(
                                    '(Pilih template untuk melihat preview)').addClass('text-muted');
                                $('#char_count_personal_guru').text(0);
                            } else if (type === 'orangtua') {
                                $('#previewPersonalOrangtuaMsg').text(
                                    '(Pilih template untuk melihat preview)').addClass('text-muted');
                                $('#char_count_personal_orangtua').text(0);
                            }
                        } else {
                            if (type === 'siswa') {
                                $('.siswa-checkbox-mass').prop('checked', false);
                                $('#select-all-siswa').prop('checked', false);
                                $('#previewMassSiswaMsg').text('(Pilih template untuk melihat preview)')
                                    .addClass('text-muted');
                                $('#char_count_mass_siswa').text(0);
                                $('#selected-count-siswa').text(0);
                            } else if (type === 'guru') {
                                $('.guru-checkbox-mass').prop('checked', false);
                                $('#select-all-guru').prop('checked', false);
                                $('#previewMassGuruMsg').text('(Pilih template untuk melihat preview)')
                                    .addClass('text-muted');
                                $('#char_count_mass_guru').text(0);
                                $('#selected-count-guru').text(0);
                            } else if (type === 'orangtua') {
                                $('.orangtua-checkbox-mass').prop('checked', false);
                                $('#select-all-orangtua').prop('checked', false);
                                $('#previewMassOrangtuaMsg').text(
                                    '(Pilih template untuk melihat preview)').addClass('text-muted');
                                $('#char_count_mass_orangtua').text(0);
                                $('#selected-count-orangtua').text(0);
                            }
                        }
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || 'Gagal mengirim pesan.');
                    }
                });
            }
        });
    </script>
@endsection
