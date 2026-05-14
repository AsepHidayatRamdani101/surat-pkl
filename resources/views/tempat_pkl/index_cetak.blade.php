@extends('adminlte::page')

@section('title', 'Surat Izin Orang Tua')

@section('content')
    <div class="container pt-4">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4>Data Tempat PKL</h4>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalTanggal">
                        <i class="fas fa-calendar"></i> Nomor Surat
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table id="tempatPklTable" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th>Perusahaan</th>
                            <th>Tgl Mulai</th>
                            <th>Tgl Selesai</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>

                <!-- Modal Tanggal -->
                <div class="modal fade" id="modalTanggal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form id="formTanggal" method="GET" action="{{ route('tempat-pkl.set-tanggal') }}">

                                <div class="modal-header">
                                    <h5 class="modal-title">Set Nomor Surat</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Nomor Surat</label>
                                        <input type="text" name="nomor_surat" class="form-control"
                                            value="{{ session('tempat_pkl_nomor_surat', '087/PK.03.03-SMKN8GRT') }}" required>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Simpan Nomor Surat</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

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
                                        <select name="siswa_id[]" id="siswa_id" class="form-control select2" multiple
                                            style="width: 100%;">
                                            @foreach ($siswa as $siswa)
                                                <option value="{{ $siswa->id }}">{{ $siswa->nama_siswa }} -
                                                    {{ $siswa->kelas->nama_kelas }}</option>
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
                                        <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                                        <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control">
                                    </div>
                                    <div class="mb-2">
                                        <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                                        <input type="date" name="tanggal_selesai" id="tanggal_selesai"
                                            class="form-control">
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

            $('.select2').select2({
                theme: 'bootstrap-5',
                placeholder: 'Pilih salah satu atau lebih...',
                allowClear: true,
                dropdownParent: $('#modalForm') // jika select2 berada dalam modal
            });

            let table = $('#tempatPklTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('tempat-pkl.index_cetak') }}',
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
                        data: 'tanggal_mulai',
                        name: 'tanggal_mulai'
                    },
                    {
                        data: 'tanggal_selesai',
                        name: 'tanggal_selesai'
                    },
                    {
                        data: 'aksi',
                        name: 'aksi',
                        orderable: false,
                        searchable: false
                    },
                ]
            });
        });

        //btn-edit
    </script>
@endsection
