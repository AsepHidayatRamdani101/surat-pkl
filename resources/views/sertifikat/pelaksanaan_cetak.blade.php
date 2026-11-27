<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Pelaksanaan PKL</title>
    <style>
        @if(!empty($customFontDataUri) && !empty($customFontName)) @font-face { font-family: '{{ $customFontName }}'; src: url('{{ $customFontDataUri }}'); } @endif
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 24px;
            font-family: {{ $fontFamily ?? 'Georgia, "Times New Roman", serif' }};
            background: #e5e7eb;
            color: #1f2937;
        }

        .print-actions {
            text-align: center;
            margin-bottom: 16px;
            font-family: "Segoe UI", Arial, sans-serif;
        }

        .btn {
            border: 1px solid #d1d5db;
            background: #ffffff;
            color: #111827;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 0 4px;
            font-size: 14px;
        }

        .btn-primary {
            background: #0f766e;
            color: #ffffff;
            border-color: #0f766e;
        }

        .btn-warning {
            background: #d97706;
            color: #ffffff;
            border-color: #d97706;
        }

        .btn-temp {
            background: #4338ca;
            color: #ffffff;
            border-color: #4338ca;
        }

        .temp-edit-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(17, 24, 39, .55);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            font-family: "Segoe UI", Arial, sans-serif;
        }

        .temp-edit-overlay.open {
            display: flex;
        }

        .temp-edit-panel {
            width: 100%;
            max-width: 420px;
            max-height: 90vh;
            overflow-y: auto;
            background: #ffffff;
            border-radius: 10px;
            padding: 20px 22px;
            text-align: left;
            box-shadow: 0 12px 32px rgba(0, 0, 0, .25);
        }

        .temp-edit-panel h3 {
            margin: 0 0 4px;
            font-size: 16px;
            color: #111827;
        }

        .temp-edit-panel p.hint {
            margin: 0 0 14px;
            font-size: 12px;
            color: #6b7280;
        }

        .temp-edit-field {
            margin-bottom: 10px;
        }

        .temp-edit-field label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .temp-edit-field input {
            width: 100%;
            padding: 6px 8px;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            font-size: 13px;
            box-sizing: border-box;
        }

        .temp-edit-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 12px;
        }

        .temp-edit-actions button {
            border: 1px solid #d1d5db;
            background: #f3f4f6;
            color: #111827;
            padding: 6px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
        }

        .temp-edit-actions button.apply {
            background: #4338ca;
            color: #ffffff;
            border-color: #4338ca;
        }

        .sheet {
            width: 1123px;
            height: 794px;
            margin: 0 auto;
            background: #fffdf7;
            @if(!empty($backgroundFrontDataUri ?? $backgroundDataUri)) background-image: url('{{ $backgroundFrontDataUri ?? $backgroundDataUri }}'); background-size: cover; background-position: center; @endif
            box-shadow: 0 10px 30px rgba(0, 0, 0, .25);
            padding: 18px;
            position: relative;
        }

        .frame {
            width: 100%;
            height: 100%;
            border: 3px solid #1d4ed8;
            padding: 6px;
            position: relative;
        }

        .frame-inner {
            width: 100%;
            height: 100%;
            border: 1px solid #b45309;
            padding: 30px 56px 24px;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .corner {
            position: absolute;
            width: 46px;
            height: 46px;
            border-color: #1d4ed8;
            border-style: solid;
            border-width: 0;
        }

        .corner.tl { top: 10px; left: 10px; border-top-width: 4px; border-left-width: 4px; }
        .corner.tr { top: 10px; right: 10px; border-top-width: 4px; border-right-width: 4px; }
        .corner.bl { bottom: 10px; left: 10px; border-bottom-width: 4px; border-left-width: 4px; }
        .corner.br { bottom: 10px; right: 10px; border-bottom-width: 4px; border-right-width: 4px; }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 440px;
            opacity: .05;
            pointer-events: none;
        }

        .header {
            text-align: center;
            position: relative;
        }

        .header .logo {
            position: absolute;
            left: 0;
            top: 0;
            width: 72px;
        }

        .header .instansi {
            font-size: 13px;
            letter-spacing: 2px;
            color: #4b5563;
            text-transform: uppercase;
            margin: 0;
        }

        .header .sekolah {
            font-size: 24px;
            font-weight: bold;
            color: #1d4ed8;
            letter-spacing: 1px;
            margin: 2px 0 0;
        }

        .header .alamat {
            font-size: 11px;
            color: #6b7280;
            margin: 2px 0 0;
        }

        .title-block {
            text-align: center;
            margin-top: 18px;
        }

        .title {
            font-size: 44px;
            letter-spacing: 10px;
            color: #b45309;
            margin: 0;
            font-weight: bold;
        }

        .subtitle {
            font-size: 15px;
            letter-spacing: 4px;
            color: #374151;
            text-transform: uppercase;
            margin: 4px 0 0;
        }

        .nomor {
            font-size: 12px;
            color: #ffffff;
            margin: 6px 0 0;
        }

        .body-block {
            text-align: center;
            margin-top: 14px;
        }

        .diberikan {
            font-size: 14px;
            font-style: italic;
            color: #4b5563;
            margin: 0;
        }

        .nama-siswa {
            font-size: 34px;
            font-weight: bold;
            color: #111827;
            margin: 6px auto 0;
            padding-bottom: 6px;
            border-bottom: 2px solid #b45309;
            display: inline-block;
            min-width: 440px;
        }

        .identitas {
            font-size: 13px;
            color: #4b5563;
            margin: 8px 0 0;
        }

        .pernyataan {
            font-size: 13px;
            color: #374151;
            max-width: 760px;
            margin: 10px auto 0;
            line-height: 1.6;
        }

        .pernyataan strong {
            color: #111827;
        }

        .footer {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .ttd-block {
            text-align: center;
            font-size: 13px;
            color: #1f2937;
            min-width: 280px;
        }

        .ttd-block .ttd-img {
            height: 58px;
            margin: 4px 0;
        }

        .ttd-block .ttd-space {
            height: 58px;
        }

        .ttd-block .nama-ttd {
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
        }

        .ttd-block .jabatan {
            font-size: 12px;
            color: #4b5563;
            margin: 2px 0 0;
        }

        .cap-area {
            text-align: center;
            font-size: 11px;
            color: #9ca3af;
        }

        .cap-area img {
            width: 90px;
            opacity: .9;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 0;
            }

            body {
                background: #ffffff;
                padding: 0;
            }

            .print-actions,
            .temp-edit-overlay {
                display: none !important;
            }

            .sheet {
                width: 100%;
                height: 100vh;
                box-shadow: none;
                margin: 0;
            }
        }
    </style>
