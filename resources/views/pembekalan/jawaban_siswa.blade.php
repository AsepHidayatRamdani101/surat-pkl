@extends('adminlte::page')

@section('title', 'Jawaban Tugas Siswa')

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Jawaban Tugas Siswa</h1>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid pt-3">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('pembekalan.jawaban-siswa') }}">
                    <div class="form-row align-items-end">
                        <div class="col-md-3 mb-2">
                            <label class="mb-1">Kelompok Bimbingan</label>
                            <select name="kelompok_id" class="form-control form-control-sm">
                                <option value="">Semua Kelompok</option>
                                @foreach ($kelompokOptions as $kelompok)
                                    <option value="{{ $kelompok->id }}"
                                        {{ (string) $filters['kelompok_id'] === (string) $kelompok->id ? 'selected' : '' }}>
                                        {{ $kelompok->nama_kelompok }}
                                        @if ($kelompok->pembimbing)
                                            - {{ $kelompok->pembimbing->nama_pembimbing }}
                                        @endif
                                        ({{ $kelompok->siswa_count }} siswa)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 mb-2">
                            <label class="mb-1">Guru Pembimbing</label>
                            <select name="pembimbing_id" class="form-control form-control-sm">
                                <option value="">Semua Pembimbing</option>
                                @foreach ($pembimbingOptions as $pembimbing)
                                    <option value="{{ $pembimbing->id }}"
                                        {{ (string) $filters['pembimbing_id'] === (string) $pembimbing->id ? 'selected' : '' }}>
                                        {{ $pembimbing->nama_pembimbing }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 mb-2">
                            <label class="mb-1">Materi</label>
                            <select name="materi_id" class="form-control form-control-sm">
                                <option value="">Semua Materi</option>
                                @foreach ($materiOptions as $materi)
                                    <option value="{{ $materi->id }}"
                                        {{ (string) $filters['materi_id'] === (string) $materi->id ? 'selected' : '' }}>
                                        {{ $materi->topik }}
                                        @if ($materi->tanggal_materi)
                                            ({{ \Carbon\Carbon::parse($materi->tanggal_materi)->format('d/m/Y') }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 mb-2">
                            <label class="mb-1">Status Pengerjaan</label>
                            <select name="status" class="form-control form-control-sm">
                                <option value="">Semua Status</option>
                                <option value="belum" {{ ($filters['status'] ?? '') === 'belum' ? 'selected' : '' }}>Belum
                                </option>
                                <option value="proses" {{ ($filters['status'] ?? '') === 'proses' ? 'selected' : '' }}>
                                    Proses</option>
                                <option value="selesai" {{ ($filters['status'] ?? '') === 'selesai' ? 'selected' : '' }}>
                                    Selesai</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row mt-1 align-items-end">
                        <div class="col-md-9 mb-2">
                            <input type="text" name="keyword" class="form-control form-control-sm"
                                placeholder="Cari siswa, materi, judul tugas, atau isi jawaban"
                                value="{{ $filters['keyword'] }}">
                        </div>
                        <div class="col-md-3 mb-2 d-flex">
                            <button type="submit" class="btn btn-sm btn-primary mr-1 w-100">Filter</button>
                            <a href="{{ route('pembekalan.jawaban-siswa') }}"
                                class="btn btn-sm btn-outline-secondary w-100">Reset</a>
                        </div>
                    </div>
                    <input type="hidden" name="filtered" value="1">
                </form>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                {{ session('success') }}
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex align-items-center flex-wrap">
                <div>
                    <h5 class="mb-0">Daftar Jawaban Siswa</h5>
                    @if ($isFiltered)
                        <small class="text-muted">
                            {{ $rows->count() }} data
                            @if ($canInputNilai)
                                &middot; {{ $rows->where('status', 'proses')->count() }} menunggu nilai
                            @endif
                        </small>
                    @endif
                </div>
                @if ($canInputNilai && $isFiltered && $rows->filter(fn($r) => $r->jawaban_id && $r->submitted_at)->isNotEmpty())
                    <button type="button" class="btn btn-sm btn-primary ml-auto" id="btnMulaiNilai">
                        <i class="fas fa-bolt mr-1"></i>Mulai Menilai
                    </button>
                @endif
            </div>
            <div class="card-body table-responsive">
                @if (!$isFiltered)
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-filter fa-2x mb-2 d-block"></i>
                        Gunakan filter di atas lalu klik <strong>Filter</strong> untuk menampilkan data.
                    </div>
                @else
                    <table id="jawabanTable" class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th style="width: 95px;">Tanggal Submit</th>
                                <th style="width: 190px;">Siswa</th>
                                <th style="width: 200px;">Materi</th>
                                <th style="width: 170px;">Tugas</th>
                                <th style="width: 90px;">Status</th>
                                <th style="width: 80px;">Nilai</th>
                                <th style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $item)
                                <tr data-id="{{ $item->jawaban_id }}">
                                    <td
                                        data-order="{{ $item->submitted_at ? \Carbon\Carbon::parse($item->submitted_at)->format('YmdHis') : 0 }}">
                                        @if ($item->submitted_at)
                                            {{ \Carbon\Carbon::parse($item->submitted_at)->format('d-m-Y H:i') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        {{ $item->siswa->nama_siswa ?? '-' }}
                                        @if ($item->siswa && $item->siswa->kelas)
                                            <br><small class="text-muted">{{ $item->siswa->kelas->nama_kelas }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $item->tugasPembekalan?->materi?->topik ?? '-' }}
                                        @if ($item->tugasPembekalan?->materi?->tanggal_materi)
                                            <br><small
                                                class="text-muted">{{ \Carbon\Carbon::parse($item->tugasPembekalan->materi->tanggal_materi)->format('d-m-Y') }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $item->tugasPembekalan?->judul_tugas ?? '-' }}</td>
                                    <td>
                                        @if ($item->status === 'selesai')
                                            <span class="badge badge-success">Selesai</span>
                                        @elseif ($item->status === 'proses')
                                            <span class="badge badge-warning text-white">Proses</span>
                                        @else
                                            <span class="badge badge-secondary">Belum</span>
                                        @endif
                                    </td>
                                    <td class="font-weight-bold text-center">{{ $item->nilaiTugas?->nilai !== null ? rtrim(rtrim(number_format((float) $item->nilaiTugas->nilai, 2, '.', ''), '0'), '.') : '-' }}</td>
                                    <td>
                                        @if ($item->jawaban_id)
                                            @php($bisaNilai = $canInputNilai && $item->submitted_at)
                                            <button type="button"
                                                class="btn btn-xs {{ $bisaNilai ? 'btn-primary' : 'btn-info' }} btn-buka"
                                                data-id="{{ $item->jawaban_id }}">
                                                <i class="fas {{ $bisaNilai ? 'fa-pen' : 'fa-eye' }} mr-1"></i>{{ $bisaNilai ? 'Nilai' : 'Lihat' }}
                                            </button>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                {{-- Empty state handled by DataTables language.emptyTable. --}}
                            @endforelse
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    @if ($isFiltered)
        <div class="modal fade" id="modalNilai" tabindex="-1" role="dialog" aria-labelledby="nTitle" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header py-2">
                        <div>
                            <h5 class="modal-title mb-0" id="nTitle">Detail Jawaban Siswa</h5>
                            <div class="small text-muted" id="nMeta"></div>
                        </div>
                        <div class="ml-auto d-flex align-items-center">
                            <span class="small text-muted mr-2" id="nCounter"></span>
                            <div class="btn-group btn-group-sm mr-2">
                                <button type="button" class="btn btn-outline-secondary" id="btnPrev"
                                    title="Siswa sebelumnya"><i class="fas fa-chevron-left"></i></button>
                                <button type="button" class="btn btn-outline-secondary" id="btnNext"
                                    title="Siswa berikutnya"><i class="fas fa-chevron-right"></i></button>
                            </div>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    </div>

                    <div class="modal-body nilai-body p-0">
                        <div class="row no-gutters h-100">
                            <div class="col-lg-5 nilai-col border-right">
                                <ul class="nav nav-tabs px-2 pt-2" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="tabSoalLink" data-toggle="tab" href="#paneSoal"
                                            role="tab">Soal</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="tabFileLink" data-toggle="tab" href="#paneFile"
                                            role="tab">File Soal <span class="badge badge-light" id="nFileCount"></span></a>
                                    </li>
                                </ul>
                                <div class="tab-content p-3">
                                    <div class="tab-pane fade show active" id="paneSoal" role="tabpanel">
                                        <div id="nSoal"></div>
                                    </div>
                                    <div class="tab-pane fade" id="paneFile" role="tabpanel">
                                        <div id="nFiles"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-7 nilai-col">
                                <div class="p-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <h6 class="mb-0 font-weight-bold">Jawaban Siswa</h6>
                                        <span class="ml-2 small text-muted" id="nSubmit"></span>
                                        <span class="ml-auto" id="nStatus"></span>
                                    </div>
                                    <div class="jawaban-rendered" id="nJawaban"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer nilai-footer d-block">
                        @if ($canInputNilai)
                            <div id="panelNilai">
                                <div class="form-row align-items-end">
                                    <div class="col-lg-5 mb-2">
                                        <label class="mb-1 small font-weight-bold" for="inNilai">Nilai (0 - 100)</label>
                                        <div class="d-flex align-items-center flex-wrap">
                                            <input type="number" id="inNilai" class="form-control nilai-input mr-2"
                                                min="0" max="100" step="0.01" inputmode="decimal"
                                                autocomplete="off">
                                            <div class="btn-group btn-group-sm flex-wrap" id="quickNilai">
                                                @foreach ([50, 60, 70, 75, 80, 85, 90, 100] as $q)
                                                    <button type="button" class="btn btn-outline-primary"
                                                        data-nilai="{{ $q }}">{{ $q }}</button>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-7 mb-2">
                                        <label class="mb-1 small font-weight-bold" for="inCatatan">Catatan Penilaian
                                            <span class="text-muted font-weight-normal">(opsional)</span></label>
                                        <input type="text" id="inCatatan" class="form-control form-control-sm"
                                            maxlength="1000" placeholder="Catatan untuk siswa" autocomplete="off">
                                        <div class="mt-1" id="quickCatatan">
                                            @foreach (['Bagus, pertahankan', 'Sudah sesuai', 'Jawaban kurang lengkap', 'Perlu diperdalam', 'Terlalu singkat', 'Kurang sesuai soal'] as $c)
                                                <button type="button" class="btn btn-xs btn-light border mr-1 mb-1"
                                                    data-catatan="{{ $c }}">{{ $c }}</button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center flex-wrap">
                                    <span class="small mr-3" id="nMsg"></span>
                                    <span class="small text-muted d-none d-md-inline">Tekan <kbd>Enter</kbd> untuk simpan
                                        &amp; lanjut</span>
                                    <div class="ml-auto">
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnSave">
                                            <i class="fas fa-save mr-1"></i>Simpan
                                        </button>
                                        <button type="button" class="btn btn-primary btn-sm" id="btnSaveNext">
                                            Simpan &amp; Berikutnya <i class="fas fa-arrow-right ml-1"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-secondary py-2 mb-0 d-none" id="panelBelumSubmit">
                                Jawaban belum disubmit, nilai belum bisa diinput.
                            </div>
                        @else
                            <div class="d-flex align-items-center">
                                <span class="mr-2">Nilai:</span>
                                <strong id="nNilaiView">-</strong>
                                <span class="ml-3 text-muted" id="nCatatanView"></span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <script type="application/json" id="detailData">{!! json_encode($detail, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>
        <script type="application/json" id="soalData">{!! json_encode($soalMap, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>
    @endif
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    <style>
        #modalNilai .modal-dialog {
            max-width: min(1280px, 96vw);
        }

        .nilai-body {
            height: calc(100vh - 290px);
            min-height: 320px;
            overflow: hidden;
        }

        .nilai-col {
            height: 100%;
            overflow-y: auto;
            background: #fff;
        }

        .nilai-col:first-child {
            background: #f8f9fb;
        }

        .nilai-footer {
            background: #f8f9fb;
            border-top: 2px solid #dee2e6;
        }

        .nilai-input {
            width: 120px;
            font-size: 1.6rem;
            font-weight: 700;
            text-align: center;
            height: calc(2.9rem + 2px);
        }

        .jawaban-rendered {
            font-size: 15px;
            line-height: 1.65;
            color: #1f2937;
            word-break: break-word;
        }

        .jawaban-rendered > div:first-child {
            margin-top: 0;
        }

        .jawaban-rendered p,
        .jawaban-rendered .jw-line,
        .jawaban-rendered blockquote {
            margin: 0 0 .5rem;
        }

        .jawaban-rendered .jw-heading {
            font-weight: 700;
            margin: .75rem 0 .35rem;
        }

        .jawaban-rendered .jw-plain {
            white-space: pre-wrap;
        }

        .jawaban-rendered blockquote {
            border-left: 3px solid #cbd5e1;
            padding-left: .75rem;
            color: #475569;
        }

        .jawaban-rendered ul,
        .jawaban-rendered ol {
            padding-left: 1.4rem;
            margin: 0 0 .5rem;
        }

        .jawaban-rendered li {
            margin-bottom: .35rem;
        }

        .jawaban-rendered table {
            width: 100%;
            border-collapse: collapse;
            margin: .5rem 0 .85rem;
            font-size: 14px;
        }

        .jawaban-rendered th,
        .jawaban-rendered td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            vertical-align: top;
        }

        .jawaban-rendered th {
            background: #f1f5f9;
        }

        .soal-item {
            display: flex;
            gap: .6rem;
            margin-bottom: .6rem;
        }

        .soal-no {
            flex: 0 0 1.6rem;
            height: 1.6rem;
            border-radius: 50%;
            background: #007bff;
            color: #fff;
            font-size: .8rem;
            font-weight: 700;
            text-align: center;
            line-height: 1.6rem;
        }

        .soal-blob {
            white-space: pre-wrap;
            font-size: 14px;
            line-height: 1.6;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: .65rem .75rem;
            margin-bottom: .6rem;
        }

        .soal-file-item {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: .75rem;
            margin-bottom: .85rem;
        }

        .soal-file-preview-img {
            display: block;
            width: 100%;
            max-height: 420px;
            object-fit: contain;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            background: #fff;
        }

        .soal-file-preview-pdf {
            width: 100%;
            height: 62vh;
            min-height: 320px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #fff;
        }

        @media (max-width: 991.98px) {
            .nilai-body {
                height: auto;
                max-height: calc(100vh - 330px);
                overflow-y: auto;
            }

            .nilai-col {
                height: auto;
                overflow: visible;
            }
        }
    </style>
@endsection

@section('plugins.Datatables', true)

@section('js')
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>

    @if ($isFiltered)
        <script>
            // Redirect to clean URL on browser reload so data doesn't auto-load
            if (performance.getEntriesByType('navigation')[0]?.type === 'reload') {
                window.location.replace('{{ route('pembekalan.jawaban-siswa') }}');
            }

            window.NILAI_CFG = {
                canInput: @json((bool) $canInputNilai),
                saveUrl: @json(url('pembekalan/jawaban-siswa')),
                token: @json(csrf_token())
            };
        </script>

        @verbatim
            <script>
                $(function() {
                    if (!$.fn.DataTable) {
                        console.error('DataTables library gagal dimuat.');
                        return;
                    }

                    const CFG = window.NILAI_CFG;
                    const DATA = JSON.parse(document.getElementById('detailData').textContent);
                    const SOAL = JSON.parse(document.getElementById('soalData').textContent);
                    const $modal = $('#modalNilai');
                    let current = null;
                    let order = [];
                    let soalKey = null;
                    let filesKey = null;
                    let saving = false;

                    if ($.fn.DataTable.isDataTable('#jawabanTable')) {
                        $('#jawabanTable').DataTable().destroy();
                    }

                    const table = $('#jawabanTable').DataTable({
                        pageLength: 10,
                        lengthChange: true,
                        ordering: true,
                        searching: true,
                        responsive: true,
                        autoWidth: false,
                        order: [
                            [0, 'desc']
                        ],
                        language: {
                            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
                            emptyTable: 'Belum ada jawaban tugas siswa.'
                        }
                    });

                    $modal.appendTo(document.body);

                    function isSubmitted(id) {
                        return !!(DATA[id] && DATA[id].submitted);
                    }

                    // Urutan siswa mengikuti urutan dan pencarian tabel yang sedang tampil.
                    function visibleIds() {
                        return table.rows({
                                order: 'current',
                                search: 'applied'
                            }).nodes().toArray()
                            .map(function(tr) {
                                return Number(tr.getAttribute('data-id'));
                            })
                            .filter(function(id) {
                                return CFG.canInput ? isSubmitted(id) : !!DATA[id];
                            });
                    }

                    function statusBadge(status) {
                        if (status === 'selesai') {
                            return '<span class="badge badge-success">Selesai</span>';
                        }
                        if (status === 'proses') {
                            return '<span class="badge badge-warning text-white">Proses</span>';
                        }
                        return '<span class="badge badge-secondary">Belum</span>';
                    }

                    function openFor(id) {
                        order = visibleIds();
                        if (order.indexOf(id) === -1) {
                            order.push(id);
                        }
                        show(id);
                        $modal.modal('show');
                    }

                    function isDirty() {
                        if (!CFG.canInput || current === null || !isSubmitted(current)) {
                            return false;
                        }
                        const d = DATA[current];
                        return String($('#inNilai').val()).trim() !== String(d.nilai) ||
                            String($('#inCatatan').val()) !== String(d.catatan);
                    }

                    function go(delta) {
                        const i = order.indexOf(current) + delta;
                        if (i < 0 || i >= order.length) {
                            return;
                        }
                        if (isDirty() && !window.confirm('Nilai/catatan belum disimpan. Pindah tanpa menyimpan?')) {
                            return;
                        }
                        show(order[i]);
                    }

                    function buildSoal(tid) {
                        const s = SOAL[tid] || {};
                        const $box = $('#nSoal').empty();
                        const essay = s.essay || [];
                        const prompts = s.prompts || [];

                        essay.forEach(function(t, i) {
                            $box.append(
                                $('<div class="soal-item">')
                                .append($('<span class="soal-no">').text(i + 1))
                                .append($('<div>').text(t))
                            );
                        });

                        prompts.forEach(function(t) {
                            if (essay.length) {
                                return;
                            }
                            $box.append($('<div class="soal-blob">').text(t));
                        });

                        if (!essay.length && !prompts.length) {
                            $box.append(
                                '<div class="text-muted">Belum ada teks soal tersimpan. Lihat tab <strong>File Soal</strong>.</div>'
                            );
                        }
                        $('#nFileCount').text((s.files || []).length || '');
                    }

                    function buildFiles(tid) {
                        const s = SOAL[tid] || {};
                        const files = s.files || [];
                        const $box = $('#nFiles').empty();

                        if (!files.length) {
                            $box.append('<div class="text-muted">Tidak ada file soal.</div>');
                            return;
                        }

                        files.forEach(function(f, i) {
                            const $item = $('<div class="soal-file-item">');
                            const $head = $('<div class="d-flex flex-wrap align-items-center justify-content-between mb-2">');
                            const $title = $('<div>')
                                .append($('<strong>').text('File ' + (i + 1)))
                                .append($('<div class="text-muted small">').text(f.name));
                            const $btns = $('<div>');

                            if (f.exists) {
                                $btns.append($('<a class="btn btn-xs btn-outline-primary mr-1" target="_blank" rel="noopener">')
                                    .attr('href', f.url).html('<i class="fas fa-external-link-alt mr-1"></i>Buka'));
                                $btns.append($('<a class="btn btn-xs btn-outline-secondary" download>')
                                    .attr('href', f.url).html('<i class="fas fa-download mr-1"></i>Unduh'));
                            }
                            $head.append($title).append($btns);
                            $item.append($head);

                            if (!f.exists) {
                                $item.append(
                                    '<div class="alert alert-warning small mb-0">File tidak ditemukan di server. ' +
                                    'Minta panitia mengunggah ulang file soal pada menu Tugas Pembekalan.</div>'
                                );
                            } else if (f.ext === 'pdf') {
                                $item.append($('<iframe class="soal-file-preview-pdf">')
                                    .attr('src', f.url + '#view=FitH').attr('title', 'File soal ' + (i + 1)));
                                $item.append(
                                    '<div class="small text-muted mt-1">PDF tidak tampil? Klik <strong>Buka</strong> atau <strong>Unduh</strong>.</div>'
                                );
                            } else if (['jpg', 'jpeg', 'png', 'webp'].indexOf(f.ext) !== -1) {
                                $item.append($('<img class="soal-file-preview-img" loading="lazy">')
                                    .attr('src', f.url).attr('alt', 'File soal ' + (i + 1)));
                            } else {
                                $item.append('<div class="text-muted small">Preview tidak tersedia untuk format ini.</div>');
                            }

                            $box.append($item);
                        });
                    }

                    function ensureFiles() {
                        if (filesKey !== soalKey && $('#tabFileLink').hasClass('active')) {
                            buildFiles(soalKey);
                            filesKey = soalKey;
                        }
                    }

                    function setMsg(text, cls) {
                        $('#nMsg').attr('class', 'small mr-3 ' + (cls || '')).text(text || '');
                    }

                    function show(id) {
                        current = id;
                        const d = DATA[id];
                        const pos = order.indexOf(id);

                        $('#nMeta').text([d.siswa, d.kelas, d.materi + ' - ' + d.tugas].filter(Boolean).join(' · '));
                        $('#nSubmit').text(d.submitted ? 'Dikirim ' + d.submitted : '');
                        $('#nStatus').html(statusBadge(d.status));
                        $('#nCounter').text((pos + 1) + ' / ' + order.length);
                        $('#btnPrev').prop('disabled', pos <= 0);
                        $('#btnNext').prop('disabled', pos === -1 || pos >= order.length - 1);

                        $('#nJawaban').html(d.html || '<div class="text-muted">Siswa belum menulis jawaban.</div>');
                        $('.nilai-col').scrollTop(0);

                        if (soalKey !== d.tugas_id) {
                            soalKey = d.tugas_id;
                            buildSoal(d.tugas_id);
                            filesKey = null;
                        }
                        ensureFiles();

                        if (CFG.canInput) {
                            const ok = !!d.submitted;
                            $('#panelNilai').toggleClass('d-none', !ok);
                            $('#panelBelumSubmit').toggleClass('d-none', ok);
                            $('#inNilai').val(d.nilai);
                            $('#inCatatan').val(d.catatan);
                            syncQuick();
                            setMsg(d.status === 'selesai' ? 'Sudah dinilai. Anda bisa mengubahnya.' : '', 'text-muted');
                            if (ok) {
                                setTimeout(function() {
                                    $('#inNilai').trigger('focus').trigger('select');
                                }, 150);
                            }
                        } else {
                            $('#nNilaiView').text(d.nilai === '' ? '-' : d.nilai);
                            $('#nCatatanView').text(d.catatan);
                        }
                    }

                    function syncQuick() {
                        const v = String($('#inNilai').val()).trim();
                        $('#quickNilai button').each(function() {
                            const on = v !== '' && Number(v) === Number($(this).data('nilai'));
                            $(this).toggleClass('active', on);
                        });
                    }

                    function updateRow(id) {
                        const d = DATA[id];
                        const tr = table.rows().nodes().toArray().find(function(n) {
                            return Number(n.getAttribute('data-id')) === id;
                        });
                        if (!tr) {
                            return;
                        }
                        table.cell(tr, 4).data(statusBadge(d.status));
                        table.cell(tr, 5).data(d.nilai === '' ? '-' : d.nilai);
                    }

                    function errorText(xhr) {
                        const j = xhr.responseJSON || {};
                        if (xhr.status === 419) {
                            return 'Sesi habis. Muat ulang halaman lalu coba lagi.';
                        }
                        if (j.errors) {
                            return Object.values(j.errors)[0][0];
                        }
                        return j.message || 'Gagal menyimpan (kode ' + xhr.status + ').';
                    }

                    function save(next) {
                        if (saving || current === null || !isSubmitted(current)) {
                            return;
                        }
                        const d = DATA[current];
                        const raw = String($('#inNilai').val()).trim().replace(',', '.');
                        const num = Number(raw);

                        if (raw === '' || isNaN(num) || num < 0 || num > 100) {
                            setMsg('Nilai harus berupa angka 0 - 100.', 'text-danger');
                            $('#inNilai').trigger('focus');
                            return;
                        }

                        saving = true;
                        $('#btnSave, #btnSaveNext').prop('disabled', true);
                        setMsg('Menyimpan...', 'text-muted');

                        $.ajax({
                            url: CFG.saveUrl + '/' + d.id + '/nilai',
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            data: {
                                _token: CFG.token,
                                nilai: raw,
                                catatan: $('#inCatatan').val()
                            }
                        }).done(function(res) {
                            d.nilai = res.nilai;
                            d.catatan = res.catatan || '';
                            d.status = 'selesai';
                            updateRow(d.id);
                            $('#nStatus').html(statusBadge('selesai'));

                            if (!next) {
                                setMsg('Tersimpan.', 'text-success');
                                return;
                            }

                            const pos = order.indexOf(d.id);
                            const target = order.slice(pos + 1).find(function(i) {
                                return DATA[i].status !== 'selesai';
                            });

                            if (target !== undefined) {
                                show(target);
                                setMsg('Nilai ' + d.siswa + ' tersimpan.', 'text-success');
                            } else {
                                setMsg('Tersimpan. Tidak ada lagi jawaban yang belum dinilai di bawah ini.', 'text-success');
                            }
                        }).fail(function(xhr) {
                            setMsg(errorText(xhr), 'text-danger');
                        }).always(function() {
                            saving = false;
                            $('#btnSave, #btnSaveNext').prop('disabled', false);
                        });
                    }

                    $(document).on('click', '.btn-buka', function() {
                        openFor(Number($(this).data('id')));
                    });

                    $('#btnMulaiNilai').on('click', function() {
                        const ids = visibleIds();
                        const first = ids.find(function(i) {
                            return DATA[i].status !== 'selesai';
                        });
                        if (first === undefined && !ids.length) {
                            return;
                        }
                        openFor(first !== undefined ? first : ids[0]);
                    });

                    $('#btnPrev').on('click', function() {
                        go(-1);
                    });
                    $('#btnNext').on('click', function() {
                        go(1);
                    });
                    $('#btnSave').on('click', function() {
                        save(false);
                    });
                    $('#btnSaveNext').on('click', function() {
                        save(true);
                    });

                    $('#quickNilai').on('click', 'button', function() {
                        $('#inNilai').val($(this).data('nilai')).trigger('focus');
                        syncQuick();
                    });

                    $('#quickCatatan').on('click', 'button', function() {
                        const cur = String($('#inCatatan').val()).trim();
                        const add = String($(this).data('catatan'));
                        const joined = cur === '' ? add : cur.replace(/[.\s]+$/, '') + '. ' + add;
                        $('#inCatatan').val(joined.slice(0, 1000)).trigger('focus');
                    });

                    $('#inNilai').on('input', syncQuick);
                    $('#inNilai, #inCatatan').on('keydown', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            save(true);
                        }
                    });

                    $('#tabFileLink').on('shown.bs.tab', ensureFiles);

                    $modal.on('hide.bs.modal', function(e) {
                        if (isDirty() && !window.confirm('Nilai/catatan belum disimpan. Tutup tanpa menyimpan?')) {
                            e.preventDefault();
                        }
                    });

                    $modal.on('hidden.bs.modal', function() {
                        $('#nFiles').empty();
                        filesKey = null;
                        current = null;
                    });
                });
            </script>
        @endverbatim
    @endif
@endsection
