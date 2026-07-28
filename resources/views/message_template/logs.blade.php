@extends('adminlte::page')

@section('title', 'Riwayat Pengiriman Pesan')

@push('css')
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endpush

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Riwayat Pengiriman Pesan</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('message-template.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Pengiriman</h3>
                <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 300px;">
                        <select id="filterStatus" class="form-control">
                            <option value="">-- Semua Status --</option>
                            <option value="terkirim">Terkirim</option>
                            <option value="pending">Pending</option>
                            <option value="gagal">Gagal</option>
                        </select>
                        <select id="filterTipe" class="form-control ml-2">
                            <option value="">-- Semua Tipe --</option>
                            <option value="personal">Personal</option>
                            <option value="masal">Masal</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table id="logsTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 20%;">Template</th>
                            <th style="width: 15%;">Nomor HP</th>
                            <th style="width: 10%;">Tipe</th>
                            <th style="width: 10%;">Status</th>
                            <th style="width: 15%;">Pengirim</th>
                            <th style="width: 15%;">Tanggal</th>
                            <th style="width: 10%;">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(function() {
            var table = $('#logsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('message-template.logs-data') }}',
                    type: 'GET',
                    data: function(d) {
                        d.status = $('#filterStatus').val();
                        d.tipe = $('#filterTipe').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'template_name',
                        name: 'template_name'
                    },
                    {
                        data: 'nomor_hp',
                        name: 'nomor_hp'
                    },
                    {
                        data: 'tipe',
                        name: 'tipe',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'pengirim',
                        name: 'pengirim',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'tanggal',
                        name: 'tanggal'
                    },
                    {
                        data: 'id',
                        name: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data) {
                            return '<button class="btn btn-sm btn-info btnDetail" data-id="' +
                                data + '">Lihat</button>';
                        }
                    }
                ]
            });

            // Reload table when filter changes
            $('#filterStatus, #filterTipe').on('change', function() {
                table.ajax.reload();
            });

            $(document).on('click', '.btnDetail', function() {
                alert('Detail fitur akan ditampilkan dalam update berikutnya.');
            });
        });
    </script>
@endsection
