@extends('adminlte::page')

@section('title', 'Surat Izin Orang Tua')

@section('content')
    <div class="container-fluid pt-4">
        <div class="card app-card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center my-1">
                    <span class="card-title-icon"><i class="fas fa-clipboard-check"></i></span>
                    <div>
                        <h4>Data Monitoring PKL</h4>
                        <small>Pantau penempatan siswa dan pembimbing monitoring PKL</small>
                    </div>
                </div>
                <div class="my-1 d-flex align-items-center flex-wrap">
                    @if (auth()->user()->role === 'pembimbing')
                        <a href="{{ route('monitoring.exportExcel') }}" class="btn btn-outline-success">
                            <i class="fas fa-file-excel mr-1"></i> Export Excel
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="monitoringTable" class="table table-striped table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Tempat PKL</th>
                            <th>No. Telepon Siswa</th>
                            <th>Nama Pembimbing</th>
                            <th class="text-center">Aksi</th>
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
                                    <button type="button" class="btn btn-outline-secondary"
                                        data-dismiss="modal">Batal</button>
                                    <button type="button" class="btn btn-primary btn-simpan">
                                        <i class="fas fa-save mr-1"></i> Simpan
                                    </button>
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
    @include('partials.table_styles')
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
    @include('partials.table_scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
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
                        data: 'siswa.kelas.nama_kelas',
                        name: 'siswa.kelas.nama_kelas'
                    },
                    {
                        data: 'perusahaan.nama_perusahaan',
                        name: 'perusahaan.nama_perusahaan'
                    },
                    {
                        data: 'siswa.no_hp_siswa',
                        name: 'siswa.no_hp_siswa'
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
                const id = $(this).data('id');

                $.ajax({
                    url: `/monitoring/${id}/edit`,
                    type: 'GET',
                    success: function(data) {
                        $('#id').val(data.id);
                        $('#siswa_id').val(data.siswa_id).trigger('change');
                        $('#perusahaan_id').val(data.perusahaan_id).trigger('change');
                        $('#pembimbing_id').val(data.pembimbing_id || '').trigger('change');
                        $('#modalForm').modal('show');
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                    }
                });
            });

            $(document).on('click', '.btn-simpan', function() {
                const id = $('#id').val();

                $.ajax({
                    url: `/monitoring/${id}`,
                    type: 'PUT',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        siswa_id: $('#siswa_id').val(),
                        perusahaan_id: $('#perusahaan_id').val(),
                        pembimbing_id: $('#pembimbing_id').val(),
                    },
                    success: function() {
                        $('#modalForm').modal('hide');
                        $('#monitoringTable').DataTable().ajax.reload();
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                    }
                });
            });

            $(document).on('click', '.btn-hapus', function() {
                const id = $(this).data('id');

                konfirmasiHapus({
                    title: 'Hapus data monitoring ini?',
                    text: 'Data yang dihapus tidak bisa dikembalikan.'
                }, function() {
                    $.ajax({
                        url: `/monitoring/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function() {
                            table.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            console.log(xhr.responseText);
                        }
                    });
                });
            });

        });

        //btn-edit
    </script>
@endsection
