@extends('adminlte::page')

@section('title', 'Data Absensi Pembekalan')

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Data Absensi Pembekalan</h1>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid pt-3">
        @if ($showInputSection && $canManageAbsensi)
            <div class="card shadow-sm border-0 mb-3" id="input-absensi">
                <div class="card-header bg-white">
                    <div class="d-flex flex-wrap justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Input Absensi Kelompok (Multiple)</h5>
                            <small class="text-muted">Pilih kelompok, tabel siswa akan muncul otomatis dalam halaman
                                ini.</small>
                        </div>
                        <a href="{{ route('pembekalan.absensi.formulir') }}"
                            class="btn btn-sm btn-outline-primary mt-2 mt-md-0">
                            <i class="fas fa-print mr-1"></i> Formulir Print
                        </a>
                        <a href="{{ route('pembekalan.pembinaan') }}"
                            class="btn btn-sm btn-outline-warning mt-2 mt-md-0 ml-md-2">
                            <i class="fas fa-user-shield mr-1"></i> Pembinaan
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form id="filterInputAbsensiForm" class="mb-3" onsubmit="return false;">
                        <div class="form-row align-items-end">
                            <div class="col-md-5 mb-2">
                                <label class="mb-1">Kelompok Bimbingan</label>
                                <select id="kelompokInputSelect" name="kelompok_id_input"
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
                                <label class="mb-1">Tanggal Absensi</label>
                                <input type="date" id="tanggalInputSelect" name="tanggal_absensi_input"
                                    class="form-control form-control-sm" value="{{ $bulkInput['tanggal_absensi'] }}"
                                    required>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="mb-1">Sesi</label>
                                <select id="sesiInputSelect" name="sesi_absensi_input" class="form-control form-control-sm"
                                    required>
                                    <option value="datang"
                                        {{ ($bulkInput['sesi_absensi'] ?? 'datang') === 'datang' ? 'selected' : '' }}>Datang
                                    </option>
                                    <option value="pulang"
                                        {{ ($bulkInput['sesi_absensi'] ?? 'datang') === 'pulang' ? 'selected' : '' }}>Pulang
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <div class="alert alert-light border py-2 mb-0 text-muted text-center">
                                    Tabel tampil otomatis
                                </div>
                            </div>
                        </div>
                    </form>

                    <div id="inputAbsensiInfo" class="alert alert-info py-2 mb-2 d-none"></div>
                    <div id="inputAbsensiWarning" class="alert alert-warning py-2 mb-2 d-none"></div>
                    <div id="inputAbsensiLoading" class="alert alert-secondary py-2 mb-2 d-none">
                        <span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
                        Memuat data siswa kelompok...
                    </div>

                    <form method="POST" action="{{ route('pembekalan.absensi.bulk-store') }}" id="bulkAbsensiForm">
                        @csrf
                        <input type="hidden" name="kelompok_id" id="bulkKelompokId">
                        <input type="hidden" name="tanggal_absensi" id="bulkTanggalAbsensi">
                        <input type="hidden" name="sesi_absensi" id="bulkSesiAbsensi">

                        <div class="table-responsive">
                            <table id="inputAbsensiTable" class="table table-bordered table-striped table-sm mb-2">
                                <thead>
                                    <tr>
                                        <th style="width: 44px;" class="text-center">
                                            <input type="checkbox" id="checkAllSiswaAbsensi" title="Pilih semua siswa">
                                        </th>
                                        <th style="width: 50px;">No</th>
                                        <th style="width: 260px;">Siswa</th>
                                        <th style="width: 140px;">Status</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody id="inputAbsensiBody"></tbody>
                            </table>
                        </div>

                        <button type="submit" id="submitBulkAbsensiBtn" class="btn btn-sm btn-success" disabled>
                            Simpan Absensi Kelompok
                        </button>
                    </form>
                </div>
            </div>
        @endif

        @if ($showRiwayatSection)
            <div class="card shadow-sm border-0 mb-3" id="lihat-absensi">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-end mb-2">
                        <a href="{{ route('pembekalan.absensi.formulir') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-print mr-1"></i> Formulir Print
                        </a>
                        <a href="{{ route('pembekalan.pembinaan') }}" class="btn btn-sm btn-outline-warning ml-2">
                            <i class="fas fa-user-shield mr-1"></i> Pembinaan
                        </a>
                    </div>
                    <form method="GET" action="{{ route('pembekalan.absensi.riwayat') }}" id="filterForm"
                        class="filter-form">
                        <div class="form-row align-items-end">
                            <div class="col-md-3 mb-2">
                                <label class="mb-1">Dari</label>
                                <input type="date" name="tanggal_awal" class="form-control form-control-sm filter-input"
                                    value="{{ $filters['tanggal_awal'] }}">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="mb-1">Sampai</label>
                                <input type="date" name="tanggal_akhir"
                                    class="form-control form-control-sm filter-input"
                                    value="{{ $filters['tanggal_akhir'] }}">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="mb-1">Pembimbing</label>
                                <select name="pembimbing_id" class="form-control form-control-sm filter-input">
                                    <option value="">Semua Pembimbing</option>
                                    @foreach ($pembimbingOptions as $pembimbing)
                                        <option value="{{ $pembimbing->id }}"
                                            {{ (string) $filters['pembimbing_id'] === (string) $pembimbing->id ? 'selected' : '' }}>
                                            {{ $pembimbing->nama_pembimbing }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-2 d-flex">
                                <button type="submit" class="btn btn-sm btn-primary mr-1 w-100">Filter</button>
                                <a href="{{ route('pembekalan.absensi.riwayat') }}"
                                    class="btn btn-sm btn-outline-secondary w-100">Reset</a>
                            </div>
                        </div>

                        <div class="form-row mt-1">
                            <div class="col-md-3 mb-2">
                                <label class="mb-1">Status</label>
                                <select name="status" class="form-control form-control-sm filter-input">
                                    <option value="">Semua Status</option>
                                    <option value="hadir" {{ $filters['status'] === 'hadir' ? 'selected' : '' }}>Hadir
                                    </option>
                                    <option value="izin" {{ $filters['status'] === 'izin' ? 'selected' : '' }}>Izin
                                    </option>
                                    <option value="alpa" {{ $filters['status'] === 'alpa' ? 'selected' : '' }}>Alpa
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="mb-1">Sesi</label>
                                <select name="sesi_absensi" class="form-control form-control-sm filter-input">
                                    <option value="">Semua Sesi</option>
                                    <option value="datang" {{ $filters['sesi_absensi'] === 'datang' ? 'selected' : '' }}>
                                        Datang</option>
                                    <option value="pulang" {{ $filters['sesi_absensi'] === 'pulang' ? 'selected' : '' }}>
                                        Pulang</option>
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
                            <div class="col-md-3">
                                <label class="mb-1">Cari Data</label>
                                <input type="text" name="keyword" class="form-control form-control-sm filter-input"
                                    placeholder="Cari siswa, pembimbing, atau keterangan"
                                    value="{{ $filters['keyword'] }}">
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Dashboard Cards --}}
            <div class="row pt-2">
                {{-- Siswa Telah Diabsen --}}
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary h-100">
                        <div class="inner">
                            <h3 id="card-siswa-telah-diabsen">{{ $siswaAbsenCount }}</h3>
                            <p>Siswa Telah Diabsen</p>
                            <a href="{{ route('pembekalan.absensi.detail', array_merge(['type' => 'siswa_absen'], array_filter($filters, fn($v) => !empty($v)))) }}"
                                class="btn btn-block btn-primary" id="link-siswa-telah-diabsen">Lihat Detail</a>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>

                {{-- Guru Telah Mengabsen --}}
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success h-100">
                        <div class="inner">
                            <h3 id="card-guru-telah-mengabsen">{{ $pembimbingAbsenCount }}</h3>
                            <p>Guru Telah Mengabsen</p>
                            <a href="{{ route('pembekalan.absensi.detail', array_merge(['type' => 'guru_absen'], array_filter($filters, fn($v) => !empty($v)))) }}"
                                class="btn btn-block btn-primary" id="link-guru-telah-mengabsen">Lihat Detail</a>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                </div>

                {{-- Siswa Belum Diabsen --}}
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning h-100">
                        <div class="inner">
                            <h3 id="card-siswa-belum-diabsen">{{ $siswaBelumAbs }}</h3>
                            <p>Siswa Belum Diabsen</p>
                            <a href="{{ route('pembekalan.absensi.detail', array_merge(['type' => 'siswa_belum'], array_filter($filters, fn($v) => !empty($v)))) }}"
                                class="btn btn-block btn-primary" id="link-siswa-belum-diabsen">Lihat Detail</a>
                        </div>
                        <div class="icon">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                    </div>
                </div>

                {{-- Guru Belum Mengabsen --}}
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger h-100">
                        <div class="inner">
                            <h3 id="card-guru-belum-mengabsen">{{ $pembimbingBelumAbs }}</h3>
                            <p>Guru Belum Mengabsen</p>
                            <a href="{{ route('pembekalan.absensi.detail', array_merge(['type' => 'guru_belum'], array_filter($filters, fn($v) => !empty($v)))) }}"
                                class="btn btn-block btn-primary" id="link-guru-belum-mengabsen">Lihat Detail</a>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-times"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0">Riwayat Absensi Pembekalan</h5>
                        <small class="text-muted">{{ $absensi->count() }} data</small>
                    </div>
                    <div>
                        <button type="button" id="deleteSelectedBtn" class="btn btn-sm btn-danger d-none">
                            <i class="fas fa-trash mr-1"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
                        </button>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <form id="bulkDeleteForm" method="POST" action="{{ route('pembekalan.absensi.bulk-delete') }}"
                        style="display: none;">
                        @csrf
                        @method('POST')
                        <input type="hidden" name="ids" id="bulkDeleteIds">
                    </form>
                    <table id="absensiTable" class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" id="selectAllCheckbox" title="Pilih semua">
                                </th>
                                <th style="width: 95px;">Tanggal</th>
                                <th style="width: 220px;">Siswa</th>
                                <th style="width: 180px;">Kelompok</th>
                                <th style="width: 220px;">Pembimbing</th>
                                <th style="width: 90px;">Sesi</th>
                                <th style="width: 100px;">Status</th>
                                <th style="width: 120px;">Atribut</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($absensi as $item)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="rowCheckbox" value="{{ $item->id }}">
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($item->tanggal_absensi)->format('d-m-Y') }}</td>
                                    <td>
                                        {{ $item->siswa->nama_siswa ?? '-' }}
                                        @if ($item->siswa && $item->siswa->kelas)
                                            <br><small class="text-muted">{{ $item->siswa->kelas->nama_kelas }}</small>
                                        @endif
                                    </td>
                                    <td>{{ optional(optional($item->siswa)->kelompokBimbingan)->pluck('nama_kelompok')->first() ?? '-' }}
                                    </td>
                                    <td>{{ $item->pembimbing->nama_pembimbing ?? '-' }}</td>
                                    <td><span
                                            class="badge badge-light border">{{ ucfirst($item->sesi_absensi ?? 'datang') }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $badge =
                                                [
                                                    'hadir' => 'success',
                                                    'izin' => 'warning',
                                                    'alpa' => 'danger',
                                                ][$item->status] ?? 'secondary';
                                        @endphp
                                        <span
                                            class="badge badge-{{ $badge }}">{{ strtoupper($item->status) }}</span>
                                    </td>
                                    <td>
                                        @if ($item->atribut_lengkap === null)
                                            <span class="badge badge-secondary">Belum Dicek</span>
                                        @elseif ($item->atribut_lengkap)
                                            <span class="badge badge-success">Lengkap</span>
                                        @else
                                            <span class="badge badge-danger">Tidak Lengkap</span>
                                        @endif
                                    </td>
                                    <td>{{ \Illuminate\Support\Str::limit($item->keterangan ?? '-', 180) }}</td>
                                </tr>
                            @empty
                                {{-- Empty state handled by DataTables language.emptyTable. --}}
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('plugins.Datatables', true)

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
                    title: 'Gagal menyimpan absensi',
                    html: errorMessages.join('<br>'),
                });
            }

            if (!$.fn.DataTable) {
                console.error('DataTables library gagal dimuat.');
                return;
            }

            // Dynamic Dashboard Cards Update
            let updateCardsTimeout;
            const filterInputs = document.querySelectorAll('.filter-input');
            const cardsUpdateApiUrl = @json(route('api.absensi.dashboard-cards'));

            function getFilterQueryString() {
                // Use riwayat filter form if available, otherwise use input filter form
                const filterForm = document.getElementById('filterForm') || document.getElementById(
                    'filterInputAbsensiForm');
                if (!filterForm) return '';
                const formData = new FormData(filterForm);
                const params = new URLSearchParams(formData);
                return params.toString();
            }

            function updateDetailLinks() {
                // Use riwayat filter form if available, otherwise use input filter form
                const filterForm = document.getElementById('filterForm') || document.getElementById(
                    'filterInputAbsensiForm');
                if (!filterForm) return;
                const formData = new FormData(filterForm);
                const params = new URLSearchParams(formData);
                const baseUrl = @json(route('pembekalan.absensi.detail'));

                const detailLinkConfigs = [{
                        linkId: 'link-siswa-telah-diabsen',
                        type: 'siswa_absen'
                    },
                    {
                        linkId: 'link-guru-telah-mengabsen',
                        type: 'guru_absen'
                    },
                    {
                        linkId: 'link-siswa-belum-diabsen',
                        type: 'siswa_belum'
                    },
                    {
                        linkId: 'link-guru-belum-mengabsen',
                        type: 'guru_belum'
                    }
                ];

                detailLinkConfigs.forEach(config => {
                    const link = document.getElementById(config.linkId);
                    if (link) {
                        // Create a new URLSearchParams with current filters
                        const detailParams = new URLSearchParams(params);
                        detailParams.set('type', config.type);

                        // Build complete URL
                        const fullUrl = `${baseUrl}?${detailParams.toString()}`;
                        link.href = fullUrl;
                    }
                });
            }

            function updateDashboardCards() {
                clearTimeout(updateCardsTimeout);
                updateCardsTimeout = setTimeout(function() {
                    const params = getFilterQueryString();

                    fetch(`${cardsUpdateApiUrl}?${params}`)
                        .then(response => response.json())
                        .then(data => {
                            // Update card values with animation
                            updateCardValue('card-siswa-telah-diabsen', data.siswa_telah_diabsen);
                            updateCardValue('card-guru-telah-mengabsen', data.guru_telah_mengabsen);
                            updateCardValue('card-siswa-belum-diabsen', data.siswa_belum_diabsen);
                            updateCardValue('card-guru-belum-mengabsen', data.guru_belum_mengabsen);
                            // Update detail links with new filter parameters
                            updateDetailLinks();
                        })
                        .catch(error => console.error('Error updating cards:', error));
                }, 500); // Debounce 500ms
            }

            function updateCardValue(elementId, newValue) {
                const element = document.getElementById(elementId);
                if (element) {
                    const currentValue = parseInt(element.textContent);
                    if (currentValue !== newValue) {
                        element.textContent = newValue;
                        // Add a subtle pulse animation
                        element.style.transition = 'color 0.3s ease';
                        element.style.color = '#ffc107';
                        setTimeout(() => {
                            element.style.color = '';
                        }, 300);
                    }
                }
            }

            // Add event listeners to all filter inputs
            filterInputs.forEach(input => {
                input.addEventListener('change', updateDashboardCards);
                input.addEventListener('input', updateDashboardCards);
            });

            // Initialize detail links on page load
            updateDetailLinks();

            // Bulk delete functionality
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            const rowCheckboxes = document.querySelectorAll('.rowCheckbox');
            const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
            const selectedCountSpan = document.getElementById('selectedCount');
            const bulkDeleteForm = document.getElementById('bulkDeleteForm');
            const bulkDeleteIds = document.getElementById('bulkDeleteIds');

            function updateDeleteButton() {
                const checkedCount = document.querySelectorAll('.rowCheckbox:checked').length;
                selectedCountSpan.textContent = checkedCount;
                deleteSelectedBtn.classList.toggle('d-none', checkedCount === 0);
            }

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    rowCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    updateDeleteButton();
                });
            }

            rowCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const allChecked = Array.from(rowCheckboxes).every(cb => cb.checked);
                    const someChecked = Array.from(rowCheckboxes).some(cb => cb.checked);
                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = allChecked;
                        selectAllCheckbox.indeterminate = someChecked && !allChecked;
                    }
                    updateDeleteButton();
                });
            });

            if (deleteSelectedBtn) {
                deleteSelectedBtn.addEventListener('click', function() {
                    const checkedIds = Array.from(document.querySelectorAll('.rowCheckbox:checked'))
                        .map(checkbox => checkbox.value);

                    if (checkedIds.length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Tidak Ada Pilihan',
                            text: 'Silakan pilih minimal 1 data untuk dihapus',
                        });
                        return;
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: 'Konfirmasi Hapus',
                        text: `Anda akan menghapus ${checkedIds.length} data absensi. Tindakan ini tidak dapat dibatalkan!`,
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            bulkDeleteIds.value = JSON.stringify(checkedIds);
                            bulkDeleteForm.submit();
                        }
                    });
                });
            }

            const riwayatTable = $('#absensiTable');
            if (riwayatTable.length) {
                if ($.fn.DataTable.isDataTable('#absensiTable')) {
                    riwayatTable.DataTable().destroy();
                }

                riwayatTable.DataTable({
                    pageLength: 10,
                    lengthChange: true,
                    ordering: true,
                    searching: true,
                    responsive: true,
                    autoWidth: false,
                    order: [
                        [1, 'desc']
                    ],
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
                        emptyTable: 'Belum ada data absensi pembekalan.'
                    }
                });
            }

            const isInputPage = @json($showInputSection && $canManageAbsensi);
            if (!isInputPage) {
                return;
            }

            const endpoint = @json(route('pembekalan.absensi.input.students'));
            const $kelompok = $('#kelompokInputSelect');
            const $tanggal = $('#tanggalInputSelect');
            const $bulkForm = $('#bulkAbsensiForm');
            const $bulkKelompokId = $('#bulkKelompokId');
            const $bulkTanggalAbsensi = $('#bulkTanggalAbsensi');
            const $bulkSesiAbsensi = $('#bulkSesiAbsensi');
            const $inputInfo = $('#inputAbsensiInfo');
            const $inputWarning = $('#inputAbsensiWarning');
            const $inputLoading = $('#inputAbsensiLoading');
            const $tbody = $('#inputAbsensiBody');
            const $submitBtn = $('#submitBulkAbsensiBtn');
            const $checkAll = $('#checkAllSiswaAbsensi');
            const $sesi = $('#sesiInputSelect');

            const hideWarning = () => {
                $inputWarning.addClass('d-none').text('');
            };

            const showWarning = (message) => {
                $inputWarning.removeClass('d-none').text(message);
            };

            const setInfo = (kelompokName, pembimbingName) => {
                let text = 'Kelompok: ' + kelompokName;
                if (pembimbingName) {
                    text += ' - Pembimbing: ' + pembimbingName;
                }
                $inputInfo.removeClass('d-none').html(text);
            };

            const clearInfo = () => {
                $inputInfo.addClass('d-none').text('');
            };

            const resetInputTable = () => {
                if ($.fn.DataTable.isDataTable('#inputAbsensiTable')) {
                    $('#inputAbsensiTable').DataTable().destroy();
                }
                $tbody.empty();
                $bulkKelompokId.val('');
                $bulkTanggalAbsensi.val('');
                $bulkSesiAbsensi.val('');
                clearInfo();
                $checkAll.prop('checked', false);
                setupInputDataTable('Pilih kelompok bimbingan untuk menampilkan data siswa.');
                updateSubmitState();
            };

            const setLoading = (isLoading) => {
                $kelompok.prop('disabled', isLoading);
                $tanggal.prop('disabled', isLoading);
                $sesi.prop('disabled', isLoading);
                $submitBtn.prop('disabled', isLoading);
                $inputLoading.toggleClass('d-none', !isLoading);
            };

            const escapeHtml = (value) => {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            };

            const buildRows = (students) => {
                const rows = [];

                students.forEach((student, index) => {
                    const siswaId = Number(student.siswa_id);
                    const status = student.status || 'hadir';
                    const siswaName = escapeHtml(student.nama_siswa || '-');
                    const kelasName = student.kelas ? '<br><small class="text-muted">' + escapeHtml(
                        student.kelas) + '</small>' : '';
                    const keterangan = escapeHtml(student.keterangan || '');

                    rows.push(`
                        <tr>
                            <td class="text-center align-middle">
                                <input type="checkbox" class="siswa-checkbox" name="siswa_ids[]" value="${siswaId}" checked>
                            </td>
                            <td>${index + 1}</td>
                            <td>${siswaName}${kelasName}</td>
                            <td>
                                <select name="statuses[${siswaId}]" class="form-control form-control-sm" required>
                                    <option value="hadir" ${status === 'hadir' ? 'selected' : ''}>Hadir</option>
                                    <option value="izin" ${status === 'izin' ? 'selected' : ''}>Izin</option>
                                    <option value="alpa" ${status === 'alpa' ? 'selected' : ''}>Alpa</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="keterangans[${siswaId}]" class="form-control form-control-sm" value="${keterangan}" placeholder="Keterangan (opsional)">
                            </td>
                        </tr>
                    `);
                });

                $tbody.html(rows.join(''));
                $checkAll.prop('checked', students.length > 0);
                updateSubmitState();
            };

            const setupInputDataTable = (emptyTableMessage = 'Belum ada siswa pada kelompok ini.') => {
                $('#inputAbsensiTable').DataTable({
                    pageLength: 10,
                    lengthChange: true,
                    ordering: true,
                    searching: true,
                    responsive: true,
                    autoWidth: false,
                    order: [
                        [1, 'asc']
                    ],
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
                        emptyTable: emptyTableMessage
                    }
                });
            };

            const updateSubmitState = () => {
                const selectedCount = $('#inputAbsensiTable .siswa-checkbox:checked').length;
                $submitBtn.prop('disabled', selectedCount === 0 || !$bulkKelompokId.val());
            };

            const syncCheckAllState = () => {
                const totalCheckbox = $('#inputAbsensiTable .siswa-checkbox').length;
                const checkedCheckbox = $('#inputAbsensiTable .siswa-checkbox:checked').length;
                $checkAll.prop('checked', totalCheckbox > 0 && totalCheckbox === checkedCheckbox);
            };

            const loadStudents = () => {
                hideWarning();

                const kelompokId = $kelompok.val();
                const tanggalAbsensi = $tanggal.val();
                const sesiAbsensi = $sesi.val();

                if (!kelompokId || !tanggalAbsensi || !sesiAbsensi) {
                    resetInputTable();
                    return;
                }

                setLoading(true);

                $.ajax({
                    url: endpoint,
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        kelompok_id: kelompokId,
                        tanggal_absensi: tanggalAbsensi,
                        sesi_absensi: sesiAbsensi,
                    },
                    success: function(response) {
                        const students = Array.isArray(response.students) ? response.students : [];

                        if ($.fn.DataTable.isDataTable('#inputAbsensiTable')) {
                            $('#inputAbsensiTable').DataTable().destroy();
                        }

                        $bulkKelompokId.val(response.kelompok.id);
                        $bulkTanggalAbsensi.val(response.tanggal_absensi);
                        $bulkSesiAbsensi.val(response.sesi_absensi);
                        buildRows(students);
                        setupInputDataTable();
                        setInfo(response.kelompok.nama_kelompok, response.kelompok.pembimbing);
                        updateSubmitState();
                    },
                    error: function(xhr) {
                        resetInputTable();
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

            $kelompok.on('change', loadStudents);
            $tanggal.on('change', loadStudents);
            $sesi.on('change', loadStudents);

            $(document).on('change', '#checkAllSiswaAbsensi', function() {
                const isChecked = $(this).is(':checked');
                $('#inputAbsensiTable .siswa-checkbox').prop('checked', isChecked);
                updateSubmitState();
            });

            $(document).on('change', '#inputAbsensiTable .siswa-checkbox', function() {
                syncCheckAllState();
                updateSubmitState();
            });

            $bulkForm.on('submit', function(event) {
                if ($('#inputAbsensiTable .siswa-checkbox:checked').length === 0) {
                    event.preventDefault();
                    showWarning('Pilih minimal satu siswa untuk menyimpan absensi.');
                }
            });

            resetInputTable();

            if ($kelompok.val()) {
                loadStudents();
            }
        });
    </script>
@endsection