</head>

<body>
    <div class="print-actions">
        @if (!empty($editDataUrl))
            <a class="btn btn-warning" href="{{ $editDataUrl }}">Edit Pembimbing &amp; Pimpinan</a>
        @endif
        <button class="btn btn-temp" type="button" onclick="document.getElementById('tempEditOverlay').classList.add('open')">Edit Sementara (Tidak Disimpan)</button>
        <a class="btn btn-primary" href="{{ $downloadUrl }}">Download PDF</a>
        <button class="btn" onclick="window.print()">Print</button>
        <a class="btn" href="{{ $backUrl ?? route('dashboard') }}">Kembali</a>
    </div>

    <div id="tempEditOverlay" class="temp-edit-overlay">
        <div class="temp-edit-panel">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <h3>Edit Tampilan Sementara</h3>
                <button type="button" onclick="document.getElementById('tempEditOverlay').classList.remove('open')" style="border:none;background:none;font-size:20px;line-height:1;cursor:pointer;color:#6b7280;padding:0 0 0 12px;">&times;</button>
            </div>
            <p class="hint">Perubahan hanya tampil di layar ini dan tidak disimpan ke database. Muat ulang halaman untuk mengembalikan data asli.</p>

            <div class="temp-edit-field">
                <label for="tempNamaPerusahaan">Nama Perusahaan</label>
                <input type="text" id="tempNamaPerusahaan" placeholder="Nama perusahaan">
            </div>
            <div class="temp-edit-field">
                <label for="tempNamaPimpinan">Nama Pimpinan</label>
                <input type="text" id="tempNamaPimpinan" placeholder="Nama pimpinan perusahaan">
            </div>
            <div class="temp-edit-field">
                <label for="tempNipPimpinan">NIP Pimpinan</label>
                <input type="text" id="tempNipPimpinan" placeholder="NIP pimpinan">
            </div>
            <div class="temp-edit-field">
                <label for="tempNamaPembimbing">Nama Pembimbing</label>
                <input type="text" id="tempNamaPembimbing" placeholder="Nama pembimbing">
            </div>
            <div class="temp-edit-field">
                <label for="tempNipPembimbing">NIP Pembimbing</label>
                <input type="text" id="tempNipPembimbing" placeholder="NIP pembimbing">
            </div>

            <div class="temp-edit-actions">
                <button type="button" onclick="resetTempSertifikatEdit()">Reset</button>
                <button type="button" onclick="saveTempSertifikatEdit()">Simpan Temporer</button>
                <button type="button" class="apply" onclick="applyTempSertifikatEdit({ closeModal: true, notify: true })">Terapkan</button>
            </div>
        </div>
    </div>

    <div class="sheet">
        <div class="frame">
            <div class="corner tl"></div>
            <div class="corner tr"></div>
            <div class="corner bl"></div>
            <div class="corner br"></div>

            <div class="frame-inner">
                @if ($logoPath)
                    <img class="watermark" src="{{ asset($logoPath) }}" alt="">
                @endif

                <div class="header">
                    @if ($logoPath)
                        <img class="logo" src="{{ asset($logoPath) }}" alt="Logo SMKN 8 Garut">
                    @endif
                    <p class="instansi">Pemerintah Daerah Provinsi Jawa Barat &mdash; Dinas Pendidikan</p>
                    <p class="sekolah">SMK NEGERI 8 GARUT</p>
                    <p class="alamat">Praktik Kerja Lapangan Tahun {{ $tanggalCetak->format('Y') }}</p>
                </div>

                <div class="title-block">
                    <h1 class="title">SERTIFIKAT</h1>
                    <p class="subtitle">Pelaksanaan Praktik Kerja Lapangan</p>
                    <p class="nomor">Nomor: SPL/{{ $siswa->nisn ?? $siswa->nis }}/{{ $tanggalCetak->format('Y') }}</p>
                </div>

                <div class="body-block">
                    <p class="diberikan">diberikan kepada</p>
                    <div class="nama-siswa">{{ strtoupper($siswa->nama_siswa) }}</div>
                    <p class="identitas">
                        NIS: {{ $siswa->nisn ?? $siswa->nis }} &nbsp;|&nbsp;
                        Kelas: {{ $siswa->kelas->nama_kelas ?? '-' }} &nbsp;|&nbsp;
                        Jurusan: {{ $siswa->kelas->jurusan->nama_jurusan ?? '-' }}
                    </p>
                    <p class="pernyataan">
                        yang telah melaksanakan <strong>Praktik Kerja Lapangan (PKL)</strong>
                        di <strong>{{ $perusahaan->nama_perusahaan }}</strong>
                        @if ($perusahaan->alamat), {{ $perusahaan->alamat }}@endif,
                        terhitung mulai tanggal
                        <strong>{{ $tanggalMulai?->locale('id')->translatedFormat('d F Y') ?? '-' }}</strong>
                        sampai dengan
                        <strong>{{ $tanggalSelesai?->locale('id')->translatedFormat('d F Y') ?? '-' }}</strong>
                        @if ($durasi)({{ $durasi }})@endif
                        dengan baik dan penuh tanggung jawab.
                        @if ($sertifikatSidang)
                            <br>Nilai Akhir: <strong>{{ rtrim(rtrim(number_format($sertifikatSidang->nilai_akhir, 2), '0'), '.') }}</strong>
                            &mdash; Predikat: <strong>{{ $sertifikatSidang->predikat }}</strong>
                        @endif
                    </p>
                </div>

                <div class="footer">
                    <div class="ttd-block">
                        <div>&nbsp;</div>
                        <div>{{ $pemilikSamaPembimbing ? 'Pemilik/Pembimbing' : 'Pembimbing' }} {{ $perusahaan->nama_perusahaan }},</div>
                        <div class="ttd-space"></div>
                        <p class="nama-ttd">{{ $namaPembimbing ?? '____________________' }}</p>
                        <p class="jabatan">
                            @if ($nipPembimbing)
                                NIP. {{ $nipPembimbing }}<br>
                            @endif
                            Pembimbing Perusahaan/Instansi
                        </p>
                    </div>

                    <div class="cap-area">
                        @if ($capPath)
                            <img src="{{ asset($capPath) }}" alt="Cap SMKN 8 Garut">
                        @endif
                        @unless ($pemilikSamaPembimbing)
                            <div style="margin-top: 8px; font-size: 12px; color: #4b5563;">
                                Mengetahui,<br>Pemilik Perusahaan/Instansi<br><br><br>
                                <strong style="color:#1f2937; text-decoration: underline;">{{ $namaPemilik ?? '____________________' }}</strong>
                                @if ($nipPemilik)
                                    <br>NIP. {{ $nipPemilik }}
                                @endif
                            </div>
                        @endunless
                    </div>

                    <div class="ttd-block">
                        <div>Garut, {{ $tanggalCetak->translatedFormat('d F Y') }}</div>
                        <div>Kepala SMK Negeri 8 Garut,</div>
                        @if ($ttdPath)
                            <img class="ttd-img" src="{{ asset($ttdPath) }}" alt="Tanda tangan">
                        @else
                            <div class="ttd-space"></div>
                        @endif
                        <p class="nama-ttd">{{ $kepalaSekolah }}</p>
                        <p class="jabatan">NIP. {{ $nipKepalaSekolah }}</p>
                    </div>
                </div>
            </div>
        </div>
        @include('sertifikat.partials.design_overlay')
    </div>
