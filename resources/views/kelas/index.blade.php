@extends('adminlte::page')

@section('title', 'Data Kelas')

@section('content')
    <div class="container pt-4">
        <div class="card">
            <div class="card-header">
                <h4 class="m-0">Data Kelas</h4>
                <div class="float-right">
                    <div class="custom-control custom-switch d-inline-block mr-2 align-middle">
                        <input type="checkbox" class="custom-control-input" id="toggleSwitchKelas">
                        <label class="custom-control-label" for="toggleSwitchKelas" id="toggleSwitchKelasLabel">Switch XII ke
                            XI</label>
                    </div>
                    <button class="btn btn-sm btn-primary" id="btnTambah">Tambah Data</button>
                    <button class="btn btn-sm btn-success" id="btnImport">Import Data</button>
                </div>
            </div>
            <div class="card-body">
                <table id="kelasTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kelas</th>
                            <th>Jurusan</th>
                            <th>Tingkat</th>
                            <th>Jumlah Rombel</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>

                <!-- Modal Import -->
                <div class="modal fade" id="modalImport" tabindex="-1" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Import Data Kelas</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <p class="text-muted mb-3">
                                    <a href="{{ route('kelas.downloadTemplate') }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-download"></i> Download Format Template
                                    </a>
                                </p>
                                <form id="formImportKelas" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="file">File Excel (Format: Nama Kelas | Nama Jurusan | Tingkat |
                                            Jumlah Rombel)</label>
                                        <input type="file" name="file" id="file" class="form-control"
                                            accept=".xlsx,.xls,.csv" required>
                                        <small class="form-text text-muted">Format kolom: Kolom A = Nama Kelas, Kolom B =
                                            Nama Jurusan, Kolom C = Tingkat (11-13), Kolom D = Jumlah Rombel</small>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" id="btnImportFile">Import</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Form -->
                <div class="modal fade" id="modalForm" tabindex="-1" role="dialog">
                    <div class="modal-dialog">
                        <form id="formKelas">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalFormLabel">Form Kelas</h5>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="id" id="id">
                                    <div class="form-group">
                                        <label for="nama_kelas">Nama Kelas</label>
                                        <input type="text" name="nama_kelas" id="nama_kelas" class="form-control"
                                            required>
                                    </div>

                                    <div class="form-group">
                                        <label for="jurusan_id">Jurusan</label>
                                        <select name="jurusan_id" id="jurusan_id" class="form-control" required>
                                            <option value="">Pilih Jurusan</option>
                                            @foreach ($jurusan as $item)
                                                <option value="{{ $item->id }}">{{ $item->nama_jurusan }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="tingkat">Tingkat</label>
                                        <select name="tingkat" id="tingkat" class="form-control" required>
                                            <option value="">Pilih Tingkat</option>
                                            <option value="11">11 (XI)</option>
                                            <option value="12">12 (XII)</option>
                                            <option value="13">13 (XIII)</option>
                                        </select>
                                    </div>

                                    <div class="form-group mb-0">
                                        <label for="jumlah_rombel">Jumlah Rombel</label>
                                        <input type="number" name="jumlah_rombel" id="jumlah_rombel" class="form-control"
                                            min="0" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                    <button type="button" class="btn btn-primary" id="btnSimpan">Simpan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
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

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            })

            let table = $('#kelasTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('kelas.data') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'nama_kelas',
                        name: 'nama_kelas'
                    },
                    {
                        data: 'jurusan_nama',
                        name: 'jurusan_nama'
                    },
                    {
                        data: 'tingkat',
                        name: 'tingkat'
                    },
                    {
                        data: 'jumlah_rombel',
                        name: 'jumlah_rombel'
                    },
                    {
                        data: 'aksi',
                        name: 'aksi',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // Tambah Data
            $('#btnTambah').click(function() {
                $('#formKelas')[0].reset();
                $('#id').val('');
                $('#modalFormLabel').text('Tambah Kelas');
                $('#modalForm').modal('show');
            });

            // Import Data
            $('#btnImport').click(function() {
                $('#formImportKelas')[0].reset();
                $('#modalImport').modal('show');
            });

            function updateToggleSwitchKelasLabel() {
                let isChecked = $('#toggleSwitchKelas').is(':checked');
                $('#toggleSwitchKelasLabel').text(isChecked ? 'Switch XI ke XII' : 'Switch XII ke XI');
            }

            function showSuccess(message) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: message,
                    timer: 1800,
                    showConfirmButton: false
                });
            }

            function showError(message) {
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi kesalahan',
                    text: message
                });
            }

            updateToggleSwitchKelasLabel();

            $('#toggleSwitchKelas').change(async function() {
                let isChecked = $(this).is(':checked');
                let url = isChecked ? '{{ route('kelas.switch-xi-xii') }}' :
                    '{{ route('kelas.switch-xii-xi') }}';
                let confirmText = isChecked ?
                    'Ubah semua kelas XI menjadi XII dan tingkat 11 menjadi 12?' :
                    'Ubah semua kelas XII menjadi XI dan tingkat 12 menjadi 11?';

                updateToggleSwitchKelasLabel();

                let result = await Swal.fire({
                    icon: 'question',
                    title: 'Konfirmasi',
                    text: confirmText,
                    showCancelButton: true,
                    confirmButtonText: 'Ya, ubah',
                    cancelButtonText: 'Batal'
                });

                if (!result.isConfirmed) {
                    $(this).prop('checked', !isChecked);
                    updateToggleSwitchKelasLabel();
                    return;
                }

                $.ajax({
                    url: url,
                    type: 'POST',
                    success: function(response) {
                        table.ajax.reload();
                        showSuccess(response.message + ' Total data: ' + response.jumlah);
                    },
                    error: function(xhr) {
                        $('#toggleSwitchKelas').prop('checked', !isChecked);
                        updateToggleSwitchKelasLabel();
                        showError(xhr.responseJSON?.message ||
                            'Terjadi kesalahan saat mengubah data kelas.');
                    }
                });
            });

            // Simpan Data
            $('#btnSimpan').click(function() {
                let id = $('#id').val();
                let url = id ? '{{ route('kelas.update', '') }}/' + id : '{{ route('kelas.store') }}';
                let method = id ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    type: method,
                    data: $('#formKelas').serialize(),
                    success: function(response) {
                        $('#modalForm').modal('hide');
                        table.ajax.reload();
                        showSuccess(response.message);
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON?.errors || {};
                        let message = 'Error: ';
                        for (let key in errors) {
                            message += errors[key][0] + '\n';
                        }
                        showError(message === 'Error: ' ?
                            'Terjadi kesalahan saat menyimpan data.' : message);
                    }
                });
            });

            // Import File
            $('#btnImportFile').click(function() {
                let formData = new FormData($('#formImportKelas')[0]);
                $.ajax({
                    url: '{{ route('kelas.import') }}',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        $('#modalImport').modal('hide');
                        table.ajax.reload();
                        showSuccess(response.message);
                    },
                    error: function(xhr) {
                        let response = xhr.responseJSON || {};
                        let message = response.message || 'Terjadi kesalahan saat import data.';
                        if (response.errors && response.errors.length > 0) {
                            message += '\n\nDetail Error:\n';
                            response.errors.forEach(function(error) {
                                message += error + '\n';
                            });
                        }
                        showError(message);
                    }
                });
            });

            // Edit Data
            $(document).on('click', '.btnEdit', function() {
                let id = $(this).data('id');
                console.log(id);

                $.ajax({
                    url: `kelas/${id}/edit`,
                    type: 'GET',
                    success: function(data) {
                        $('#id').val(data.id);
                        $('#nama_kelas').val(data.nama_kelas);
                        $('#jurusan_id').val(data.jurusan_id);
                        $('#tingkat').val(data.tingkat);
                        $('#jumlah_rombel').val(data.jumlah_rombel);
                        $('#modalFormLabel').text('Edit Kelas');
                        $('#modalForm').modal('show');
                    }
                });
            });

            // Hapus Data
            $(document).on('click', '.btnHapus', async function() {
                let id = $(this).data('id');
                let result = await Swal.fire({
                    icon: 'warning',
                    title: 'Hapus data?',
                    text: 'Apakah Anda yakin ingin menghapus data ini?',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal'
                });

                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: '{{ route('kelas.destroy', '') }}/' + id,
                    type: 'DELETE',
                    success: function(response) {
                        table.ajax.reload();
                        showSuccess(response.message);
                    },
                    error: function(xhr) {
                        showError(xhr.responseJSON?.message ||
                            'Terjadi kesalahan saat menghapus data.');
                    }
                });
            });
        });
    </script>
@endsection
