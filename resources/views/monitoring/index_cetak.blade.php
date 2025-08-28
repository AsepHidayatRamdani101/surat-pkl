@extends('adminlte::page')

@section('title', 'Monitoring PKL')

@section('content')
    <div class="container pt-4">
        <div class="card">
            <div class="card-header">
                <h4>Data Monitoring PKL</h4>
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

            </div>

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

            $('.select2').select2({
                theme: 'bootstrap-5',
                placeholder: 'Pilih salah satu atau lebih...',
                allowClear: true,
                dropdownParent: $('#modalForm') // jika select2 berada dalam modal
            });

            let table = $('#monitoringTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('monitoring.index_cetak') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'siswa',
                        name: 'siswa'
                    },

                    {
                        data: 'perusahaan',
                        name: 'perusahaan'
                    },
                    {
                        data: 'pembimbing',
                        name: 'pembimbing'
                    },
                    {
                        data: 'aksi',
                        name: 'aksi',
                        orderable: false,
                        searchable: false
                    },
                ]
            });

            //btn-tambah-pembimbing
            $(document).on('click', '.btn-edit-pembimbing', function() {

                let id = $(this).data('id');
                // alert(id);
                let pembimbing = $(this).data('pembimbing');
                // console.log(id);

                $.ajax({
                    url: `/monitoring/lihatdata/${id}`,
                    type: 'GET',
                    success: function(data) {
                        console.log(data);

                        $('#id').val(data.perusahaan_id);
                        $('#siswa_id').val(data.siswa_id).trigger('change');
                        $('#perusahaan_id').val(data.perusahaan_id).trigger('change');
                        $('#pembimbing_id').val(data.pembimbing_id).trigger('change');
                        // $('#modalFormLabel').html('Edit Data Tempat PKL');
                    },
                });

                $('#modalForm').modal('show');
            });

            //btn-simpan
            $(document).on('click', '.btn-simpan', function() {
                alert($('#pembimbing_id').val());
                $.ajax({
                    url: `/tempat-pkl/${$('#id').val()}/editPembimbing`,
                    type: 'PUT',
                    data: {
                        _token: '{{ csrf_token() }}',
                        perusahaan_id: $('#perusahaan_id').val(),
                        pembimbing_id: $('#pembimbing_id').val()
                    },
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

            // Lakukan sesuatu dengan perusahaanId, misalnya membuka modal
        });


        //btn-edit
    </script>
@endsection
