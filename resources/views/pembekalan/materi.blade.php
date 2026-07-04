@extends('adminlte::page')

@section('title', 'Data Materi Pembekalan')

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Data Materi Pembekalan</h1>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid pt-3">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('pembekalan.materi') }}">
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
                        <div class="col-md-4 mb-2">
                            <label class="mb-1">Cari Topik</label>
                            <input type="text" name="keyword" class="form-control form-control-sm"
                                placeholder="misal: etika kerja" value="{{ $filters['keyword'] }}">
                        </div>
                        <div class="col-md-2 mb-2 d-flex">
                            <button type="submit" class="btn btn-sm btn-primary mr-1 w-100">Filter</button>
                            <a href="{{ route('pembekalan.materi') }}"
                                class="btn btn-sm btn-outline-secondary w-100">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white d-flex align-items-center">
                <div>
                    <h5 class="mb-0">Daftar Materi Pembekalan</h5>
                    <small class="text-muted">{{ $materi->count() }} data</small>
                </div>
                @can('panitia')
                    <button type="button" class="btn btn-sm btn-primary ml-auto" id="btnOpenMateriModal">Tambah Materi</button>
                @endcan
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped datatable-materi">
                    <thead>
                        <tr>
                            <th style="width: 90px;">Tanggal</th>
                            <th style="width: 180px;">Topik</th>
                            <th>Konten</th>
                            <th style="width: 180px;">Catatan</th>
                            @can('panitia')
                                <th style="width: 130px;">Aksi</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($materi as $item)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($item->tanggal_materi)->format('d-m-Y') }}</td>
                                <td>{{ $item->topik }}</td>
                                <td>
                                    @if (!empty($item->isi_materi))
                                        <div>
                                            <span class="badge badge-secondary">TEXT</span>
                                            {{ \Illuminate\Support\Str::limit($item->isi_materi, 180) }}
                                        </div>
                                    @endif

                                    @if ($item->file_pdf_path)
                                        <div class="mt-1">
                                            <span class="badge badge-danger">PDF</span>
                                            <a href="{{ asset('storage/' . $item->file_pdf_path) }}" target="_blank">Lihat
                                                PDF</a>
                                        </div>
                                    @endif

                                    @if (!empty($item->video_url))
                                        <div class="mt-1">
                                            <span class="badge badge-info">VIDEO</span>
                                            <a href="{{ $item->video_url }}" target="_blank">Buka Video</a>
                                        </div>
                                    @endif

                                    @if (empty($item->isi_materi) && empty($item->file_pdf_path) && empty($item->video_url))
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if (!empty($item->catatan))
                                        {{ \Illuminate\Support\Str::limit($item->catatan, 120) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                @can('panitia')
                                    <td>
                                        <button type="button" class="btn btn-xs btn-warning mb-1 btn-edit-materi"
                                            data-id="{{ $item->id }}" data-tanggal_materi="{{ $item->tanggal_materi }}"
                                            data-topik="{{ e($item->topik) }}"
                                            data-isi_materi="{{ e($item->isi_materi ?? '') }}"
                                            data-video_url="{{ $item->video_url ?? '' }}"
                                            data-catatan="{{ e($item->catatan ?? '') }}"
                                            data-file_pdf_url="{{ $item->file_pdf_path ? asset('storage/' . $item->file_pdf_path) : '' }}">
                                            Edit
                                        </button>

                                        <form method="POST" action="{{ route('pembekalan.materi.destroy', $item->id) }}"
                                            class="d-inline form-delete-materi">
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
            <div class="modal fade" id="materiModal" tabindex="-1" role="dialog" aria-labelledby="materiModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="materiModalLabel">Tambah Materi Pembekalan</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST" enctype="multipart/form-data" action="{{ route('pembekalan.materi.store') }}"
                            id="materiForm">
                            <div class="modal-body">
                                @csrf
                                <input type="hidden" name="edit_id" id="edit_id" value="{{ old('edit_id') }}">
                                <input type="hidden" name="_method" id="materiFormMethod" value="POST">

                                <div class="form-row">
                                    <div class="col-md-6 mb-2">
                                        <label class="mb-1">Tanggal</label>
                                        <input type="date" name="tanggal_materi" class="form-control form-control-sm"
                                            value="{{ old('tanggal_materi', now()->toDateString()) }}" required>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <label class="mb-1">Format Materi</label>
                                        <input type="text" class="form-control form-control-sm"
                                            value="Bisa gabungan: Text + PDF + Video" disabled>
                                    </div>

                                    <div class="col-md-12 mb-2">
                                        <label class="mb-1">Materi/Topik</label>
                                        <input type="text" name="topik" class="form-control form-control-sm"
                                            value="{{ old('topik') }}" placeholder="Masukkan topik materi pembekalan"
                                            required>
                                    </div>

                                    <div class="col-md-12 mb-2">
                                        <label class="mb-1">Isi Materi (Text)</label>
                                        <textarea name="isi_materi" rows="4" class="form-control form-control-sm"
                                            placeholder="Tuliskan isi materi pembekalan...">{{ old('isi_materi') }}</textarea>
                                    </div>

                                    <div class="col-md-12 mb-2">
                                        <label class="mb-1">File Materi (PDF)</label>
                                        <input type="file" name="materi_file"
                                            class="form-control form-control-sm materi-file-input" accept="application/pdf">
                                        <small class="text-muted d-none mt-1" id="currentPdfInfo">File saat ini:
                                            <a href="#" target="_blank" id="currentPdfLink">Lihat PDF</a>
                                        </small>
                                        <div class="form-check mt-2 d-none" id="removePdfWrap">
                                            <input class="form-check-input" type="checkbox" name="hapus_pdf_lama"
                                                id="hapus_pdf_lama" value="1">
                                            <label class="form-check-label" for="hapus_pdf_lama">Hapus PDF lama</label>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mb-2">
                                        <label class="mb-1">URL Video</label>
                                        <input type="url" name="video_url" class="form-control form-control-sm"
                                            value="{{ old('video_url') }}"
                                            placeholder="https://youtube.com/... atau link video lainnya">
                                    </div>

                                    <div class="col-md-12 mb-2">
                                        <label class="mb-1">Catatan</label>
                                        <textarea name="catatan" rows="2" class="form-control form-control-sm"
                                            placeholder="Catatan tambahan (opsional)">{{ old('catatan') }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    data-dismiss="modal">Tutup</button>
                                <button type="submit" class="btn btn-sm btn-primary" id="btnSubmitMateri">Tambah
                                    Materi</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan
    </div>
@endsection

@section('plugins.Datatables', true)

@section('css')
    <style>
        .materi-file-input {
            height: calc(1.8125rem + 2px);
            padding-top: .2rem;
            padding-bottom: .2rem;
        }
    </style>
@endsection

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

            const materiForm = $('#materiForm');
            const materiModal = $('#materiModal');
            const materiTitle = $('#materiModalLabel');
            const submitBtn = $('#btnSubmitMateri');
            const methodInput = $('#materiFormMethod');
            const editIdInput = $('#edit_id');
            const storeUrl = @json(route('pembekalan.materi.store'));
            const updateUrlTemplate = @json(route('pembekalan.materi.update', ['materi' => '__ID__']));
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
                    title: 'Gagal menyimpan materi',
                    html: errorMessages.join('<br>'),
                });
            }

            function setValue(name, value) {
                const el = materiForm.find('[name="' + name + '"]');
                el.val(value || '');
            }

            function resetModalToAdd() {
                materiTitle.text('Tambah Materi Pembekalan');
                submitBtn.text('Tambah Materi');
                materiForm.attr('action', storeUrl);
                methodInput.val('POST');
                editIdInput.val('');
                $('#currentPdfInfo').addClass('d-none');
                $('#currentPdfLink').attr('href', '#');
                $('#removePdfWrap').addClass('d-none');
                $('#hapus_pdf_lama').prop('checked', false);

                if (!@json($errors->any())) {
                    materiForm[0].reset();
                    setValue('tanggal_materi', @json(now()->toDateString()));
                }
            }

            function setModalToEdit(data) {
                materiTitle.text('Edit Materi Pembekalan');
                submitBtn.text('Simpan Perubahan');
                materiForm.attr('action', updateUrlTemplate.replace('__ID__', data.id));
                methodInput.val('PUT');
                editIdInput.val(data.id);

                setValue('tanggal_materi', data.tanggal_materi);
                setValue('topik', data.topik || '');
                setValue('isi_materi', data.isi_materi || '');
                setValue('video_url', data.video_url || '');
                setValue('catatan', data.catatan || '');

                if (data.file_pdf_url) {
                    $('#currentPdfLink').attr('href', data.file_pdf_url);
                    $('#currentPdfInfo').removeClass('d-none');
                    $('#removePdfWrap').removeClass('d-none');
                } else {
                    $('#currentPdfInfo').addClass('d-none');
                    $('#currentPdfLink').attr('href', '#');
                    $('#removePdfWrap').addClass('d-none');
                }

                $('#hapus_pdf_lama').prop('checked', false);
            }

            $('#btnOpenMateriModal').on('click', function() {
                resetModalToAdd();
                materiModal.modal('show');
            });

            $(document).on('click', '.btn-edit-materi', function() {
                const btn = $(this);
                setModalToEdit({
                    id: btn.data('id'),
                    tanggal_materi: btn.data('tanggal_materi') || '',
                    topik: btn.data('topik') || '',
                    isi_materi: btn.data('isi_materi') || '',
                    video_url: btn.data('video_url') || '',
                    catatan: btn.data('catatan') || '',
                    file_pdf_url: btn.data('file_pdf_url') || ''
                });
                materiModal.modal('show');
            });

            $(document).on('submit', '.form-delete-materi', function(e) {
                e.preventDefault();
                const form = this;

                Swal.fire({
                    title: 'Hapus materi?',
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
                        materiTitle.text('Edit Materi Pembekalan');
                        submitBtn.text('Simpan Perubahan');
                        methodInput.val('PUT');
                        materiForm.attr('action', updateUrlTemplate.replace('__ID__', $('#edit_id').val()));
                    } else {
                        resetModalToAdd();
                    }
                    $('#materiModal').modal('show');
                @endif
            @endcan

            $('.datatable-materi').DataTable({
                pageLength: 10,
                lengthChange: true,
                ordering: true,
                searching: true,
                responsive: true,
                autoWidth: false,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
                    emptyTable: 'Belum ada data materi pembekalan.'
                }
            });
        });
    </script>
@endsection
