@extends('adminlte::page')

@section('title', 'Data Tugas Pembekalan')

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Data Tugas Pembekalan</h1>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid pt-3">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('pembekalan.tugas') }}">
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
                        <div class="col-md-4 mb-2 d-flex">
                            <button type="submit" class="btn btn-sm btn-primary mr-1 w-100">Filter</button>
                            <a href="{{ route('pembekalan.tugas') }}"
                                class="btn btn-sm btn-outline-secondary w-100">Reset</a>
                        </div>
                    </div>
                    <div class="form-row mt-1">
                        <div class="col-md-12">
                            <input type="text" name="keyword" class="form-control form-control-sm"
                                placeholder="Cari judul / deskripsi tugas" value="{{ $filters['keyword'] }}">
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white d-flex align-items-center">
                <div>
                    <h5 class="mb-0">Daftar Tugas Pembekalan</h5>
                    <small class="text-muted">{{ $tugas->count() }} data</small>
                </div>
                @can('panitia')
                    <button type="button" class="btn btn-sm btn-primary ml-auto" id="btnOpenTugasModal">Tambah Tugas</button>
                @endcan
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped datatable-tugas">
                    <thead>
                        <tr>
                            <th style="width: 95px;">Tanggal</th>
                            <th style="width: 220px;">Materi</th>
                            <th style="width: 170px;">Judul Tugas</th>
                            <th>Soal Essay</th>
                            <th>Deskripsi</th>
                            <th style="width: 95px;">Deadline</th>
                            <th style="width: 160px;">Status Jawaban</th>
                            @can('panitia')
                                <th style="width: 130px;">Aksi</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tugas as $item)
                            @php
                                $jawabanTerbaru = $item->jawabanSiswa->sortByDesc('submitted_at')->first();
                            @endphp
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($item->tanggal_tugas)->format('d-m-Y') }}</td>
                                <td>
                                    @if ($item->materi)
                                        {{ $item->materi->topik }}
                                        <br><small
                                            class="text-muted">{{ \Carbon\Carbon::parse($item->materi->tanggal_materi)->format('d-m-Y') }}</small>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $item->judul_tugas }}</td>
                                <td>
                                    @if (is_array($item->soal_essay) && count($item->soal_essay) > 0)
                                        <ol class="pl-3 mb-0">
                                            @foreach ($item->soal_essay as $soal)
                                                <li>{{ $soal }}</li>
                                            @endforeach
                                        </ol>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ \Illuminate\Support\Str::limit($item->deskripsi_tugas ?? '-', 140) }}</td>
                                <td>
                                    @if ($item->deadline)
                                        {{ \Carbon\Carbon::parse($item->deadline)->format('d-m-Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if ($jawabanTerbaru)
                                        <span class="badge badge-success">Sudah Dijawab</span>
                                        @if (!is_null($jawabanTerbaru->nilaiTugas?->nilai))
                                            <div><small>Nilai: {{ $jawabanTerbaru->nilaiTugas->nilai }}</small></div>
                                        @endif
                                    @else
                                        <span class="badge badge-secondary">Belum Dijawab</span>
                                    @endif
                                </td>
                                @can('panitia')
                                    <td>
                                        <button type="button" class="btn btn-xs btn-warning mb-1 btn-edit-tugas"
                                            data-id="{{ $item->id }}" data-materi_id="{{ $item->materi_id }}"
                                            data-tanggal_tugas="{{ $item->tanggal_tugas }}"
                                            data-judul_tugas="{{ e($item->judul_tugas) }}"
                                            data-soal_essay='@json($item->soal_essay ?? [])'
                                            data-deskripsi_tugas="{{ e($item->deskripsi_tugas ?? '') }}"
                                            data-deadline="{{ $item->deadline }}">
                                            Edit
                                        </button>

                                        <form method="POST" action="{{ route('pembekalan.tugas.destroy', $item->id) }}"
                                            class="d-inline form-delete-tugas">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger mb-1">Hapus</button>
                                        </form>
                                    </td>
                                @endcan
                            </tr>
                        @empty
                            {{-- Empty state handled by DataTables language.emptyTable. --}}
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @can('panitia')
            <div class="modal fade" id="tugasModal" tabindex="-1" role="dialog" aria-labelledby="tugasModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="tugasModalLabel">Tambah Tugas Pembekalan</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST" action="{{ route('pembekalan.tugas.store') }}" id="tugasForm">
                            <div class="modal-body">
                                @csrf
                                <input type="hidden" name="edit_id" id="edit_id" value="{{ old('edit_id') }}">
                                <input type="hidden" name="_method" id="tugasFormMethod" value="POST">

                                <div class="form-row">
                                    <div class="col-md-12 mb-2">
                                        <label class="mb-1">Materi (1 Materi = 1 Tugas)</label>
                                        <select name="materi_id" class="form-control form-control-sm" required>
                                            <option value="">Pilih Materi</option>
                                            @foreach ($materiOptions as $materi)
                                                <option value="{{ $materi->id }}"
                                                    {{ (string) old('materi_id') === (string) $materi->id ? 'selected' : '' }}>
                                                    {{ \Carbon\Carbon::parse($materi->tanggal_materi)->format('d-m-Y') }} -
                                                    {{ $materi->topik }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <label class="mb-1">Tanggal Tugas</label>
                                        <input type="date" name="tanggal_tugas" class="form-control form-control-sm"
                                            value="{{ old('tanggal_tugas', now()->toDateString()) }}" required>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <label class="mb-1">Deadline</label>
                                        <input type="date" name="deadline" class="form-control form-control-sm"
                                            value="{{ old('deadline') }}">
                                    </div>

                                    <div class="col-md-12 mb-2">
                                        <label class="mb-1">Judul Tugas</label>
                                        <input type="text" name="judul_tugas" class="form-control form-control-sm"
                                            value="{{ old('judul_tugas') }}" placeholder="Masukkan judul tugas" required>
                                    </div>

                                    <div class="col-md-12 mb-2">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="mb-0">Soal Essay (minimal 2 soal)</label>
                                            <button type="button" class="btn btn-xs btn-outline-primary"
                                                id="btnAddSoalEssay">Tambah Soal</button>
                                        </div>
                                        <div id="soalEssayWrap"></div>
                                    </div>

                                    <div class="col-md-12 mb-2">
                                        <label class="mb-1">Deskripsi Tugas</label>
                                        <textarea name="deskripsi_tugas" rows="4" class="form-control form-control-sm"
                                            placeholder="Jelaskan detail tugas pembekalan...">{{ old('deskripsi_tugas') }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    data-dismiss="modal">Tutup</button>
                                <button type="submit" class="btn btn-sm btn-primary" id="btnSubmitTugas">Tambah
                                    Tugas</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan
    </div>
@endsection

@section('plugins.Datatables', true)

@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function() {
            const originalSwalFire = Swal.fire.bind(Swal);
            Swal.fire = function(options, ...args) {
                if (typeof options === 'object' && options !== null) {
                    const merged = {
                        confirmButtonColor: '#0d6efd',
                        cancelButtonColor: '#6c757d',
                        ...options,
                    };

                    if (merged.showCancelButton) {
                        merged.confirmButtonText = merged.confirmButtonText || 'Ya, lanjut';
                        merged.cancelButtonText = merged.cancelButtonText || 'Batal';
                    } else {
                        merged.confirmButtonText = merged.confirmButtonText || 'OK';
                    }

                    if (merged.icon === 'success' && merged.timer === undefined && merged.showConfirmButton ===
                        undefined) {
                        merged.timer = 1800;
                        merged.showConfirmButton = false;
                    }

                    return originalSwalFire(merged, ...args);
                }

                return originalSwalFire(options, ...args);
            };

            const tugasForm = $('#tugasForm');
            const tugasModal = $('#tugasModal');
            const tugasTitle = $('#tugasModalLabel');
            const submitBtn = $('#btnSubmitTugas');
            const methodInput = $('#tugasFormMethod');
            const editIdInput = $('#edit_id');
            const storeUrl = @json(route('pembekalan.tugas.store'));
            const updateUrlTemplate = @json(route('pembekalan.tugas.update', ['tugasPembekalan' => '__ID__']));
            const successMessage = @json(session('success'));
            const errorMessages = @json($errors->all());
            const oldSoalEssay = @json(old('soal_essay', []));

            function soalEssayInputHtml(index, value = '') {
                const escaped = String(value || '').replace(/"/g, '&quot;');
                return `
                    <div class="input-group input-group-sm mb-2 soal-essay-item">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Soal ${index + 1}</span>
                        </div>
                        <input type="text" name="soal_essay[]" class="form-control" value="${escaped}" placeholder="Masukkan pertanyaan essay" required>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-danger btn-remove-soal">Hapus</button>
                        </div>
                    </div>
                `;
            }

            function renderSoalEssay(values = []) {
                const normalized = (Array.isArray(values) ? values : []).filter(v => String(v || '').trim() !== '');
                const finalValues = normalized.length >= 2 ? normalized : ['', ''];
                $('#soalEssayWrap').empty();
                finalValues.forEach((value, index) => {
                    $('#soalEssayWrap').append(soalEssayInputHtml(index, value));
                });
                reindexSoalEssay();
            }

            function reindexSoalEssay() {
                $('#soalEssayWrap .soal-essay-item').each(function(index) {
                    $(this).find('.input-group-text').text('Soal ' + (index + 1));
                });
            }

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
                    title: 'Gagal menyimpan tugas',
                    html: errorMessages.join('<br>'),
                });
            }

            function setValue(name, value) {
                const el = tugasForm.find('[name="' + name + '"]');
                el.val(value || '');
            }

            function resetModalToAdd() {
                tugasTitle.text('Tambah Tugas Pembekalan');
                submitBtn.text('Tambah Tugas');
                tugasForm.attr('action', storeUrl);
                methodInput.val('POST');
                editIdInput.val('');

                if (!@json($errors->any())) {
                    tugasForm[0].reset();
                    setValue('tanggal_tugas', @json(now()->toDateString()));
                    renderSoalEssay(['', '']);
                }
            }

            function setModalToEdit(data) {
                tugasTitle.text('Edit Tugas Pembekalan');
                submitBtn.text('Simpan Perubahan');
                tugasForm.attr('action', updateUrlTemplate.replace('__ID__', data.id));
                methodInput.val('PUT');
                editIdInput.val(data.id);

                setValue('materi_id', data.materi_id || '');
                setValue('tanggal_tugas', data.tanggal_tugas || '');
                setValue('judul_tugas', data.judul_tugas || '');
                setValue('deskripsi_tugas', data.deskripsi_tugas || '');
                setValue('deadline', data.deadline || '');
                renderSoalEssay(data.soal_essay || ['', '']);
            }

            $('#btnOpenTugasModal').on('click', function() {
                resetModalToAdd();
                tugasModal.modal('show');
            });

            $(document).on('click', '.btn-edit-tugas', function() {
                const btn = $(this);
                setModalToEdit({
                    id: btn.data('id'),
                    materi_id: btn.data('materi_id'),
                    tanggal_tugas: btn.data('tanggal_tugas') || '',
                    judul_tugas: btn.data('judul_tugas') || '',
                    soal_essay: btn.data('soal_essay') || [],
                    deskripsi_tugas: btn.data('deskripsi_tugas') || '',
                    deadline: btn.data('deadline') || ''
                });
                tugasModal.modal('show');
            });

            $('#btnAddSoalEssay').on('click', function() {
                const index = $('#soalEssayWrap .soal-essay-item').length;
                $('#soalEssayWrap').append(soalEssayInputHtml(index, ''));
                reindexSoalEssay();
            });

            $(document).on('click', '.btn-remove-soal', function() {
                const total = $('#soalEssayWrap .soal-essay-item').length;
                if (total <= 2) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Minimal 2 soal',
                        text: 'Tugas essay harus berisi minimal 2 soal.',
                    });
                    return;
                }

                $(this).closest('.soal-essay-item').remove();
                reindexSoalEssay();
            });

            $(document).on('submit', '.form-delete-tugas', function(e) {
                e.preventDefault();
                const form = this;

                Swal.fire({
                    title: 'Hapus tugas?',
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

            @can('panitia')
                @if ($errors->any())
                    if ($('#edit_id').val()) {
                        tugasTitle.text('Edit Tugas Pembekalan');
                        submitBtn.text('Simpan Perubahan');
                        methodInput.val('PUT');
                        tugasForm.attr('action', updateUrlTemplate.replace('__ID__', $('#edit_id').val()));
                    } else {
                        resetModalToAdd();
                    }
                    $('#tugasModal').modal('show');
                @endif
            @endcan

            if (@json($errors->any())) {
                renderSoalEssay(oldSoalEssay);
            } else {
                renderSoalEssay(['', '']);
            }

            $('.datatable-tugas').DataTable({
                pageLength: 10,
                lengthChange: true,
                ordering: true,
                searching: true,
                responsive: true,
                autoWidth: false,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
                    emptyTable: 'Belum ada data tugas pembekalan.'
                }
            });
        });
    </script>
@endsection
