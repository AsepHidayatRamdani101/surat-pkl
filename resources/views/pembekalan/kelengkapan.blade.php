@extends('adminlte::page')

@section('title', 'Cek Kelengkapan Siswa')

@php
    $appBaseUrl = request()->getBaseUrl();
    $kelengkapanPageUrl = $appBaseUrl . route('pembekalan.kelengkapan', [], false);
    $kelengkapanBulkStoreUrl = $appBaseUrl . route('pembekalan.kelengkapan.bulk-store', [], false);
    $kelengkapanInputStudentsUrl = $appBaseUrl . route('pembekalan.kelengkapan.input.students', [], false);
    $formulirAbsensiUrl = $appBaseUrl . route('pembekalan.absensi.formulir', [], false);
@endphp

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Cek Kelengkapan Siswa</h1>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid pt-3">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Input Kelengkapan Kelompok</h5>
                        <small class="text-muted">Pilih kelompok, tanggal, dan sesi untuk memeriksa item kelengkapan
                            siswa.</small>
                    </div>
                    <a href="{{ $formulirAbsensiUrl }}" class="btn btn-sm btn-outline-primary mt-2 mt-md-0">
                        <i class="fas fa-print mr-1"></i> Formulir Print
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if ($items->isEmpty())
                    <div class="alert alert-warning mb-0">
                        Daftar kelengkapan belum diinput oleh admin. Silakan tambahkan item melalui menu master kelengkapan
                        siswa.
                    </div>
                @else
                    <form id="filterKelengkapanForm" class="mb-3" onsubmit="return false;">
                        <div class="form-row align-items-end">
                            <div class="col-md-5 mb-2">
                                <label class="mb-1">Kelompok Bimbingan</label>
                                <select id="kelompokKelengkapanSelect" name="kelompok_id_input"
                                    class="form-control form-control-sm" required>
                                    <option value="">Pilih Kelompok</option>
                                    @foreach ($kelompokOptions as $kelompok)
                                        <option value="{{ $kelompok->id }}"
                                            {{ (string) $bulkInput['kelompok_id'] === (string) $kelompok->id ? 'selected' : '' }}>
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
                                <label class="mb-1">Tanggal Cek</label>
                                <input type="date" id="tanggalKelengkapanSelect" name="tanggal_cek_input"
                                    class="form-control form-control-sm" value="{{ $bulkInput['tanggal_cek'] }}" required>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="mb-1">Sesi</label>
                                <select id="sesiKelengkapanSelect" name="sesi_cek_input"
                                    class="form-control form-control-sm" required>
                                    <option value="datang"
                                        {{ ($bulkInput['sesi_cek'] ?? 'datang') === 'datang' ? 'selected' : '' }}>Datang
                                    </option>
                                    <option value="pulang"
                                        {{ ($bulkInput['sesi_cek'] ?? 'datang') === 'pulang' ? 'selected' : '' }}>Pulang
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <div class="alert alert-light border py-2 mb-0 text-muted text-center">Form tampil otomatis
                                </div>
                            </div>
                        </div>
                    </form>

                    <div id="kelengkapanInfo" class="alert alert-info py-2 mb-2 d-none"></div>
                    <div id="kelengkapanWarning" class="alert alert-warning py-2 mb-2 d-none"></div>
                    <div id="kelengkapanLoading" class="alert alert-secondary py-2 mb-2 d-none">
                        <span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
                        Memuat data siswa kelompok...
                    </div>

                    <form method="POST" action="{{ $kelengkapanBulkStoreUrl }}" id="bulkKelengkapanForm">
                        @csrf
                        <input type="hidden" name="kelompok_id" id="bulkKelengkapanKelompokId">
                        <input type="hidden" name="tanggal_cek" id="bulkTanggalCek">
                        <input type="hidden" name="sesi_cek" id="bulkSesiCek">

                        <div class="table-responsive">
                            <table id="inputKelengkapanTable" class="table table-bordered table-striped table-sm mb-2">
                                <thead>
                                    <tr>
                                        <th style="width: 44px;" class="text-center">
                                            <input type="checkbox" id="checkAllSiswaKelengkapan" title="Pilih semua siswa"
                                                checked>
                                        </th>
                                        <th style="width: 50px;">No</th>
                                        <th style="width: 260px;">Siswa</th>
                                        <th>Kelengkapan</th>
                                        <th style="width: 240px;">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody id="kelengkapanStudentList"></tbody>
                            </table>
                        </div>

                        <button type="submit" id="submitBulkKelengkapanBtn" class="btn btn-sm btn-success d-none">
                            Simpan Cek Kelengkapan
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body py-3">
                <form id="filterForm" method="GET" action="{{ $kelengkapanPageUrl }}">
                    <div class="form-row align-items-end">
                        <div class="col-md-2 mb-2">
                            <label class="mb-1">Dari</label>
                            <input type="date" name="tanggal_awal" class="form-control form-control-sm filter-input"
                                value="{{ $filters['tanggal_awal'] }}">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="mb-1">Sampai</label>
                            <input type="date" name="tanggal_akhir" class="form-control form-control-sm filter-input"
                                value="{{ $filters['tanggal_akhir'] }}">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="mb-1">Pembimbing</label>
                            <select name="pembimbing_id" class="form-control form-control-sm filter-input"
                                {{ $isPembimbingOnly ? 'disabled' : '' }}>
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
                            <label class="mb-1">Kelompok</label>
                            <select name="kelompok_id" class="form-control form-control-sm filter-input">
                                <option value="">Semua Kelompok</option>
                                @foreach ($kelompokOptions as $kelompok)
                                    <option value="{{ $kelompok->id }}"
                                        {{ (string) $filters['kelompok_id'] === (string) $kelompok->id ? 'selected' : '' }}>
                                        {{ $kelompok->nama_kelompok }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2 d-flex">
                            <button type="submit" class="btn btn-sm btn-primary mr-1 w-100">Filter</button>
                            <a href="{{ $kelengkapanPageUrl }}" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
                        </div>
                    </div>
                    <div class="form-row mt-1">
                        <div class="col-md-2 mb-2">
                            <label class="mb-1">Sesi</label>
                            <select name="sesi_cek" class="form-control form-control-sm filter-input">
                                <option value="">Semua Sesi</option>
                                <option value="datang" {{ $filters['sesi_cek'] === 'datang' ? 'selected' : '' }}>Datang
                                </option>
                                <option value="pulang" {{ $filters['sesi_cek'] === 'pulang' ? 'selected' : '' }}>Pulang
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="mb-1">Status</label>
                            <select name="status_kelengkapan" class="form-control form-control-sm filter-input">
                                <option value="">Semua Status</option>
                                <option value="lengkap"
                                    {{ $filters['status_kelengkapan'] === 'lengkap' ? 'selected' : '' }}>Lengkap</option>
                                <option value="belum_lengkap"
                                    {{ $filters['status_kelengkapan'] === 'belum_lengkap' ? 'selected' : '' }}>Belum
                                    Lengkap</option>
                            </select>
                        </div>
                        <div class="col-md-8 mb-2">
                            <label class="mb-1">Cari Data</label>
                            <input type="text" name="keyword" class="form-control form-control-sm filter-input"
                                placeholder="Cari siswa, pembimbing, atau catatan" value="{{ $filters['keyword'] }}">
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Dashboard Cards -->
        <div class="row mb-3">
            <div class="col-md-6 mb-2">
                <div class="small-box bg-success shadow-sm" id="card-siswa-lengkap">
                    <div class="inner">
                        <h3 id="count-siswa-lengkap">0</h3>
                        <p>Siswa Lengkap</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <a href="#" id="link-siswa-lengkap" class="small-box-footer">
                        Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-md-6 mb-2">
                <div class="small-box bg-danger shadow-sm" id="card-siswa-belum-lengkap">
                    <div class="inner">
                        <h3 id="count-siswa-belum-lengkap">0</h3>
                        <p>Siswa Belum Lengkap</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <a href="#" id="link-siswa-belum-lengkap" class="small-box-footer">
                        Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-md-6 mb-2">
                <div class="small-box bg-info shadow-sm" id="card-guru-terinput">
                    <div class="inner">
                        <h3 id="count-guru-terinput">0</h3>
                        <p>Guru yang Sudah Input</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <a href="#" id="link-guru-terinput" class="small-box-footer">
                        Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-md-6 mb-2">
                <div class="small-box bg-warning shadow-sm" id="card-siswa-terinput">
                    <div class="inner">
                        <h3 id="count-siswa-terinput">0</h3>
                        <p>Siswa yang Sudah Terinput</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <a href="#" id="link-siswa-terinput" class="small-box-footer">
                        Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex align-items-center">
                <div>
                    <h5 class="mb-0">Riwayat Cek Kelengkapan</h5>
                    <small class="text-muted">{{ $records->count() }} data</small>
                </div>
            </div>
            <div class="card-body table-responsive">
                <table id="kelengkapanTable" class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th style="width: 95px;">Tanggal</th>
                            <th style="width: 90px;">Sesi</th>
                            <th style="width: 220px;">Siswa</th>
                            <th style="width: 200px;">Pembimbing</th>
                            <th style="width: 120px;">Status</th>
                            <th>Item Belum Lengkap</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $item)
                            @php
                                $uncheckedItems = collect($item->item_checks ?? [])->filter(
                                    fn($check) => empty($check['is_checked']),
                                );
                            @endphp
                            <tr>
                                <td>{{ optional($item->tanggal_cek)->format('d-m-Y') }}</td>
                                <td><span class="badge badge-light border">{{ ucfirst($item->sesi_cek) }}</span></td>
                                <td>
                                    {{ $item->siswa->nama_siswa ?? '-' }}
                                    @if ($item->siswa && $item->siswa->kelas)
                                        <br><small class="text-muted">{{ $item->siswa->kelas->nama_kelas }}</small>
                                    @endif
                                </td>
                                <td>{{ $item->pembimbing->nama_pembimbing ?? '-' }}</td>
                                <td>
                                    <span class="badge badge-{{ $item->is_lengkap ? 'success' : 'danger' }}">
                                        {{ $item->is_lengkap ? 'Lengkap' : 'Belum Lengkap' }}
                                    </span>
                                </td>
                                <td>
                                    @if ($uncheckedItems->isEmpty())
                                        <span class="text-success">Semua item lengkap</span>
                                    @else
                                        {{ $uncheckedItems->pluck('nama_item')->join(', ') }}
                                    @endif
                                </td>
                                <td>{{ $item->catatan ?: '-' }}</td>
                            </tr>
                        @empty
                            {{-- Empty state handled by DataTables language.emptyTable. --}}
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('plugins.Datatables', true)

