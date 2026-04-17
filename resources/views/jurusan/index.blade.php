@extends('adminlte::page')

@section('title', 'Data Jurusan')

@section('content')
    <div class="container pt-4">
        <div class="card">
            <div class="card-header">
                <h4 class="m-0">Data Jurusan</h4>
                <div class="float-right">
                    <button class="btn btn-sm btn-primary" id="btnTambah">Tambah Data</button>
                    <button class="btn btn-sm btn-success" id="btnImport">Import Data</button>
                </div>
            </div>
            <div class="card-body">
                <table id="jurusanTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Jurusan</th>
                            <th>Nama Jurusan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>

                <!-- Modal Import -->
                <div class="modal fade" id="modalImport" tabindex="-1" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Import Data Jurusan</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <p class="text-muted mb-3">
                                    <a href="{{ route('jurusan.downloadTemplate') }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-download"></i> Download Format Template
                                    </a>
                                </p>
                                <form id="formImportJurusan" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="file">File Excel (Format: Nama Jurusan | Kode Jurusan)</label>
                                        <input type="file" name="file" id="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                                        <small class="form-text text-muted">Format kolom: Kolom A = Nama Jurusan, Kolom B = Kode Jurusan</small>
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
                        <form id="formJurusan">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalFormLabel">Form Jurusan</h5>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="id" id="id">
                                    <div class="form-group">
                                        <label for="kode_jurusan">Kode Jurusan</label>
                                        <input type="text" name="kode_jurusan" id="kode_jurusan" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="nama_jurusan">Nama Jurusan</label>
                                        <input type="text" name="nama_jurusan" id="nama_jurusan" class="form-control" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-success" id="btnSimpan">Simpan</button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
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
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            })

            let table = $('#jurusanTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('jurusan.data') }}',
                columns: [
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'kode_jurusan',
                        name: 'kode_jurusan'
                    },
                    {
                        data: 'nama_jurusan',
                        name: 'nama_jurusan'
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
                $('#formJurusan')[0].reset();
                $('#id').val('');
                $('#modalFormLabel').text('Tambah Jurusan');
                $('#modalForm').modal('show');
            });

            // Import Data
            $('#btnImport').click(function() {
                $('#formImportJurusan')[0].reset();
                $('#modalImport').modal('show');
            });

            // Simpan Data
            $('#btnSimpan').click(function() {
                let id = $('#id').val();
                let url = id ? '{{ route('jurusan.update', '') }}/' + id : '{{ route('jurusan.store') }}';
                let method = id ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    type: method,
                    data: $('#formJurusan').serialize(),
                    success: function(response) {
                        $('#modalForm').modal('hide');
                        table.ajax.reload();
                        alert(response.message);
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON.errors;
                        let message = 'Error: ';
                        for (let key in errors) {
                            message += errors[key][0] + '\n';
                        }
                        alert(message);
                    }
                });
            });

            // Import File
            $('#btnImportFile').click(function() {
                let formData = new FormData($('#formImportJurusan')[0]);
                $.ajax({
                    url: '{{ route('jurusan.import') }}',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        $('#modalImport').modal('hide');
                        table.ajax.reload();
                        alert(response.message);
                    },
                    error: function(xhr) {
                        let response = xhr.responseJSON;
                        let message = response.message;
                        if (response.errors && response.errors.length > 0) {
                            message += '\n\nDetail Error:\n';
                            response.errors.forEach(function(error) {
                                message += error + '\n';
                            });
                        }
                        alert(message);
                    }
                });
            });

            // Edit Data
            $(document).on('click', '.btnEdit', function() {
                let id = $(this).data('id');
                $.ajax({
                    url: `jurusan/${id}/edit`,
                    type: 'GET',
                    success: function(data) {
                        $('#id').val(data.id);
                        $('#kode_jurusan').val(data.kode_jurusan);
                        $('#nama_jurusan').val(data.nama_jurusan);
                        $('#modalFormLabel').text('Edit Jurusan');
                        $('#modalForm').modal('show');
                    }
                });
            });

            // Hapus Data
            $(document).on('click', '.btnHapus', function() {
                let id = $(this).data('id');
                if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                    $.ajax({
                        url: '{{ route('jurusan.destroy', '') }}/' + id,
                        type: 'DELETE',
                        success: function(response) {
                            table.ajax.reload();
                            alert(response.message);
                        },
                        error: function(xhr) {
                            alert('Error: ' + xhr.responseJSON.message);
                        }
                    });
                }
            });
        });
    </script>
@endsection