@include('sertifikat.partials.halaman_nilai')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    (function () {
        var fieldMap = {
            tempNamaPerusahaan: { selectors: ['[data-key="perusahaan"]'], upper: true },
            tempNamaPimpinan: { selectors: ['[data-key="pimpinan"]'] },
            tempNipPimpinan: { selectors: ['[data-key="pimpinan_nip"]'], nip: true },
            tempNamaPembimbing: { selectors: ['[data-key="signature_name"]'] },
            tempNipPembimbing: { selectors: ['[data-key="signature_nip"]'], nip: true }
        };

        var originalValues = {};
        var saved = false;

        function getElements(selectors) {
            var found = [];
            selectors.forEach(function (selector) {
                document.querySelectorAll(selector).forEach(function (el) {
                    found.push(el);
                });
            });
            return found;
        }

        function captureOriginals() {
            Object.keys(fieldMap).forEach(function (inputId) {
                var config = fieldMap[inputId];
                var el = getElements(config.selectors)[0];
                originalValues[inputId] = el ? el.textContent : '';
            });
        }

        function formatNip(value) {
            var nip = (value || '').trim().replace(/^NIP\.?\s*/i, '');
            return nip !== '' ? 'NIP. ' + nip : '';
        }

        window.applyTempSertifikatEdit = function (options) {
            Object.keys(fieldMap).forEach(function (inputId) {
                var config = fieldMap[inputId];
                var input = document.getElementById(inputId);
                if (!input) return;

                var rawValue = input.value.trim();
                var displayValue = rawValue === ''
                    ? originalValues[inputId]
                    : (config.nip ? formatNip(rawValue) : rawValue);
                if (config.upper && rawValue !== '') displayValue = displayValue.toUpperCase();

                getElements(config.selectors).forEach(function (el) {
                    el.textContent = displayValue;
                });
            });

            if (options && options.closeModal) {
                document.getElementById('tempEditOverlay').classList.remove('open');
            }

            if (options && options.notify && window.Swal) {
                Swal.fire({
                    icon: 'success',
                    title: 'Diterapkan',
                    text: 'Tampilan sertifikat diperbarui sementara di halaman ini.',
                    toast: true,
                    position: 'top-end',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        };

        window.resetTempSertifikatEdit = function () {
            Object.keys(fieldMap).forEach(function (inputId) {
                var config = fieldMap[inputId];
                var input = document.getElementById(inputId);
                if (input) input.value = '';

                getElements(config.selectors).forEach(function (el) {
                    el.textContent = originalValues[inputId];
                });
            });
            saved = false;

            if (window.Swal) {
                Swal.fire({
                    icon: 'info',
                    title: 'Direset',
                    text: 'Tampilan dikembalikan ke data asli.',
                    toast: true,
                    position: 'top-end',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        };

        window.saveTempSertifikatEdit = function () {
            window.applyTempSertifikatEdit();
            saved = true;
            document.getElementById('tempEditOverlay').classList.remove('open');

            if (window.Swal) {
                Swal.fire({
                    icon: 'success',
                    title: 'Tersimpan Sementara',
                    text: 'Perubahan ditampilkan di halaman ini. Tidak disimpan ke database — memuat ulang (refresh) halaman akan mengembalikan data ke semula.',
                    confirmButtonText: 'Mengerti'
                });
            }
        };

        document.addEventListener('DOMContentLoaded', captureOriginals);

        // Refreshing/leaving the page always restores the original server-rendered
        // values, since nothing here is persisted anywhere (by design).
    })();
</script>
</body>

</html>
