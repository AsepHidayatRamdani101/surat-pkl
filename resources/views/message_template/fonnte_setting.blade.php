@extends('adminlte::page')

@section('title', 'Pengaturan Fonnte')

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Pengaturan Fonnte</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('message-template.send-message') }}" class="btn btn-sm btn-secondary">
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
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
            </div>
        @endif

        <div class="row">
            {{-- Pengaturan Token --}}
            <div class="col-md-6">
                <div class="card card-primary">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-key mr-1"></i> Token API Fonnte</h5>
                    </div>
                    <form method="POST" action="{{ route('message-template.fonnte-setting.store') }}">
                        @csrf
                        <div class="card-body">
                            <div class="form-group">
                                <label for="token">Token Fonnte</label>
                                <div class="input-group">
                                    <input type="password" class="form-control @error('token') is-invalid @enderror"
                                        id="token" name="token" value="{{ old('token', $setting->token) }}"
                                        placeholder="Masukkan token API Fonnte..." autocomplete="off">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary" id="btnToggleToken"
                                            title="Lihat/sembunyikan token">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    @error('token')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <small class="form-text text-muted">
                                    Token didapat dari dashboard <a href="https://md.fonnte.com" target="_blank"
                                        rel="noopener">md.fonnte.com</a> pada menu Device.
                                </small>
                            </div>

                            @if (!$setting->token && $envToken)
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Saat ini aplikasi memakai token dari file <code>.env</code> (FONNTE_TOKEN).
                                    Jika token disimpan di sini, token database yang akan dipakai.
                                </div>
                            @elseif ($setting->token)
                                <div class="alert alert-success mb-0">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Token tersimpan di database.
                                    @if ($setting->updated_at)
                                        Terakhir diperbarui: {{ $setting->updated_at->format('d/m/Y H:i') }}
                                    @endif
                                </div>
                            @else
                                <div class="alert alert-warning mb-0">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Token belum diatur. Pengiriman WhatsApp tidak akan berfungsi.
                                </div>
                            @endif
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Simpan Token
                            </button>
                            <button type="button" class="btn btn-info" id="btnTestConnection">
                                <i class="fas fa-plug mr-1"></i> Tes Koneksi
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Hasil tes koneksi --}}
                <div class="card d-none" id="cardConnectionResult">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-info-circle mr-1"></i> Hasil Tes Koneksi</h5>
                    </div>
                    <div class="card-body" id="connectionResultBody"></div>
                </div>
            </div>

            {{-- Tes Kirim --}}
            <div class="col-md-6">
                <div class="card card-success">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fab fa-whatsapp mr-1"></i> Tes Kirim Pesan WhatsApp</h5>
                    </div>
                    <form id="formTestSend">
                        @csrf
                        <div class="card-body">
                            <div class="form-group">
                                <label for="nomor_tujuan">Nomor WhatsApp Tujuan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nomor_tujuan" name="nomor_tujuan"
                                    placeholder="Contoh: 08123456789 atau 628123456789" required maxlength="20">
                                <small class="form-text text-muted">
                                    Nomor diawali 0 otomatis diubah menjadi 62.
                                </small>
                            </div>
                            <div class="form-group mb-0">
                                <label>Pesan yang akan dikirim:</label>
                                <div class="alert alert-light border mb-0">
                                    <p class="mb-0 text-muted" style="white-space: pre-line;">✅ *Tes Kirim Pesan*

Ini adalah pesan percobaan dari aplikasi SIPKL.
Jika Anda menerima pesan ini, berarti konfigurasi Fonnte sudah berfungsi dengan baik.</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-success" id="btnTestSend">
                                <i class="fas fa-paper-plane mr-1"></i> Kirim Pesan Tes
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Hasil tes kirim --}}
                <div class="alert d-none" id="alertTestSendResult"></div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        // Toggle lihat/sembunyikan token
        document.getElementById('btnToggleToken').addEventListener('click', function () {
            var input = document.getElementById('token');
            var icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });

        var csrfToken = document.querySelector('input[name="_token"]').value;

        // Tes koneksi
        document.getElementById('btnTestConnection').addEventListener('click', function () {
            var btn = this;
            var card = document.getElementById('cardConnectionResult');
            var body = document.getElementById('connectionResultBody');

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1"></span> Menghubungkan...';
            card.classList.add('d-none');

            fetch('{{ route('message-template.fonnte-test-connection') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    var html = '';
                    if (data.success) {
                        html += '<div class="alert alert-success mb-2"><i class="fas fa-check-circle mr-1"></i> ' + data.message + '</div>';
                        if (data.device) {
                            html += '<table class="table table-sm table-bordered mb-0">'
                                + '<tr><th style="width:40%">Nama Device</th><td>' + data.device.name + '</td></tr>'
                                + '<tr><th>Nomor Device</th><td>' + data.device.device + '</td></tr>'
                                + '<tr><th>Status</th><td>' + (data.device.status === 'connect'
                                    ? '<span class="badge badge-success">Terhubung</span>'
                                    : '<span class="badge badge-danger">' + data.device.status + '</span>') + '</td></tr>'
                                + '<tr><th>Sisa Kuota</th><td>' + data.device.quota + '</td></tr>'
                                + '<tr><th>Expired</th><td>' + data.device.expired + '</td></tr>'
                                + '</table>';
                        }
                    } else {
                        html = '<div class="alert alert-danger mb-0"><i class="fas fa-times-circle mr-1"></i> ' + data.message + '</div>';
                    }
                    body.innerHTML = html;
                    card.classList.remove('d-none');
                })
                .catch(function () {
                    body.innerHTML = '<div class="alert alert-danger mb-0"><i class="fas fa-times-circle mr-1"></i> Terjadi kesalahan saat menghubungi server.</div>';
                    card.classList.remove('d-none');
                })
                .finally(function () {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-plug mr-1"></i> Tes Koneksi';
                });
        });

        // Tes kirim pesan
        document.getElementById('formTestSend').addEventListener('submit', function (e) {
            e.preventDefault();

            var btn = document.getElementById('btnTestSend');
            var alertBox = document.getElementById('alertTestSendResult');
            var nomor = document.getElementById('nomor_tujuan').value;

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1"></span> Mengirim...';
            alertBox.classList.add('d-none');

            fetch('{{ route('message-template.fonnte-test-send') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ nomor_tujuan: nomor }),
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    alertBox.className = 'alert ' + (data.success ? 'alert-success' : 'alert-danger');
                    alertBox.innerHTML = '<i class="fas ' + (data.success ? 'fa-check-circle' : 'fa-times-circle') + ' mr-1"></i> '
                        + (data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Terjadi kesalahan.'));
                })
                .catch(function () {
                    alertBox.className = 'alert alert-danger';
                    alertBox.innerHTML = '<i class="fas fa-times-circle mr-1"></i> Terjadi kesalahan saat menghubungi server.';
                })
                .finally(function () {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-paper-plane mr-1"></i> Kirim Pesan Tes';
                });
        });
    </script>
@endsection