@section('css')
    <style>
        .kelengkapan-checklist {
            display: grid;
            gap: 0.5rem;
        }

        .kelengkapan-check-item {
            border: 1px solid #edf2f7;
            border-radius: 10px;
            padding: 0.5rem 0.65rem;
            background: #f8fafc;
        }

        .kelengkapan-check-row {
            display: grid;
            grid-template-columns: minmax(120px, 1fr) 150px;
            gap: 0.75rem;
            align-items: center;
        }

        .kelengkapan-check-label {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0;
        }

        @media (max-width: 576px) {
            .kelengkapan-check-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function() {
            const successMessage = @json(session('success'));
            const errorMessages = @json($errors->all());

            if (successMessage) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: successMessage,
                    timer: 1800,
                    showConfirmButton: false
                });
            }

            if (errorMessages.length > 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal menyimpan data',
                    html: errorMessages.join('<br>'),
                });
            }

            if ($.fn.DataTable) {
                $('#kelengkapanTable').DataTable({
                    pageLength: 10,
                    responsive: true,
                    autoWidth: false,
                    ordering: true,
                    order: [
                        [0, 'desc']
                    ],
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
                        emptyTable: 'Belum ada data cek kelengkapan siswa.'
                    }
                });
            }

            const endpoint = @json($kelengkapanInputStudentsUrl);
            const $kelompok = $('#kelompokKelengkapanSelect');
            const $tanggal = $('#tanggalKelengkapanSelect');
            const $sesi = $('#sesiKelengkapanSelect');
            const $list = $('#kelengkapanStudentList');
            const $loading = $('#kelengkapanLoading');
            const $info = $('#kelengkapanInfo');
            const $warning = $('#kelengkapanWarning');
            const $submitBtn = $('#submitBulkKelengkapanBtn');
            const $bulkKelompok = $('#bulkKelengkapanKelompokId');
            const $bulkTanggal = $('#bulkTanggalCek');
            const $bulkSesi = $('#bulkSesiCek');
            const $checkAll = $('#checkAllSiswaKelengkapan');

            const setupInputTable = (emptyTableMessage =
                'Pilih kelompok bimbingan untuk menampilkan data siswa.') => {
                if (!$.fn.DataTable) {
                    return;
                }

                $('#inputKelengkapanTable').DataTable({
                    pageLength: 10,
                    lengthChange: true,
                    ordering: true,
                    searching: true,
                    responsive: true,
                    autoWidth: false,
                    order: [
                        [0, 'asc']
                    ],
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
                        emptyTable: emptyTableMessage
                    }
                });
            };

            const setLoading = (isLoading) => {
                $kelompok.prop('disabled', isLoading);
                $tanggal.prop('disabled', isLoading);
                $sesi.prop('disabled', isLoading);
                $submitBtn.prop('disabled', isLoading);
                $checkAll.prop('disabled', isLoading);
                $loading.toggleClass('d-none', !isLoading);
            };

            const updateSubmitState = () => {
                const selectedCount = $('#inputKelengkapanTable .siswa-checkbox:checked').length;
                $submitBtn.toggleClass('d-none', selectedCount === 0);
                $submitBtn.prop('disabled', selectedCount === 0 || !$bulkKelompok.val());
            };

            const syncCheckAllState = () => {
                const totalCheckbox = $('#inputKelengkapanTable .siswa-checkbox').length;
                const checkedCheckbox = $('#inputKelengkapanTable .siswa-checkbox:checked').length;
                $checkAll.prop('checked', totalCheckbox > 0 && totalCheckbox === checkedCheckbox);
            };

            const clearInfo = () => {
                $info.addClass('d-none').text('');
            };

            const showInfo = (message) => {
                $info.removeClass('d-none').html(message);
            };

            const clearWarning = () => {
                $warning.addClass('d-none').text('');
            };

            const showWarning = (message) => {
                $warning.removeClass('d-none').text(message);
            };

            const escapeHtml = (value) => String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');

            const resetFormArea = () => {
                if ($.fn.DataTable && $.fn.DataTable.isDataTable('#inputKelengkapanTable')) {
                    $('#inputKelengkapanTable').DataTable().destroy();
                }

                $list.empty();
                $bulkKelompok.val('');
                $bulkTanggal.val('');
                $bulkSesi.val('');
                $submitBtn.addClass('d-none');
                $checkAll.prop('checked', true);
                clearInfo();
            };

            const buildStudentRows = (items, students) => {
                const rows = students.map((student, index) => {
                    const siswaId = Number(student.siswa_id);
                    const checkedIds = Array.isArray(student.checked_item_ids) ? student
                        .checked_item_ids.map(Number) : [];
                    const hasRecord = Boolean(student.has_record);
                    const itemNodes = items.map((item) => {
                        const inputId = `kelengkapan-${siswaId}-${item.id}`;
                        const checked = hasRecord ? checkedIds.includes(Number(item.id)) : true;
                        const deskripsi = item.deskripsi ?
                            `<small class="text-muted d-block">${escapeHtml(item.deskripsi)}</small>` :
                            '';

                        return `
                            <div class="kelengkapan-check-item">
                                <div class="kelengkapan-check-row">
                                    <div>
                                        <label class="kelengkapan-check-label" for="${inputId}">${escapeHtml(item.nama_item)}</label>
                                        ${deskripsi}
                                    </div>
                                    <select id="${inputId}" name="item_checks[${siswaId}][${item.id}]" class="form-control form-control-sm">
                                        <option value="1" ${checked ? 'selected' : ''}>Sesuai</option>
                                        <option value="0" ${checked ? '' : 'selected'}>Tidak Sesuai</option>
                                    </select>
                                </div>
                            </div>
                        `;
                    }).join('');

                    const kelasName = student.kelas ?
                        `<br><small class="text-muted">${escapeHtml(student.kelas)}</small>` : '';

                    return `
                        <tr>
                            <td class="text-center align-middle">
                                <input type="checkbox" class="siswa-checkbox" name="siswa_ids[]" value="${siswaId}" checked>
                            </td>
                            <td>
                                ${index + 1}
                            </td>
                            <td>
                                <strong>${escapeHtml(student.nama_siswa || '-')}</strong>
                                ${kelasName}
                            </td>
                            <td>
                                <div class="kelengkapan-checklist">${itemNodes}</div>
                            </td>
                            <td>
                                <textarea name="catatans[${siswaId}]" rows="3" class="form-control form-control-sm" placeholder="Catatan pemeriksaan (opsional)">${escapeHtml(student.catatan || '')}</textarea>
                            </td>
                        </tr>
                    `;
                });

                $list.html(rows.join(''));
                $checkAll.prop('checked', students.length > 0);
                updateSubmitState();
            };

            const loadStudents = () => {
                clearWarning();

                const kelompokId = $kelompok.val();
                const tanggalCek = $tanggal.val();
                const sesiCek = $sesi.val();

                if (!kelompokId || !tanggalCek || !sesiCek) {
                    resetFormArea();
                    return;
                }

                setLoading(true);

                $.ajax({
                    url: endpoint,
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        kelompok_id: kelompokId,
                        tanggal_cek: tanggalCek,
                        sesi_cek: sesiCek,
                    },
                    success: function(response) {
                        const items = Array.isArray(response.items) ? response.items : [];
                        const students = Array.isArray(response.students) ? response.students : [];

                        if (items.length === 0) {
                            resetFormArea();
                            showWarning(response.message || 'Daftar kelengkapan belum tersedia.');
                            return;
                        }

                        if ($.fn.DataTable && $.fn.DataTable.isDataTable(
                                '#inputKelengkapanTable')) {
                            $('#inputKelengkapanTable').DataTable().destroy();
                        }

                        $bulkKelompok.val(response.kelompok.id);
                        $bulkTanggal.val(response.tanggal_cek);
                        $bulkSesi.val(response.sesi_cek);

                        buildStudentRows(items, students);
                        setupInputTable('Belum ada siswa pada kelompok ini.');
                        showInfo(
                            `Kelompok: ${escapeHtml(response.kelompok.nama_kelompok)}${response.kelompok.pembimbing ? ' - Pembimbing: ' + escapeHtml(response.kelompok.pembimbing) : ''}`
                        );
                    },
                    error: function(xhr) {
                        resetFormArea();
                        const message = xhr.responseJSON && xhr.responseJSON.message ?
                            xhr.responseJSON.message :
                            'Gagal memuat data siswa pada kelompok terpilih.';
                        showWarning(message);
                    },
                    complete: function() {
                        setLoading(false);
                    }
                });
            };

            $('#filterKelengkapanForm select, #filterKelengkapanForm input').on('change', loadStudents);

            $(document).on('change', '#checkAllSiswaKelengkapan', function() {
                const isChecked = $(this).is(':checked');
                $('#inputKelengkapanTable .siswa-checkbox').prop('checked', isChecked);
                updateSubmitState();
            });

            $(document).on('change', '#inputKelengkapanTable .siswa-checkbox', function() {
                syncCheckAllState();
                updateSubmitState();
            });

            $('#bulkKelengkapanForm').on('submit', function(event) {
                if ($('#inputKelengkapanTable .siswa-checkbox:checked').length === 0) {
                    event.preventDefault();
                    showWarning('Pilih minimal satu siswa untuk menyimpan cek kelengkapan.');
                }
            });

            resetFormArea();
            setupInputTable();

            if ($kelompok.length && $kelompok.val()) {
                loadStudents();
            }

            // Dynamic Dashboard Cards
            const dashboardCardApiUrl = @json(route('api.kelengkapan.dashboard-cards'));
            let updateDashboardCardsTimeout;

            const getFilterQueryString = () => {
                const formData = new FormData(document.getElementById('filterForm'));
                const params = new URLSearchParams(formData);
                return params.toString();
            };

            const updateCardValue = (elementId, newValue) => {
                const $element = document.getElementById(elementId);
                if ($element) {
                    const oldValue = $element.textContent;
                    $element.textContent = newValue;
                    if (oldValue !== String(newValue)) {
                        $element.style.backgroundColor = '#ffc107';
                        setTimeout(() => {
                            $element.style.backgroundColor = '';
                        }, 500);
                    }
                }
            };

            const updateDetailLinks = () => {
                const queryString = getFilterQueryString();
                const baseUrl = @json(route('pembekalan.kelengkapan'));

                document.getElementById('link-siswa-lengkap').href = baseUrl + '?' + queryString +
                    '&status_kelengkapan=lengkap';
                document.getElementById('link-siswa-belum-lengkap').href = baseUrl + '?' + queryString +
                    '&status_kelengkapan=belum_lengkap';
                document.getElementById('link-guru-terinput').href = baseUrl + '?' + queryString;
                document.getElementById('link-siswa-terinput').href = baseUrl + '?' + queryString;
            };

            const updateDashboardCards = () => {
                clearTimeout(updateDashboardCardsTimeout);
                updateDashboardCardsTimeout = setTimeout(() => {
                    const queryString = getFilterQueryString();
                    fetch(`${dashboardCardApiUrl}?${queryString}`)
                        .then(response => response.json())
                        .then(data => {
                            updateCardValue('count-siswa-lengkap', data.siswa_lengkap || 0);
                            updateCardValue('count-siswa-belum-lengkap', data.siswa_belum_lengkap ||
                                0);
                            updateCardValue('count-guru-terinput', data.guru_sudah_input || 0);
                            updateCardValue('count-siswa-terinput', data.siswa_terinput || 0);
                            updateDetailLinks();
                        })
                        .catch(error => console.error('Error fetching dashboard cards:', error));
                }, 500);
            };

            // Add event listeners to filter inputs
            document.querySelectorAll('.filter-input').forEach(input => {
                input.addEventListener('change', updateDashboardCards);
                input.addEventListener('input', updateDashboardCards);
            });

            // Initial update on page load
            updateDashboardCards();
        });
    </script>
@endsection
