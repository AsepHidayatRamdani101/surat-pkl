@extends('adminlte::page')

@section('title', 'Data Sertifikat PKL')

@push('css')
    <style>
        .sertifikat-data-card .card-header {
            gap: 1rem;
        }

        .sertifikat-data-card .card-header > .d-flex:first-child {
            min-width: 280px;
        }

        .sertifikat-data-card .sertifikat-toolbar {
            gap: .5rem;
        }

        .sertifikat-data-card .dataTables_wrapper {
            width: 100%;
        }

        .sertifikat-data-card .dataTables_wrapper .dataTables_filter input {
            min-width: 250px;
            margin-left: .5rem;
        }

        .sertifikat-data-card .dataTables_wrapper .dataTables_length select {
            min-width: 70px;
        }

        .sertifikat-data-card .table-scroll {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        #sertifikatDataTable {
            min-width: 1420px;
            margin-bottom: 0 !important;
        }

        #sertifikatDataTable th {
            padding: .7rem .65rem;
            white-space: nowrap;
            vertical-align: middle;
            font-size: .86rem;
        }

        #sertifikatDataTable td {
            padding: .65rem;
            vertical-align: top;
            line-height: 1.35;
            font-size: .88rem;
        }

        #sertifikatDataTable th:nth-child(1),
        #sertifikatDataTable td:nth-child(1) { width: 52px; text-align: center; }
        #sertifikatDataTable th:nth-child(2),
        #sertifikatDataTable td:nth-child(2) { width: 135px; white-space: nowrap; }
        #sertifikatDataTable th:nth-child(3),
        #sertifikatDataTable td:nth-child(3) { min-width: 190px; }
        #sertifikatDataTable th:nth-child(4),
        #sertifikatDataTable td:nth-child(4) { width: 155px; white-space: nowrap; }
        #sertifikatDataTable th:nth-child(5),
        #sertifikatDataTable td:nth-child(5) { width: 125px; white-space: nowrap; }
        #sertifikatDataTable th:nth-child(6),
        #sertifikatDataTable td:nth-child(6) { min-width: 190px; }
        #sertifikatDataTable th:nth-child(7),
        #sertifikatDataTable td:nth-child(7),
        #sertifikatDataTable th:nth-child(8),
        #sertifikatDataTable td:nth-child(8) { min-width: 210px; }
        #sertifikatDataTable th:nth-child(9),
        #sertifikatDataTable td:nth-child(9) { width: 125px; text-align: center; white-space: nowrap; }
        #sertifikatDataTable th:nth-child(10),
        #sertifikatDataTable td:nth-child(10) { width: 105px; text-align: center; white-space: nowrap; }

        #sertifikatDataTable .badge {
            font-weight: 600;
            padding: .45em .6em;
        }

        #sertifikatDataTable .btn-xs {
            padding: .25rem .45rem;
            white-space: nowrap;
        }

        @media (max-width: 767.98px) {
            .sertifikat-data-card .card-header {
                align-items: flex-start !important;
            }

            .sertifikat-data-card .card-header > .d-flex {
                width: 100%;
            }

            .sertifikat-data-card .sertifikat-toolbar {
                justify-content: flex-start !important;
            }

            .sertifikat-data-card .dataTables_wrapper .dataTables_filter,
            .sertifikat-data-card .dataTables_wrapper .dataTables_length {
                text-align: left;
                margin-bottom: .5rem;
            }

            .sertifikat-data-card .dataTables_wrapper .dataTables_filter input {
                width: calc(100vw - 80px);
                min-width: 0;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid pt-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        {{-- Card ringkasan pengisian data sertifikat --}}
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $siswaSudah }}</h3>
                        <p class="mb-0">Siswa Sudah Mengisi</p>
                    </div>
                    <div class="icon"><i class="fas fa-user-check"></i></div>
                    <a href="{{ route('sertifikat.data.index', ['status' => 'sudah']) }}" class="small-box-footer">
                        Lihat detail <i class="fas fa-arrow-circle-right ml-1"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $siswaBelum }}</h3>
                        <p class="mb-0">Siswa Belum Mengisi</p>
                    </div>
                    <div class="icon"><i class="fas fa-user-times"></i></div>
                    <a href="{{ route('sertifikat.data.index', ['status' => 'belum']) }}" class="small-box-footer">
                        Lihat detail <i class="fas fa-arrow-circle-right ml-1"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $perusahaanTerisi }}</h3>
                        <p class="mb-0">Perusahaan Sudah Terisi</p>
                    </div>
                    <div class="icon"><i class="fas fa-building"></i></div>
                    <span class="small-box-footer">Pemilik &amp; pembimbing lengkap</span>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $perusahaanBelum }}</h3>
                        <p class="mb-0">Perusahaan Belum Terisi</p>
                    </div>
                    <div class="icon"><i class="fas fa-building"></i></div>
                    <span class="small-box-footer">Masih ada data kosong</span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card app-card sertifikat-data-card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                        <div class="d-flex align-items-center my-1">
                            <span class="card-title-icon"><i class="fas fa-certificate"></i></span>
                            <div>
                                <h4>Data Sertifikat PKL</h4>
                                <small>Pengisian data pemilik &amp; pembimbing perusahaan oleh siswa (satu perusahaan boleh berbeda pembimbing per siswa)</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center flex-wrap my-1 sertifikat-toolbar">
                            <div class="form-inline">
                                    <select name="status" class="form-control form-control-sm mr-2">
                                    <option value="">Semua Status</option>
                                    <option value="sudah" {{ $statusFilter === 'sudah' ? 'selected' : '' }}>Sudah Mengisi</option>
                                    <option value="belum" {{ $statusFilter === 'belum' ? 'selected' : '' }}>Belum Mengisi</option>
                                </select>
                                </div>
                            @if ($siswaBelum > 0)
                                <form method="POST" action="{{ route('sertifikat.data.kirim-wa-massal') }}"
                                    class="d-inline" id="formKirimWaMassal">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success"
                                        title="Kirim WA pengingat ke semua siswa yang belum mengisi">
                                        <i class="fab fa-whatsapp mr-1"></i> Kirim WA ke Semua yang Belum Mengisi
                                        <span class="badge badge-light ml-1">{{ $siswaBelum }}</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-scroll">
                            <table id="sertifikatDataTable" class="table table-striped table-hover" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>NIS</th>
                                        <th>Nama Siswa</th>
                                        <th>Nomor Telpon Siswa</th>
                                        <th>Kelas</th>
                                        <th>Perusahaan</th>
                                        <th>Pemilik / Pimpinan Perusahaan</th>
                                        <th>Pembimbing Perusahaan</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    @include('partials.table_scripts')
    <script>
        $(function () {
            const table = $('#sertifikatDataTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: @json(route('sertifikat.data.index')),
                    data: function (data) {
                        data.status = $('select[name="status"]').val();
                    }
                },
                order: [[2, 'asc']],
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'siswa_nis', name: 'siswa.nis' },
                    { data: 'siswa_nama', name: 'siswa.nama_siswa' },
                    { data: 'siswa_telepon', name: 'siswa.no_hp_siswa' },
                    { data: 'kelas', name: 'siswa.kelas.nama_kelas', orderable: false },
                    { data: 'perusahaan_nama', name: 'perusahaan.nama_perusahaan' },
                    { data: 'pemilik', name: 'nama_pemilik_instansi', orderable: false },
                    { data: 'pembimbing_perusahaan', name: 'nama_pembimbing_instansi', orderable: false },
                    { data: 'status_html', name: 'nama_pemilik_instansi', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center' }
                ],
                language: {
                    emptyTable: 'Tidak ada data tempat PKL.',
                    zeroRecords: 'Data tidak ditemukan.'
                }
            });

            $('select[name="status"]').on('change', function () {
                table.ajax.reload();
            });
        });

        var formMassal = document.getElementById('formKirimWaMassal');
        if (formMassal) {
            formMassal.addEventListener('submit', function (e) {
                if (!confirm('Kirim pesan WhatsApp pengingat ke SEMUA siswa yang belum mengisi ({{ $siswaBelum }} siswa)?')) {
                    e.preventDefault();
                    return;
                }
                var btn = formMassal.querySelector('button[type="submit"]');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1"></span> Mengirim...';
            });
        }
    </script>
@endsection
