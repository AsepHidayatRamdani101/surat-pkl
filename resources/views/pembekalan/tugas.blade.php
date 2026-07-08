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
            /* ---- DataTables ---- */
            if ($.fn.DataTable) {
                if ($.fn.DataTable.isDataTable('.datatable-tugas')) {
                    $('.datatable-tugas').DataTable().destroy();
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
            }

            /* ---- Alerts ---- */
            const successMessage = @json(session('success'));
            const errorMessages = @json($errors->all());
            const oldSoalEssay = @json(old('soal_essay', []));

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
                    html: errorMessages.join('<br>')
                });
            }

            /* ---- Soal Essay helpers ---- */
            function soalEssayInputHtml(index, value) {
                const escaped = String(value || '').replace(/"/g, '&quot;');
                return `<div class="input-group input-group-sm mb-2 soal-essay-item">
                    <div class="input-group-prepend"><span class="input-group-text">Soal ${index + 1}</span></div>
                    <input type="text" name="soal_essay[]" class="form-control" value="${escaped}" placeholder="Masukkan pertanyaan essay" required>
                    <div class="input-group-append"><button type="button" class="btn btn-outline-danger btn-remove-soal">Hapus</button></div>
                </div>`;
            }

            function reindexSoalEssay() {
                $('#soalEssayWrap .soal-essay-item').each(function(i) {
                    $(this).find('.input-group-text').text('Soal ' + (i + 1));
                });
            }

            function renderSoalEssay(values) {
                const normalized = (Array.isArray(values) ? values : []).filter(v => String(v || '').trim() !== '');
                const final = normalized.length >= 2 ? normalized : ['', ''];
                $('#soalEssayWrap').empty();
                final.forEach((v, i) => $('#soalEssayWrap').append(soalEssayInputHtml(i, v)));
            }

            /* ---- Modal helpers ---- */
            const storeUrl = @json(route('pembekalan.tugas.store'));
            const updateUrlTpl = @json(route('pembekalan.tugas.update', ['tugasPembekalan' => '__ID__']));

            function openAddModal() {
                $('#tugasModalLabel').text('Tambah Tugas Pembekalan');
                $('#btnSubmitTugas').text('Tambah Tugas');
                $('#tugasForm').attr('action', storeUrl);
                $('#tugasFormMethod').val('POST');
                $('#edit_id').val('');
                $('#tugasForm')[0].reset();
                $('#tugasForm [name="tanggal_tugas"]').val(@json(now()->toDateString()));
                renderSoalEssay(['', '']);
            }

            function openEditModal(data) {
                $('#tugasModalLabel').text('Edit Tugas Pembekalan');
                $('#btnSubmitTugas').text('Simpan Perubahan');
                $('#tugasForm').attr('action', updateUrlTpl.replace('__ID__', data.id));
                $('#tugasFormMethod').val('PUT');
                $('#edit_id').val(data.id);
                $('#tugasForm [name="materi_id"]').val(data.materi_id || '');
                $('#tugasForm [name="tanggal_tugas"]').val(data.tanggal_tugas || '');
                $('#tugasForm [name="judul_tugas"]').val(data.judul_tugas || '');
                $('#tugasForm [name="deskripsi_tugas"]').val(data.deskripsi_tugas || '');
                $('#tugasForm [name="deadline"]').val(data.deadline || '');
                renderSoalEssay(data.soal_essay || ['', '']);
            }

            /* ---- Restore on validation error ---- */
            @can('panitia')
                @if ($errors->any())
                    if ($('#edit_id').val()) {
                        openEditModal({
                            id: $('#edit_id').val(),
                            tanggal_tugas: $('#tugasForm [name="tanggal_tugas"]').val(),
                            judul_tugas: $('#tugasForm [name="judul_tugas"]').val(),
                            deskripsi_tugas: $('#tugasForm [name="deskripsi_tugas"]').val(),
                            deadline: $('#tugasForm [name="deadline"]').val(),
                            soal_essay: oldSoalEssay
                        });
                    } else {
                        renderSoalEssay(oldSoalEssay.length ? oldSoalEssay : ['', '']);
                    }
                    $('#tugasModal').modal('show');
                @else
                    renderSoalEssay(['', '']);
                @endif
            @else
                renderSoalEssay(['', '']);
            @endcan

            /* ---- Events ---- */
            $('#btnOpenTugasModal').on('click', function() {
                openAddModal();
                $('#tugasModal').modal('show');
            });

            $(document).on('click', '.btn-edit-tugas', function() {
                const btn = $(this);
                openEditModal({
                    id: btn.data('id'),
                    materi_id: btn.data('materi_id'),
                    tanggal_tugas: btn.data('tanggal_tugas') || '',
                    judul_tugas: btn.data('judul_tugas') || '',
                    soal_essay: btn.data('soal_essay') || [],
                    deskripsi_tugas: btn.data('deskripsi_tugas') || '',
                    deadline: btn.data('deadline') || ''
                });
                $('#tugasModal').modal('show');
            });

            $('#btnAddSoalEssay').on('click', function() {
                const idx = $('#soalEssayWrap .soal-essay-item').length;
                $('#soalEssayWrap').append(soalEssayInputHtml(idx, ''));
                reindexSoalEssay();
            });

            $(document).on('click', '.btn-remove-soal', function() {
                if ($('#soalEssayWrap .soal-essay-item').length <= 2) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Minimal 2 soal',
                        text: 'Tugas essay harus berisi minimal 2 soal.'
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
                }).then(r => {
                    if (r.isConfirmed) form.submit();
                });
            });
        });
    </script>
@endsection
