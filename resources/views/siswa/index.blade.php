@extends('adminlte::page')

@section('title', 'Surat Izin Orang Tua')

@section('content')
    <div class="container pt-4">
        <div class="card">
            <div class="card-header">
                <h4>Data Tempat PKL</h4>
                <button class="btn btn-sm btn-primary ms-auto" id="btnTambah">Tambah Data</button>
                <button class="btn btn-sm btn-success" id="btnImport">Import Data</button>
            </div>
            <div class="card-body">
                <table id="siswaTable" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Jurusan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>

                <!-- Modal Import -->
                <div class="modal fade" id="modalImport" tabindex="-1" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalImportLabel">Import Data Siswa</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <form id="formImportSiswa" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="file">File Excel</label>
                                        <input type="file" name="file" id="file" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <button type="button" class="btn btn-primary" id="btnImportSiswa">Import</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Form -->
                <div class="modal fade" id="modalForm" tabindex="-1" role="dialog">
                    <div class="modal-dialog">
                        <form id="formSiswa" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalFormLabel">Form Siswa</h5>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="id" id="id">
                                    <div class="form-group">
                                        <label for="nis">NIS</label>
                                        <input type="text" name="nis" id="nis" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="nama_siswa">Nama Siswa</label>
                                        <input type="text" name="nama_siswa" id="nama_siswa" class="form-control"
                                            required>
                                    </div>
                                    <div class="form-group">
                                        <label for="kelas_id">Kelas</label>
                                        <select name="kelas_id" id="kelas_id" class="form-control" required>
                                            <option value="">Pilih Kelas</option>
                                            @foreach ($kelas as $row)
                                                <option value="{{ $row->id }}">{{ $row->nama_kelas }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-success btn-simpan">Simpan</button>
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



            let table = $('#siswaTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('siswa.data') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'nis',
                        name: 'nis'
                    },
                    {
                        data: 'nama_siswa',
                        name: 'nama_siswa'
                    },
                    {
                        data: 'kelas.nama_kelas',
                        name: 'kelas.nama_kelas'
                    },
                    {
                        data: 'kelas.jurusan.nama_jurusan',
                        name: 'kelas.jurusan.nama_jurusan'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'aksi',
                        name: 'aksi',
                        orderable: false,
                        searchable: false
                    },
                ]
            });

            $('#btnTambah').click(function() {
                $('#modalForm').modal('show');
                $('#modalFormLabel').html('Tambah Data Siswa');
                $('#formSiswa').attr('action', '{{ route('siswa.store') }}');
                $('#formSiswa').trigger('reset');
            });

            $(document).on('click', '.btnEdit', function() {
                let id = $(this).data('id');
                let url = '{{ route('siswa.edit', ':id') }}';
                url = url.replace(':id', id);
                $.get(url, function(response) {
                    $('#modalForm').modal('show');
                    $('#modalFormLabel').html('Edit Data Siswa');
                    $('#formSiswa').attr('action', '{{ route('siswa.update', ':id') }}');
                    $('#formSiswa').attr('action', $('#formSiswa').attr('action').replace(':id',
                        response.id));
                    $('#nis').val(response.nis);
                    $('#nama_siswa').val(response.nama_siswa);
                    $('#kelas_id').val(response.kelas_id);
                    $('#id').val(response.id);

                });
            });

            $('#btnImport').click(function() {
                $('#modalImport').modal('show');
            });

            $('#btnImportSiswa').click(function() {
                let formData = new FormData($('#formImportSiswa')[0]);
                $.ajax({
                    url: '{{ route('siswa.import') }}',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function() {

                        $('#modalImport').modal('hide');
                        table.ajax.reload();
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);

                    }
                });



            });

            $(document).on('click', '.btnHapus', function() {
                let id = $(this).data('id');
                let url = '{{ route('siswa.destroy', ':id') }}';
                url = url.replace(':id', id);
                if (confirm('Yakin hapus data ini?')) {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        success: function() {
                            table.ajax.reload();
                        }
                    });
                }
            });

            $(document).on('click', '.btn-simpan', function() {

                let id = $('#id').val();
                let url = id ? '{{ route('siswa.update', ':id') }}'.replace(':id', id) :
                    '{{ route('siswa.store') }}';
                let method = id ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    type: method,
                    data: {
                        _token: '{{ csrf_token() }}',
                        nis: $('#nis').val(),
                        nama_siswa: $('#nama_siswa').val(),
                        kelas_id: $('#kelas_id').val(),
                    },
                    success: function() {
                        $('#modalForm').modal('hide');
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                    }
                });
            });

        });
    </script>
@endsection
