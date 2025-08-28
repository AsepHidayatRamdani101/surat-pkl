@extends('adminlte::page')

@section('title', 'Surat Izin Orang Tua')

@section('content')
    <div class="container pt-4">
        <div class="card">
            <div class="card-header">
                <h4>Data Monitorting PKL</h4>
            </div>
            <div class="card-body">
                <table id="monitoringTable" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th>Nama Perusahaan</th>
                            <th>Nama Pembimbing</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>

                <!-- Modal Form -->
                <div class="modal fade" id="modalForm" tabindex="-1" role="dialog">

                    <div class="modal-dialog">
                        <form id="formTempatPkl" enctype="multipart/form-data">
                            @csrf

                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalFormLabel">Form Tempat PKL</h5>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <input type="hidden" id="id" name="id">
                                <div class="modal-body">
                                    <input type="hidden" name="id" id="id">
                                    <div class="mb-2">
                                        <label for="siswa_id">Pilih Siswa</label>
                                        <select name="siswa_id" id="siswa_id" class="form-control">
                                            <option value="" selected disabled>Pilih Siswa</option>
                                            @foreach ($siswa as $s)
                                                <option value="{{ $s->id }}">{{ $s->nama_siswa }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-2">
                                        <label for="perusahaan_id" class="form-label">Perusahaan</label>
                                        <select name="perusahaan_id" id="perusahaan_id" class="form-control">
                                            <option value="" selected disabled>Pilih Perusahaan</option>
                                            @foreach ($perusahaan as $p)
                                                <option value="{{ $p->id }}">{{ $p->nama_perusahaan }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-2">
                                        <label for="pembimbing_id" class="form-label">Pilih Pembimbing</label>
                                        <select name="pembimbing_id" id="pembimbing_id" class="form-control">
                                            <option value="" selected disabled>Pilih Pembimbing</option>
                                            @foreach ($pembimbing as $p)
                                                <option value="{{ $p->id }}">{{ $p->nama_pembimbing }}</option>
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    <style>
        .select2-container--bootstrap-5 .select2-selection--single {
            height: 38px;
            /* padding: 6px 12px; */
            border-radius: 0.375rem;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple {
            min-height: 38px;
            /* padding: 6px 12px; */
            border-radius: 0.375rem;
        }

        .select2-selection__choice {
            background-color: #FFC40175;
            color: white;
            /* padding: 3px 8px; */
            margin-top: 4px;
            font-size: 14px
        }
    </style>
@endsection

@section('js')

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            })



            let table = $('#monitoringTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('monitoring.index') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'siswa.nama_siswa', // ✅ sesuai key backend
                        name: 'siswa.nama_siswa'
                    },
                    {
                        data: 'perusahaan.nama_perusahaan', // ✅ sesuai key backend
                        name: 'perusahaan.nama_perusahaan'
                    },
                    {
                        data: 'pembimbing.nama_pembimbing', // ✅ sesuai key backend
                        name: 'pembimbing.nama_pembimbing'
                    },
                    {
                        data: 'aksi',
                        name: 'aksi',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $(document).on('click', '.btn-edit', function() {
                let id = $(this).data('id');
                // console.log(id);

                $.ajax({
                    url: `/tempat-pkl/${id}/edit`,
                    type: 'GET',
                    success: function(data) {
                        console.log(data);

                        $('#id').val(data.id);
                        $('#siswa_id').val(data.siswa_id).trigger('change');
                        $('#perusahaan_id').val(data.perusahaan_id).trigger('change');
                        $('#pembimbing_id').val(data.pembimbing_id).trigger('change');
                        // $('#modalFormLabel').html('Edit Data Tempat PKL');
                    },
                });

                $('#modalForm').modal('show');
            });

            $(document).on('click', '.btn-simpan', function() {

                let formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('_method', 'PUT');
                formData.append('siswa_id', $('#siswa_id').val());
                formData.append('perusahaan_id', $('#perusahaan_id').val());
                formData.append('pembimbing_id', $('#pembimbing_id').val());
                formData.append('id', $('#id').val());

                $.ajax({
                    url: `/tempat-pkl/${$('#id').val()}/editPembimbing`,
                    type: 'PUT',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(data) {
                        console.log(data);

                        $('#modalForm').modal('hide');
                        $('#monitoringTable').DataTable().ajax.reload();
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                    }
                });

            });

        });

        //btn-edit
    </script>
@endsection
