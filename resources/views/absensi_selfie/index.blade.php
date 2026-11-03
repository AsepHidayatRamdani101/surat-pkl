@extends('adminlte::page')

@section('title', $title)

@section('content_header')
    <h1>{{ $title }}</h1>
@endsection

@section('css')
    @include('partials.table_styles')
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-5">
            <div class="card app-card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                    <div class="d-flex align-items-center my-1">
                        <span class="card-title-icon"><i class="fas fa-clipboard-check"></i></span>
                        <div>
                            <h4>Status Absen Hari Ini</h4>
                            <small>{{ now()->translatedFormat('d F Y') }}</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2">
                        <div>
                            <strong>Absen Datang</strong><br>
                            <span id="statusDatang" class="{{ $absenDatang ? 'text-success' : 'text-muted' }}">
                                {{ $absenDatang ? 'Sudah absen pukul ' . substr($absenDatang->waktu, 0, 5) : 'Belum absen' }}
                            </span>
                        </div>
                        <i class="fas {{ $absenDatang ? 'fa-check-circle text-success' : 'fa-clock text-muted' }} fa-2x"></i>
                    </div>
                    <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-3">
                        <div>
                            <strong>Absen Pulang</strong><br>
                            <span id="statusPulang" class="{{ $absenPulang ? 'text-success' : 'text-muted' }}">
                                {{ $absenPulang ? 'Sudah absen pukul ' . substr($absenPulang->waktu, 0, 5) : 'Belum absen' }}
                            </span>
                        </div>
                        <i class="fas {{ $absenPulang ? 'fa-check-circle text-success' : 'fa-clock text-muted' }} fa-2x"></i>
                    </div>

                    @if ($perusahaan)
                        <div class="alert alert-info py-2 mb-0">
                            <i class="fas fa-building mr-1"></i>
                            Tempat PKL: <strong>{{ $perusahaan->nama_perusahaan }}</strong>
                            @if ($perusahaan->latitude === null || $perusahaan->longitude === null)
                                <br><small class="text-danger">Koordinat perusahaan belum diatur, akurasi lokasi tidak
                                    dapat dihitung.</small>
                            @endif
                        </div>
                    @else
                        <div class="alert alert-warning py-2 mb-0">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Data tempat PKL Anda belum tersedia.
                        </div>
                    @endif
                </div>
            </div>

            <div class="card app-card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                    <div class="d-flex align-items-center my-1">
                        <span class="card-title-icon"><i class="fas fa-history"></i></span>
                        <div>
                            <h4>Riwayat Absen</h4>
                            <small>Riwayat absen selfie Anda</small>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive p-0" style="max-height: 320px;">
                    <table class="table table-striped table-hover table-sm mb-0" id="riwayatTable">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jenis</th>
                                <th>Waktu</th>
                                <th>Akurasi Lokasi</th>
                                <th>Foto</th>
                            </tr>
                        </thead>
                        <tbody id="riwayatBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card app-card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                    <div class="d-flex align-items-center my-1">
                        <span class="card-title-icon"><i class="fas fa-camera"></i></span>
                        <div>
                            <h4>Ambil Selfie Absen</h4>
                            <small>Absen datang & pulang dengan foto selfie dan lokasi GPS</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Jenis Absen</label>
                        <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
                            <label class="btn btn-outline-primary {{ $absenDatang ? '' : 'active' }} w-100"
                                id="labelDatang">
                                <input type="radio" name="jenis" value="datang"
                                    {{ $absenDatang ? 'disabled' : 'checked' }}>
                                <i class="fas fa-sign-in-alt mr-1"></i> Datang
                            </label>
                            <label class="btn btn-outline-primary {{ $absenDatang && !$absenPulang ? 'active' : '' }} w-100"
                                id="labelPulang">
                                <input type="radio" name="jenis" value="pulang"
                                    {{ $absenPulang ? 'disabled' : ($absenDatang ? 'checked' : '') }}>
                                <i class="fas fa-sign-out-alt mr-1"></i> Pulang
                            </label>
                        </div>
                    </div>

                    <div class="position-relative mb-2 bg-dark rounded text-center" style="min-height: 280px;">
                        <video id="cameraPreview" autoplay playsinline muted class="w-100 rounded"
                            style="max-height: 380px; transform: scaleX(-1);"></video>
                        <canvas id="selfieCanvas" class="d-none"></canvas>
                        <img id="selfieResult" class="w-100 rounded d-none" style="max-height: 380px;" alt="Hasil selfie">
                        <div id="cameraError" class="text-white p-5 d-none">
                            <i class="fas fa-video-slash fa-2x mb-2"></i>
                            <p class="mb-0">Kamera tidak dapat diakses. Izinkan akses kamera pada browser Anda.</p>
                        </div>
                    </div>

                    <div id="lokasiInfo" class="alert alert-secondary py-2">
                        <i class="fas fa-map-marker-alt mr-1"></i>
                        <span id="lokasiText">Mengambil lokasi GPS...</span>
                    </div>

                    <div class="d-flex">
                        <button type="button" class="btn btn-primary mr-2" id="btnAmbilFoto" disabled>
                            <i class="fas fa-camera mr-1"></i> Ambil Foto
                        </button>
                        <button type="button" class="btn btn-secondary mr-2 d-none" id="btnUlangi">
                            <i class="fas fa-redo mr-1"></i> Ulangi
                        </button>
                        <button type="button" class="btn btn-success d-none" id="btnKirim">
                            <i class="fas fa-paper-plane mr-1"></i> Kirim Absen
                        </button>
                    </div>
                    <small class="form-text text-muted mt-2">
                        Foto harus diambil langsung dari kamera. Upload foto dari galeri tidak diperbolehkan.
                        Pastikan GPS/layanan lokasi aktif.
                    </small>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function() {
            const csrf = $('meta[name="csrf-token"]').attr('content');
            const perusahaanKoordinat = @json($perusahaan && $perusahaan->latitude !== null && $perusahaan->longitude !== null ? ['lat' => (float) $perusahaan->latitude, 'lng' => (float) $perusahaan->longitude, 'nama' => $perusahaan->nama_perusahaan] : null);
            const RADIUS_PENUH = 100,
                RADIUS_NOL = 1000;
            let stream = null,
                fotoDataUrl = null,
                posisi = null;

            function jarakMeter(lat1, lon1, lat2, lon2) {
                const rad = deg => deg * Math.PI / 180;
                const dLat = rad(lat2 - lat1),
                    dLon = rad(lon2 - lon1);
                const a = Math.sin(dLat / 2) ** 2 +
                    Math.cos(rad(lat1)) * Math.cos(rad(lat2)) * Math.sin(dLon / 2) ** 2;
                return 6371000 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            }

            function persentaseLokasi(jarak) {
                if (jarak <= RADIUS_PENUH) return 100;
                if (jarak >= RADIUS_NOL) return 0;
                return Math.round((1 - (jarak - RADIUS_PENUH) / (RADIUS_NOL - RADIUS_PENUH)) * 100);
            }

            function formatJarak(meter) {
                return meter >= 1000 ?
                    (meter / 1000).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' km' :
                    Math.round(meter).toLocaleString('id-ID') + ' m';
            }

            const video = document.getElementById('cameraPreview');
            const canvas = document.getElementById('selfieCanvas');

            function mulaiKamera() {
                navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user',
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    },
                    audio: false
                }).then(function(mediaStream) {
                    stream = mediaStream;
                    video.srcObject = mediaStream;
                    $('#cameraError').addClass('d-none');
                    $('#cameraPreview').removeClass('d-none');
                    $('#btnAmbilFoto').prop('disabled', !posisi);
                }).catch(function() {
                    $('#cameraError').removeClass('d-none');
                    $('#cameraPreview').addClass('d-none');
                    $('#btnAmbilFoto').prop('disabled', true);
                });
            }

            function ambilLokasi() {
                if (!navigator.geolocation) {
                    $('#lokasiInfo').removeClass('alert-secondary').addClass('alert-danger');
                    $('#lokasiText').text('Browser tidak mendukung GPS.');
                    return;
                }

                navigator.geolocation.watchPosition(function(pos) {
                    posisi = {
                        latitude: pos.coords.latitude,
                        longitude: pos.coords.longitude,
                        akurasi: pos.coords.accuracy
                    };

                    let info = `Lokasi terdeteksi (akurasi GPS perangkat ± ${Math.round(pos.coords.accuracy)} m).`;
                    let alertClass = 'alert-success';

                    if (perusahaanKoordinat) {
                        const jarak = jarakMeter(pos.coords.latitude, pos.coords.longitude,
                            perusahaanKoordinat.lat, perusahaanKoordinat.lng);
                        const persen = persentaseLokasi(jarak);
                        alertClass = persen >= 80 ? 'alert-success' : (persen >= 40 ? 'alert-warning' :
                            'alert-danger');
                        info = `Jarak Anda ± ${formatJarak(jarak)} dari ${perusahaanKoordinat.nama} — ` +
                            `presentase ${persen}% mendekati perusahaan. ` +
                            `(Akurasi GPS perangkat ± ${Math.round(pos.coords.accuracy)} m)`;
                    }

                    $('#lokasiInfo').removeClass('alert-secondary alert-danger alert-warning alert-success')
                        .addClass(alertClass);
                    $('#lokasiText').text(info);
                    if (stream) $('#btnAmbilFoto').prop('disabled', false);
                }, function() {
                    $('#lokasiInfo').removeClass('alert-secondary alert-success alert-warning')
                        .addClass('alert-danger');
                    $('#lokasiText').text(
                        'Lokasi tidak dapat diambil. Izinkan akses lokasi lalu muat ulang halaman.');
                }, {
                    enableHighAccuracy: true,
                    maximumAge: 10000,
                    timeout: 15000
                });
            }

            $('#btnAmbilFoto').on('click', function() {
                if (!stream || !posisi) return;
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const ctx = canvas.getContext('2d');
                // Mirror agar sesuai preview
                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                fotoDataUrl = canvas.toDataURL('image/jpeg', 0.85);

                $('#selfieResult').attr('src', fotoDataUrl).removeClass('d-none');
                $('#cameraPreview').addClass('d-none');
                $(this).addClass('d-none');
                $('#btnUlangi, #btnKirim').removeClass('d-none');
            });

            $('#btnUlangi').on('click', function() {
                fotoDataUrl = null;
                $('#selfieResult').addClass('d-none');
                $('#cameraPreview').removeClass('d-none');
                $('#btnAmbilFoto').removeClass('d-none');
                $('#btnUlangi, #btnKirim').addClass('d-none');
            });

            $('#btnKirim').on('click', function() {
                if (!fotoDataUrl || !posisi) return;
                const jenis = $('input[name="jenis"]:checked').val();
                if (!jenis) {
                    Swal.fire('Perhatian', 'Pilih jenis absen (datang/pulang).', 'warning');
                    return;
                }

                const $btn = $(this).prop('disabled', true);
                $.ajax({
                    url: '{{ route('absensi-selfie.store') }}',
                    method: 'POST',
                    data: {
                        jenis: jenis,
                        foto: fotoDataUrl,
                        latitude: posisi.latitude,
                        longitude: posisi.longitude,
                        akurasi_gps: posisi.akurasi
                    },
                    headers: {
                        'X-CSRF-TOKEN': csrf
                    }
                }).done(function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Absen berhasil',
                        text: response.keterangan_lokasi || response.message
                    }).then(() => location.reload());
                }).fail(function(xhr) {
                    const errors = xhr.responseJSON?.errors;
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: errors ? Object.values(errors).flat().join('\n') : (xhr
                            .responseJSON?.message || 'Absen tidak dapat disimpan.')
                    });
                    $btn.prop('disabled', false);
                });
            });

            function persentaseBadge(value) {
                if (value === null || value === undefined) return '<span class="text-muted">-</span>';
                const warna = value >= 80 ? 'success' : (value >= 40 ? 'warning' : 'danger');
                return `<span class="badge badge-${warna}">${value}% mendekati perusahaan</span>`;
            }

            function muatRiwayat() {
                $.getJSON('{{ route('absensi-selfie.data') }}', function(rows) {
                    const html = (rows || []).map(row => `<tr>
                        <td>${row.tanggal}</td>
                        <td class="text-capitalize">${row.jenis}</td>
                        <td>${(row.waktu || '').substring(0, 5)}</td>
                        <td>${persentaseBadge(row.persentase_lokasi)}${row.jarak_meter !== null ? `<br><small class="text-muted">± ${row.jarak_meter} m</small>` : ''}</td>
                        <td><a href="/storage/${row.foto_path}" target="_blank" rel="noopener">Lihat</a></td>
                    </tr>`).join('');
                    $('#riwayatBody').html(html ||
                        '<tr><td colspan="5" class="text-center text-muted">Belum ada riwayat absen.</td></tr>'
                    );
                });
            }

            @if ($absenDatang && $absenPulang)
                $('#btnAmbilFoto').prop('disabled', true);
                $('#lokasiInfo').removeClass('alert-secondary').addClass('alert-success');
                $('#lokasiText').text('Absen datang dan pulang hari ini sudah lengkap.');
            @else
                mulaiKamera();
                ambilLokasi();
            @endif
            muatRiwayat();

            $(window).on('beforeunload', function() {
                if (stream) stream.getTracks().forEach(track => track.stop());
            });
        });
    </script>
@endsection
