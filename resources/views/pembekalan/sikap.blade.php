@extends('adminlte::page')

@section('title', 'Catatan Sikap Pembekalan')

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Catatan Sikap Pembekalan</h1>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid pt-3">
        @if ($showInputSection && $canManageSikap)
            <div class="card shadow-sm border-0 mb-3" id="input-sikap">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Input Catatan Sikap Kelompok (Multiple)</h5>
                    <small class="text-muted">Pilih kelompok, lalu isi catatan sikap siswa sekaligus.</small>
                </div>
                <div class="card-body">
                    <form id="filterInputSikapForm" class="mb-3" onsubmit="return false;">
                        <div class="form-row align-items-end">
                            <div class="col-md-6 mb-2">
                                <label class="mb-1">Kelompok Bimbingan</label>
                                <select id="kelompokSikapSelect" name="kelompok_id_input"
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
                                <label class="mb-1">Tanggal Penilaian</label>
                                <input type="date" id="tanggalSikapSelect" name="tanggal_penilaian_input"
                                    class="form-control form-control-sm" value="{{ $bulkInput['tanggal_penilaian'] }}"
                                    required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="alert alert-light border py-2 mb-0 text-muted text-center">Tabel tampil otomatis
                                </div>
                            </div>
                        </div>
                    </form>

                    <div id="inputSikapInfo" class="alert alert-info py-2 mb-2 d-none"></div>
                    <div id="inputSikapWarning" class="alert alert-warning py-2 mb-2 d-none"></div>
                    <div id="inputSikapLoading" class="alert alert-secondary py-2 mb-2 d-none">
                        <span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
                        Memuat data siswa kelompok...
                    </div>

                    <form method="POST" action="{{ route('pembekalan.sikap.bulk-store') }}" id="bulkSikapForm">
                        @csrf
                        <input type="hidden" name="kelompok_id" id="bulkSikapKelompokId">
                        <input type="hidden" name="tanggal_penilaian" id="bulkSikapTanggalPenilaian">

                        <div class="table-responsive">
                            <table id="inputSikapTable" class="table table-bordered table-striped table-sm mb-2">
                                <thead>
                                    <tr>
                                        <th style="width: 44px;" class="text-center">
                                            <input type="checkbox" id="checkAllSiswaSikap" title="Pilih semua siswa">
                                        </th>
                                        <th style="width: 50px;">No</th>
                                        <th style="width: 260px;">Siswa</th>
                                        <th style="width: 150px;">Nilai Sikap</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody id="inputSikapBody"></tbody>
                            </table>
                        </div>

                        <button type="submit" id="submitBulkSikapBtn" class="btn btn-sm btn-primary" disabled>
                            Simpan Catatan Sikap Kelompok
                        </button>
                    </form>
                </div>
            </div>
        @endif

        @if ($showRiwayatSection)
            <div class="card shadow-sm border-0 mb-3" id="lihat-sikap">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('pembekalan.sikap.riwayat') }}">
                        <div class="form-row align-items-end">
                            <div class="col-md-3 mb-2">
                                <label class="mb-1">Dari</label>
                                <input type="date" name="tanggal_awal" class="form-control form-control-sm"
                                    value="{{ $filters['tanggal_awal'] }}">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="mb-1">Sampai</label>
                                <input type="date" name="tanggal_akhir" class="form-control form-control-sm"
                                    value="{{ $filters['tanggal_akhir'] }}">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="mb-1">Pembimbing</label>
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
                            <div class="col-md-3 mb-2 d-flex">
                                <button type="submit" class="btn btn-sm btn-primary mr-1 w-100">Filter</button>
                                <a href="{{ route('pembekalan.sikap.riwayat') }}"
                                    class="btn btn-sm btn-outline-secondary w-100">Reset</a>
                            </div>
                        </div>

                        <div class="form-row mt-1">
                            <div class="col-md-3 mb-2">
                                <label class="mb-1">Nilai Sikap</label>
                                <select name="nilai_sikap" class="form-control form-control-sm">
                                    <option value="">Semua Nilai</option>
                                    <option value="sangat_baik"
                                        {{ $filters['nilai_sikap'] === 'sangat_baik' ? 'selected' : '' }}>
                                        Sangat Baik</option>
                                    <option value="baik" {{ $filters['nilai_sikap'] === 'baik' ? 'selected' : '' }}>Baik
                                    </option>
                                    <option value="cukup" {{ $filters['nilai_sikap'] === 'cukup' ? 'selected' : '' }}>
                                        Cukup</option>
                                    <option value="perlu_bimbingan"
                                        {{ $filters['nilai_sikap'] === 'perlu_bimbingan' ? 'selected' : '' }}>Perlu
                                        Bimbingan</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="mb-1">Kelompok</label>
                                <select name="kelompok_id" class="form-control form-control-sm">
                                    <option value="">Semua Kelompok</option>
                                    @foreach ($kelompokOptions as $kelompok)
                                        <option value="{{ $kelompok->id }}"
                                            {{ (string) $filters['kelompok_id'] === (string) $kelompok->id ? 'selected' : '' }}>
                                            {{ $kelompok->nama_kelompok }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-9">
                                <label class="mb-1">Cari Data</label>
                                <input type="text" name="keyword" class="form-control form-control-sm"
                                    placeholder="Cari siswa, pembimbing, atau catatan" value="{{ $filters['keyword'] }}">
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white d-flex align-items-center">
                    <div>
                        <h5 class="mb-0">Daftar Catatan Sikap</h5>
                        <small class="text-muted">{{ $nilaiSikap->count() }} data</small>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <table id="sikapTable" class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th style="width: 95px;">Tanggal</th>
                                <th style="width: 220px;">Siswa</th>
                                <th style="width: 180px;">Kelompok</th>
                                <th style="width: 220px;">Pembimbing</th>
                                <th style="width: 140px;">Nilai Sikap</th>
                                <th>Catatan</th>
                                @if ($canManageSikap)
                                    <th style="width: 130px;">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($nilaiSikap as $item)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($item->tanggal_penilaian)->format('d-m-Y') }}</td>
                                    <td>
                                        {{ $item->siswa->nama_siswa ?? '-' }}
                                        @if ($item->siswa && $item->siswa->kelas)
                                            <br><small class="text-muted">{{ $item->siswa->kelas->nama_kelas }}</small>
                                        @endif
                                    </td>
                                    <td>{{ optional(optional($item->siswa)->kelompokBimbingan)->pluck('nama_kelompok')->first() ?? '-' }}
                                    </td>
                                    <td>{{ $item->pembimbing->nama_pembimbing ?? '-' }}</td>
                                    <td>
                                        @php
                                            $badge =
                                                [
                                                    'sangat_baik' => 'success',
                                                    'baik' => 'primary',
                                                    'cukup' => 'warning',
                                                    'perlu_bimbingan' => 'danger',
                                                ][$item->nilai_sikap] ?? 'secondary';

                                            $label =
                                                [
                                                    'sangat_baik' => 'Sangat Baik',
                                                    'baik' => 'Baik',
                                                    'cukup' => 'Cukup',
                                                    'perlu_bimbingan' => 'Perlu Bimbingan',
                                                ][$item->nilai_sikap] ?? strtoupper((string) $item->nilai_sikap);
                                        @endphp
                                        <span class="badge badge-{{ $badge }}">{{ $label }}</span>
                                    </td>
                                    <td>{{ \Illuminate\Support\Str::limit($item->catatan ?? '-', 200) }}</td>
                                    @if ($canManageSikap)
                                        <td>
                                            <button type="button" class="btn btn-xs btn-warning mb-1 btn-edit-sikap"
                                                data-id="{{ $item->id }}"
                                                data-pembimbing_id="{{ $item->pembimbing_id }}"
                                                data-siswa_id="{{ $item->siswa_id }}"
                                                data-tanggal_penilaian="{{ $item->tanggal_penilaian }}"
                                                data-nilai_sikap="{{ $item->nilai_sikap }}"
                                                data-catatan="{{ e($item->catatan ?? '') }}">
                                                Edit
                                            </button>

                                            <form method="POST"
                                                action="{{ route('pembekalan.sikap.destroy', $item->id) }}"
                                                class="d-inline form-delete-sikap">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-danger mb-1">Hapus</button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                {{-- Empty state handled by DataTables language.emptyTable. --}}
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if ($showRiwayatSection && $canManageSikap)
            <div class="modal fade" id="sikapModal" tabindex="-1" role="dialog" aria-labelledby="sikapModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="sikapModalLabel">Edit Catatan Sikap</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST" action="{{ route('pembekalan.sikap.store') }}" id="sikapForm">
                            <div class="modal-body">
                                @csrf
                                <input type="hidden" name="edit_id" id="edit_id" value="{{ old('edit_id') }}">
                                <input type="hidden" name="_method" id="sikapFormMethod" value="POST">

                                <div class="form-row">
                                    <div class="col-md-6 mb-2">
                                        <label class="mb-1">Tanggal Penilaian</label>
                                        <input type="date" name="tanggal_penilaian"
                                            class="form-control form-control-sm"
                                            value="{{ old('tanggal_penilaian', now()->toDateString()) }}" required>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <label class="mb-1">Nilai Sikap</label>
                                        <select name="nilai_sikap" class="form-control form-control-sm" required>
                                            <option value="sangat_baik"
                                                {{ old('nilai_sikap') === 'sangat_baik' ? 'selected' : '' }}>Sangat Baik
                                            </option>
                                            <option value="baik" {{ old('nilai_sikap') === 'baik' ? 'selected' : '' }}>
                                                Baik</option>
                                            <option value="cukup" {{ old('nilai_sikap') === 'cukup' ? 'selected' : '' }}>
                                                Cukup</option>
                                            <option value="perlu_bimbingan"
                                                {{ old('nilai_sikap') === 'perlu_bimbingan' ? 'selected' : '' }}>Perlu
                                                Bimbingan</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <label class="mb-1">Siswa</label>
                                        <select name="siswa_id" class="form-control form-control-sm" required>
                                            <option value="">Pilih Siswa</option>
                                            @foreach ($siswaOptions as $siswa)
                                                @php
                                                    $kelompokUtama = $siswa->kelompokBimbingan->firstWhere(
                                                        'pembimbing_id',
                                                        '!=',
                                                        null,
                                                    );
                                                    $autoPembimbingId = $kelompokUtama?->pembimbing_id;
                                                    $autoPembimbingNama = $kelompokUtama?->pembimbing?->nama_pembimbing;
                                                @endphp
                                                <option value="{{ $siswa->id }}"
                                                    data-pembimbing-id="{{ $autoPembimbingId ?? '' }}"
                                                    data-pembimbing-nama="{{ $autoPembimbingNama ?? '' }}"
                                                    {{ (string) old('siswa_id') === (string) $siswa->id ? 'selected' : '' }}>
                                                    {{ $siswa->nama_siswa }}
                                                    @if ($siswa->kelas)
                                                        - {{ $siswa->kelas->nama_kelas }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <label class="mb-1">Pembimbing</label>
                                        <select name="pembimbing_id" class="form-control form-control-sm" required>
                                            <option value="">Pilih Pembimbing</option>
                                            @foreach ($pembimbingOptions as $pembimbing)
                                                <option value="{{ $pembimbing->id }}"
                                                    {{ (string) old('pembimbing_id') === (string) $pembimbing->id ? 'selected' : '' }}>
                                                    {{ $pembimbing->nama_pembimbing }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted" id="autoPembimbingHint">Pilih siswa untuk
                                            sinkronisasi pembimbing otomatis.</small>
                                    </div>

                                    <div class="col-md-12 mb-2">
                                        <label class="mb-1">Catatan Sikap</label>
                                        <textarea name="catatan" rows="3" class="form-control form-control-sm"
                                            placeholder="Catatan penilaian sikap (opsional)">{{ old('catatan') }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    data-dismiss="modal">Tutup</button>
                                <button type="submit" class="btn btn-sm btn-primary" id="btnSubmitSikap">Simpan
                                    Perubahan</button>
                            </div>
                        </form>
                    </div>
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
            const sikapForm = $('#sikapForm');
            const sikapModal = $('#sikapModal');
            const sikapTitle = $('#sikapModalLabel');
            const submitBtn = $('#btnSubmitSikap');
            const methodInput = $('#sikapFormMethod');
            const editIdInput = $('#edit_id');
            const siswaSelect = sikapForm.find('[name="siswa_id"]');
            const pembimbingSelect = sikapForm.find('[name="pembimbing_id"]');
            const autoPembimbingHint = $('#autoPembimbingHint');
            const updateUrlTemplate = @json(route('pembekalan.sikap.update', ['nilaiSikapPembekalan' => '__ID__']));
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
                    title: 'Gagal menyimpan catatan sikap',
                    html: errorMessages.join('<br>'),
                });
            }

            function setValue(name, value) {
                const el = sikapForm.find('[name="' + name + '"]');
                el.val(value || '');
            }

            function resetPembimbingOptions(targetSelect) {
                targetSelect.find('option').prop('disabled', false);
            }

            function lockPembimbingOptions(targetSelect, allowedPembimbingId) {
                resetPembimbingOptions(targetSelect);
                targetSelect.find('option').each(function() {
                    const optionValue = $(this).val();
                    if (optionValue !== '' && optionValue !== allowedPembimbingId) {
                        $(this).prop('disabled', true);
                    }
                });
            }

            function syncPembimbingBySiswa(sourceSelect, targetSelect, targetHint) {
                const selectedOption = sourceSelect.find('option:selected');
                const pembimbingId = selectedOption.attr('data-pembimbing-id') || '';
                const pembimbingNama = selectedOption.attr('data-pembimbing-nama') || '';

                if (pembimbingId !== '') {
                    lockPembimbingOptions(targetSelect, pembimbingId);
                    targetSelect.val(pembimbingId);
                    targetHint
                        .removeClass('text-warning')
                        .addClass('text-muted')
                        .text('Pembimbing otomatis: ' + pembimbingNama + '. Opsi pembimbing lain dinonaktifkan.');
                } else if (sourceSelect.val()) {
                    resetPembimbingOptions(targetSelect);
                    targetHint
                        .removeClass('text-muted')
                        .addClass('text-warning')
                        .text('Siswa belum terhubung ke kelompok bimbingan, pilih pembimbing manual.');
                } else {
                    resetPembimbingOptions(targetSelect);
                    targetHint
                        .removeClass('text-warning')
                        .addClass('text-muted')
                        .text('Pilih siswa untuk sinkronisasi pembimbing otomatis.');
                }
            }

            function setModalToEdit(data) {
                sikapTitle.text('Edit Catatan Sikap');
                submitBtn.text('Simpan Perubahan');
                sikapForm.attr('action', updateUrlTemplate.replace('__ID__', data.id));
                methodInput.val('PUT');
                editIdInput.val(data.id);

                setValue('tanggal_penilaian', data.tanggal_penilaian || '');
                setValue('nilai_sikap', data.nilai_sikap || 'sangat_baik');
                setValue('siswa_id', data.siswa_id || '');
                setValue('pembimbing_id', data.pembimbing_id || '');
                setValue('catatan', data.catatan || '');

                syncPembimbingBySiswa(siswaSelect, pembimbingSelect, autoPembimbingHint);
            }

            if (sikapForm.length) {
                siswaSelect.on('change', function() {
                    syncPembimbingBySiswa(siswaSelect, pembimbingSelect, autoPembimbingHint);
                });
            }

            $(document).on('click', '.btn-edit-sikap', function() {
                if (!sikapForm.length) {
                    return;
                }

                const btn = $(this);
                setModalToEdit({
                    id: btn.data('id'),
                    tanggal_penilaian: btn.data('tanggal_penilaian') || '',
                    nilai_sikap: btn.data('nilai_sikap') || 'sangat_baik',
                    siswa_id: btn.data('siswa_id') || '',
                    pembimbing_id: btn.data('pembimbing_id') || '',
                    catatan: btn.data('catatan') || ''
                });
                sikapModal.modal('show');
            });

            $(document).on('submit', '.form-delete-sikap', function(e) {
                e.preventDefault();
                const form = this;

                Swal.fire({
                    title: 'Hapus catatan sikap?',
                    text: 'Data yang dihapus tidak bisa dikembalikan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

            @if ($canManageSikap)
                @if ($errors->any())
                    if (sikapForm.length && $('#edit_id').val()) {
                        sikapTitle.text('Edit Catatan Sikap');
                        submitBtn.text('Simpan Perubahan');
                        methodInput.val('PUT');
                        sikapForm.attr('action', updateUrlTemplate.replace('__ID__', $('#edit_id').val()));
                        syncPembimbingBySiswa(siswaSelect, pembimbingSelect, autoPembimbingHint);
                        $('#sikapModal').modal('show');
                    }
                @endif
            @endif

            if (!$.fn.DataTable) {
                console.error('DataTables library gagal dimuat.');
                return;
            }

            const sikapTable = $('#sikapTable');
            if (sikapTable.length) {
                if ($.fn.DataTable.isDataTable('#sikapTable')) {
                    sikapTable.DataTable().destroy();
                }

                sikapTable.DataTable({
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
                        emptyTable: 'Belum ada data catatan sikap pembekalan.'
                    }
                });
            }

            const isInputPage = @json($showInputSection && $canManageSikap);
            if (!isInputPage) {
                return;
            }

            const endpoint = @json(route('pembekalan.sikap.input.students'));
            const $kelompok = $('#kelompokSikapSelect');
            const $tanggal = $('#tanggalSikapSelect');
            const $bulkForm = $('#bulkSikapForm');
            const $bulkKelompokId = $('#bulkSikapKelompokId');
            const $bulkTanggal = $('#bulkSikapTanggalPenilaian');
            const $inputInfo = $('#inputSikapInfo');
            const $inputWarning = $('#inputSikapWarning');
            const $inputLoading = $('#inputSikapLoading');
            const $tbody = $('#inputSikapBody');
            const $submitBtn = $('#submitBulkSikapBtn');
            const $checkAll = $('#checkAllSiswaSikap');

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

            const setLoading = (isLoading) => {
                $kelompok.prop('disabled', isLoading);
                $tanggal.prop('disabled', isLoading);
                $submitBtn.prop('disabled', isLoading || $submitBtn.prop('disabled'));
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

            const updateSubmitState = () => {
                const selectedCount = $('#inputSikapTable .siswa-checkbox:checked').length;
                $submitBtn.prop('disabled', selectedCount === 0 || !$bulkKelompokId.val());
            };

            const syncCheckAllState = () => {
                const totalCheckbox = $('#inputSikapTable .siswa-checkbox').length;
                const checkedCheckbox = $('#inputSikapTable .siswa-checkbox:checked').length;
                $checkAll.prop('checked', totalCheckbox > 0 && totalCheckbox === checkedCheckbox);
            };

            const buildRows = (students) => {
                const rows = [];

                students.forEach((student, index) => {
                    const siswaId = Number(student.siswa_id);
                    const nilaiSikap = student.nilai_sikap || 'sangat_baik';
                    const siswaName = escapeHtml(student.nama_siswa || '-');
                    const kelasName = student.kelas ? '<br><small class="text-muted">' + escapeHtml(
                        student.kelas) + '</small>' : '';
                    const catatan = escapeHtml(student.catatan || '');

                    rows.push(`
                        <tr>
                            <td class="text-center align-middle">
                                <input type="checkbox" class="siswa-checkbox" name="siswa_ids[]" value="${siswaId}" checked>
                            </td>
                            <td>${index + 1}</td>
                            <td>${siswaName}${kelasName}</td>
                            <td>
                                <select name="nilai_sikap_values[${siswaId}]" class="form-control form-control-sm" required>
                                    <option value="sangat_baik" ${nilaiSikap === 'sangat_baik' ? 'selected' : ''}>Sangat Baik</option>
                                    <option value="baik" ${nilaiSikap === 'baik' ? 'selected' : ''}>Baik</option>
                                    <option value="cukup" ${nilaiSikap === 'cukup' ? 'selected' : ''}>Cukup</option>
                                    <option value="perlu_bimbingan" ${nilaiSikap === 'perlu_bimbingan' ? 'selected' : ''}>Perlu Bimbingan</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="catatans[${siswaId}]" class="form-control form-control-sm" value="${catatan}" placeholder="Catatan (opsional)">
                            </td>
                        </tr>
                    `);
                });

                $tbody.html(rows.join(''));
                $checkAll.prop('checked', students.length > 0);
                updateSubmitState();
            };

            const setupInputDataTable = (emptyTableMessage = 'Belum ada siswa pada kelompok ini.') => {
                if ($.fn.DataTable.isDataTable('#inputSikapTable')) {
                    $('#inputSikapTable').DataTable().destroy();
                }

                $('#inputSikapTable').DataTable({
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

            const resetInputTable = () => {
                if ($.fn.DataTable.isDataTable('#inputSikapTable')) {
                    $('#inputSikapTable').DataTable().destroy();
                }
                $tbody.empty();
                $bulkKelompokId.val('');
                $bulkTanggal.val('');
                clearInfo();
                $checkAll.prop('checked', false);
                setupInputDataTable('Pilih kelompok bimbingan untuk menampilkan data siswa.');
                updateSubmitState();
            };

            const loadStudents = () => {
                hideWarning();

                const kelompokId = $kelompok.val();
                const tanggalPenilaian = $tanggal.val();

                if (!kelompokId || !tanggalPenilaian) {
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
                        tanggal_penilaian: tanggalPenilaian,
                    },
                    success: function(response) {
                        const students = Array.isArray(response.students) ? response.students : [];

                        if ($.fn.DataTable.isDataTable('#inputSikapTable')) {
                            $('#inputSikapTable').DataTable().destroy();
                        }

                        $bulkKelompokId.val(response.kelompok.id);
                        $bulkTanggal.val(response.tanggal_penilaian);
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

            $(document).on('change', '#checkAllSiswaSikap', function() {
                const isChecked = $(this).is(':checked');
                $('#inputSikapTable .siswa-checkbox').prop('checked', isChecked);
                updateSubmitState();
            });

            $(document).on('change', '#inputSikapTable .siswa-checkbox', function() {
                syncCheckAllState();
                updateSubmitState();
            });

            $bulkForm.on('submit', function(event) {
                if ($('#inputSikapTable .siswa-checkbox:checked').length === 0) {
                    event.preventDefault();
                    showWarning('Pilih minimal satu siswa untuk menyimpan catatan sikap.');
                }
            });

            $kelompok.on('change', loadStudents);
            $tanggal.on('change', loadStudents);

            resetInputTable();
            if ($kelompok.val()) {
                loadStudents();
            }
        });
    </script>
@endsection
