@extends('adminlte::page')

@section('title', $title)

@section('css')
    @include('partials.table_styles')
@endsection

@section('content_header')
    <h1>{{ $title }}</h1>
@endsection

@section('content')
    @include('absensi_selfie.partials.monitoring_cards')

    <div class="card app-card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
            <div class="d-flex align-items-center my-1">
                <span class="card-title-icon"><i class="fas fa-camera"></i></span>
                <div>
                    <h4>Rekap Absen Selfie</h4>
                    <small>Pantau absen selfie datang & pulang siswa PKL</small>
                </div>
            </div>
            <div class="my-1 d-flex align-items-center flex-wrap">
                <input type="date" id="filterTanggal" class="form-control form-control-sm mr-2 my-1"
                    style="width: 170px;" value="{{ now()->toDateString() }}">
                <button type="button" class="btn btn-sm btn-outline-secondary my-1" id="btnSemuaTanggal">
                    Tampilkan Semua Tanggal
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="absenTable" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Perusahaan</th>
                            <th>Jenis</th>
                            <th>Waktu</th>
                            <th>Status</th>
                            <th>Akurasi Lokasi</th>
                            <th>Lokasi</th>
                            <th>Foto</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('js')
    @include('partials.table_scripts')
    <script>
        $(function() {
            const table = $('#absenTable').DataTable({
                processing: true,
                order: [],
                ajax: {
                    url: '{{ route('absensi-selfie.data') }}',
                    data: function(d) {
                        d.tanggal = $('#filterTanggal').val() || null;
                    },
                    dataSrc: '',
                    error: () => Swal.fire('Gagal', 'Data tidak dapat dimuat.', 'error')
                },
                columns: [{
                        data: null,
                        render: (v, d, r, meta) => meta.row + 1
                    },
                    {
                        data: 'tanggal'
                    },
                    {
                        data: 'siswa.nama_siswa',
                        defaultContent: '-'
                    },
                    {
                        data: 'siswa.kelas.nama_kelas',
                        defaultContent: '-'
                    },
                    {
                        data: 'perusahaan.nama_perusahaan',
                        defaultContent: '-'
                    },
                    {
                        data: 'jenis',
                        render: value =>
                            `<span class="badge badge-soft-${value === 'datang' ? 'primary' : 'info'} px-2 py-1 text-capitalize">${value}</span>`
                    },
                    {
                        data: 'waktu',
                        render: value => (value || '').substring(0, 5)
                    },
                    {
                        data: 'status_waktu',
                        render: value => {
                            if (value === 'tepat_waktu') return '<span class="badge badge-soft-success px-2 py-1">Tepat Waktu</span>';
                            if (value === 'terlambat') return '<span class="badge badge-soft-danger px-2 py-1">Terlambat</span>';
                            return '<span class="text-muted">-</span>';
                        }
                    },
                    {
                        data: null,
                        render: record => {
                            if (record.persentase_lokasi === null) {
                                return '<span class="text-muted">Koordinat perusahaan belum diatur</span>';
                            }
                            const value = record.persentase_lokasi;
                            const warna = value >= 80 ? 'success' : (value >= 40 ? 'warning' : 'danger');
                            const jarak = record.jarak_meter !== null ?
                                `<br><small class="text-muted">± ${record.jarak_meter} m dari perusahaan</small>` : '';
                            return `<span class="badge badge-soft-${warna} px-2 py-1">Presentase ${value}% mendekati perusahaan</span>${jarak}`;
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        render: record =>
                            `<a class="btn btn-xs btn-outline-success" href="https://www.google.com/maps?q=${record.latitude},${record.longitude}" target="_blank" rel="noopener"><i class="fas fa-map-marker-alt mr-1"></i>Maps</a>`
                    },
                    {
                        data: 'foto_path',
                        orderable: false,
                        render: value => value ?
                            `<a href="/storage/${value}" target="_blank" rel="noopener"><img src="/storage/${value}" alt="Selfie" style="height:48px" class="rounded"></a>` :
                            '-'
                    }
                ]
            });

            const monitoring = initMonitoringCards({
                tanggal: () => $('#filterTanggal').val()
            });

            $('#filterTanggal').on('change', function() {
                table.ajax.reload();
                monitoring.reload();
            });
            $('#btnSemuaTanggal').on('click', function() {
                $('#filterTanggal').val('');
                table.ajax.reload();
                monitoring.reload();
            });
        });
    </script>
@endsection
