@extends('adminlte::page')

@section('title', 'Kirim Pesan')

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Kirim Pesan - {{ $messageTemplate->nama_template }}</h1>
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
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Preview Pesan</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-light border">
                            <strong>Template:</strong> {{ $messageTemplate->nama_template }}<br>
                            <strong>Tipe:</strong> <span
                                class="badge badge-info">{{ ucfirst($messageTemplate->tipe_template) }}</span><br><br>
                            <strong>Isi Pesan:</strong><br>
                            <p class="text-break">{{ $messageTemplate->isi_template }}</p>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Pesan akan dikirim melalui Fonnte (WhatsApp/SMS)
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="personal-tab" data-toggle="pill" href="#personal" role="tab">
                            <i class="fas fa-user"></i> Pengiriman Personal
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="mass-tab" data-toggle="pill" href="#mass" role="tab">
                            <i class="fas fa-users"></i> Pengiriman Masal
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- Personal -->
                    <div class="tab-pane fade show active" id="personal" role="tabpanel">
                        <div class="card mt-0" style="border-top: none;">
                            <div class="card-header">
                                <h5 class="card-title">Kirim ke Siswa Individual</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('message-template.send-personal', $messageTemplate) }}"
                                    method="POST">
                                    @csrf
                                    <div class="form-group">
                                        <label for="siswa_id_personal">Pilih Siswa <span
                                                class="text-danger">*</span></label>
                                        <select class="form-control select2" id="siswa_id_personal" name="siswa_id"
                                            required>
                                            <option value="">-- Pilih Siswa --</option>
                                            @foreach ($siswas as $siswa)
                                                <option value="{{ $siswa->id }}"
                                                    {{ $siswa->no_hp_siswa ? '' : 'disabled' }}>
                                                    {{ $siswa->nis }} - {{ $siswa->nama_siswa }}
                                                    {{ $siswa->no_hp_siswa ? '(' . $siswa->no_hp_siswa . ')' : '(Tanpa HP)' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-paper-plane"></i> Kirim Pesan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Mass -->
                    <div class="tab-pane fade" id="mass" role="tabpanel">
                        <div class="card mt-0" style="border-top: none;">
                            <div class="card-header">
                                <h5 class="card-title">Kirim ke Banyak Siswa</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('message-template.send-mass', $messageTemplate) }}" method="POST">
                                    @csrf
                                    <div class="form-group">
                                        <label class="mb-3">
                                            <input type="checkbox" id="select-all">
                                            <strong>Pilih Semua Siswa</strong>
                                        </label>
                                        <div class="border rounded p-3"
                                            style="max-height: 400px; overflow-y: auto; background-color: #f9f9f9;">
                                            @foreach ($siswas as $siswa)
                                                <div class="custom-control custom-checkbox mb-2">
                                                    <input type="checkbox" class="custom-control-input siswa-checkbox"
                                                        id="siswa_{{ $siswa->id }}" name="siswa_ids[]"
                                                        value="{{ $siswa->id }}"
                                                        {{ $siswa->no_hp_siswa ? '' : 'disabled' }}>
                                                    <label class="custom-control-label" for="siswa_{{ $siswa->id }}">
                                                        {{ $siswa->nis }} - {{ $siswa->nama_siswa }}
                                                        {{ $siswa->no_hp_siswa ? '(' . $siswa->no_hp_siswa . ')' : '(Tanpa HP)' }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <p class="text-muted">
                                            Total siswa dipilih: <span id="selected-count">0</span>
                                        </p>
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-success" id="btnSendMass">
                                            <i class="fas fa-paper-plane"></i> Kirim Pesan ke Semua yang Dipilih
                                        </button>
                                    </div>
                                </form>
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
            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap',
                allowClear: true,
                placeholder: '-- Pilih Siswa --'
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

            function updateSelectedCount() {
                var count = $('.siswa-checkbox:checked').length;
                $('#selected-count').text(count);

                if (count === 0) {
                    $('#btnSendMass').prop('disabled', true);
                } else {
                    $('#btnSendMass').prop('disabled', false);
                }
            }

            // Initial update
            updateSelectedCount();

            // Disable send button if no siswa selected
            $('form').on('submit', function(e) {
                if ($('input[name="siswa_ids[]"]:checked').length === 0) {
                    e.preventDefault();
                    alert('Pilih minimal satu siswa untuk mengirim pesan.');
                }
            });
        });
    </script>
@endsection
