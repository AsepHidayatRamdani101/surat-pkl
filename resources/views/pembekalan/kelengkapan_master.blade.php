@extends('adminlte::page')

@section('title', 'Master Kelengkapan Siswa')

@section('content-header')
    <div class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h1 class="m-0">Master Kelengkapan Siswa</h1>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid pt-2">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0">Form Daftar Kelengkapan</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('pembekalan.kelengkapan.master.store') }}" id="kelengkapanMasterForm">
                    @csrf
                    <input type="hidden" name="_method" id="kelengkapanMethod" value="POST">
                    <div class="form-row">
                        <div class="col-md-4 mb-2">
                            <label class="mb-1">Nama Item</label>
                            <input type="text" name="nama_item" id="namaItemInput" class="form-control form-control-sm"
                                value="{{ old('nama_item') }}" required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="mb-1">Deskripsi</label>
                            <input type="text" name="deskripsi" id="deskripsiItemInput"
                                class="form-control form-control-sm" value="{{ old('deskripsi') }}" placeholder="Opsional">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="mb-1">Urutan</label>
                            <input type="number" name="urutan" id="urutanItemInput" class="form-control form-control-sm"
                                value="{{ old('urutan', 0) }}" min="0">
                        </div>
                        <div class="col-md-2 mb-2 d-flex align-items-center">
                            <div class="custom-control custom-switch mt-4">
                                <input type="checkbox" class="custom-control-input" id="isActiveInput" name="is_active"
                                    value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="isActiveInput">Aktif</label>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <button type="submit" class="btn btn-sm btn-primary" id="saveKelengkapanBtn">
                            <i class="fas fa-save mr-1"></i> Simpan Item
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary d-none"
                            id="cancelEditKelengkapanBtn">
                            Batal Edit
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h5 class="mb-0">Daftar Kelengkapan ({{ $items->count() }} item)</h5>
            </div>
            <div class="card-body table-responsive">
                <table id="kelengkapanMasterTable" class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th style="width: 70px;">Urutan</th>
                            <th style="width: 220px;">Nama Item</th>
                            <th>Deskripsi</th>
                            <th style="width: 90px;">Status</th>
                            <th style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td>{{ $item->urutan }}</td>
                                <td>{{ $item->nama_item }}</td>
                                <td>{{ $item->deskripsi ?: '-' }}</td>
                                <td>
                                    <span class="badge badge-{{ $item->is_active ? 'success' : 'secondary' }}">
                                        {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-xs btn-warning btn-edit-kelengkapan"
                                        data-id="{{ $item->id }}" data-nama_item="{{ e($item->nama_item) }}"
                                        data-deskripsi="{{ e($item->deskripsi ?? '') }}" data-urutan="{{ $item->urutan }}"
                                        data-is_active="{{ $item->is_active ? '1' : '0' }}">
                                        Edit
                                    </button>
                                    <form method="POST"
                                        action="{{ route('pembekalan.kelengkapan.master.destroy', $item->id) }}"
                                        class="d-inline form-delete-kelengkapan">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">Belum ada daftar kelengkapan siswa.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
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
                    title: 'Gagal menyimpan data',
                    html: errorMessages.join('<br>'),
                });
            }

            if ($.fn.DataTable) {
                $('#kelengkapanMasterTable').DataTable({
                    pageLength: 10,
                    responsive: true,
                    autoWidth: false,
                    ordering: true,
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
                        emptyTable: 'Belum ada daftar kelengkapan siswa.'
                    }
                });
            }

            const form = $('#kelengkapanMasterForm');
            const methodInput = $('#kelengkapanMethod');
            const saveBtn = $('#saveKelengkapanBtn');
            const cancelBtn = $('#cancelEditKelengkapanBtn');
            const defaultAction = @json(route('pembekalan.kelengkapan.master.store'));
            const updateUrlTemplate = @json(route('pembekalan.kelengkapan.master.update', ['kelengkapanSiswaItem' => '__ID__']));

            const resetFormState = () => {
                form.attr('action', defaultAction);
                methodInput.val('POST');
                form.trigger('reset');
                $('#isActiveInput').prop('checked', true);
                $('#urutanItemInput').val(0);
                saveBtn.html('<i class="fas fa-save mr-1"></i> Simpan Item');
                cancelBtn.addClass('d-none');
            };

            $(document).on('click', '.btn-edit-kelengkapan', function() {
                const button = $(this);
                form.attr('action', updateUrlTemplate.replace('__ID__', button.data('id')));
                methodInput.val('PUT');
                $('#namaItemInput').val(button.data('nama_item'));
                $('#deskripsiItemInput').val(button.data('deskripsi'));
                $('#urutanItemInput').val(button.data('urutan'));
                $('#isActiveInput').prop('checked', String(button.data('is_active')) === '1');
                saveBtn.html('<i class="fas fa-save mr-1"></i> Update Item');
                cancelBtn.removeClass('d-none');
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });

            cancelBtn.on('click', resetFormState);

            $(document).on('submit', '.form-delete-kelengkapan', function(event) {
                event.preventDefault();
                const formDelete = this;

                Swal.fire({
                    icon: 'warning',
                    title: 'Hapus item?',
                    text: 'Item kelengkapan akan dihapus dari master data.',
                    showCancelButton: true,
                    confirmButtonText: 'Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        formDelete.submit();
                    }
                });
            });
        });
    </script>
@endsection
