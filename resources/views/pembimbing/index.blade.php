@extends('adminlte::page')

@section('title', 'Data Pembimbing Sekolah')

@section('content')
    <div class="container pt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Data Pembimbing</h4>
                        <select id="filterStatusAkunPembimbing" class="form-control form-control-sm d-inline-block"
                            style="width: 220px; margin-right: 8px;">
                            <option value="">Semua Status Akun</option>
                            <option value="without" selected>Belum Punya Akun</option>
                            <option value="with">Sudah Punya Akun</option>
                        </select>
                        <button class="btn btn-sm btn-info" id="btnGenerateAkunPembimbing">Generate Akun Pembimbing</button>
                        <button class="btn btn-sm btn-primary ms-auto" id="btnTambah">Tambah Data</button>
                        <a href="{{ route('pembimbing.export-excel') }}" class="btn btn-sm btn-success">Export Excel</a>
                        <a href="{{ route('pembimbing.export-pdf') }}" class="btn btn-sm btn-danger">Export PDF</a>
                        <button class="btn btn-sm btn-secondary" id="btnImport">Import Excel</button>
                    </div>
                    <div class="card-body ">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="small-box bg-info mb-0">
                                    <div class="inner">
                                        <h3 id="totalJumlahSiswaCard">{{ (int) $pembimbing->sum('jumlah_siswa') }}</h3>
                                        <p>Total Jumlah Siswa (Kolom Jumlah Siswa)</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive pembimbing-table-wrap">
                            <table id="pembimbingTable" class="table table-bordered table-striped w-100"
                                style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>

                                        <th>Jurusan</th>
                                        <th>Jumlah Jam</th>
                                        <th>Jumlah Siswa</th>
                                        <th>Kelas</th>
                                        <th>No HP</th>
                                        <th>Status Akun</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalForm" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <form id="formPembimbing">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalFormLabel">Form Pembimbing</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id" id="id">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nama">Nama</label>
                                        <input type="text" name="nama" id="nama" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nip">NIP</label>
                                        <input type="text" name="nip" id="nip" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="jabatan_pembimbing">Jabatan</label>
                                        <select name="jabatan_pembimbing" id="jabatan_pembimbing" class="form-control"
                                            required>
                                            <option value="">Pilih Jabatan</option>
                                            <option value="Guru">Guru</option>
                                            <option value="Wakil Kepala Sekolah">Wakil Kepala Sekolah</option>
                                            <option value="Kepala Program">Kepala Program</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="jenis_kelamin">Jenis Kelamin</label>
                                        <select name="jenis_kelamin" id="jenis_kelamin" class="form-control" required>
                                            <option value="">Pilih Jenis Kelamin</option>
                                            <option value="Laki-laki">Laki-laki</option>
                                            <option value="Perempuan">Perempuan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="jenis_guru">Jenis Guru</label>
                                        <select name="jenis_guru" id="jenis_guru" class="form-control" required>
                                            <option value="">Pilih Jenis Guru</option>
                                            <option value="adaptif_normatif">Adaptif Normatif</option>
                                            <option value="guru_produktif">Guru Produktif</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" id="jurusanWrapper">
                                        <label for="jurusan_id">Jurusan</label>
                                        <select name="jurusan_id" id="jurusan_id" class="form-control">
                                            <option value="">Pilih Jurusan</option>
                                            @foreach ($jurusan as $item)
                                                <option value="{{ $item->id }}">{{ $item->nama_jurusan }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted" id="jurusanHint">Guru adaptif normatif mengajar di semua
                                            jurusan.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="jumlah_siswa">Jumlah Siswa</label>
                                        <input type="number" name="jumlah_siswa" id="jumlah_siswa" class="form-control"
                                            min="0" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="jumlah_jam">Jumlah Jam</label>
                                        <input type="number" name="jumlah_jam" id="jumlah_jam" class="form-control"
                                            min="0" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" id="kelasWrapper">
                                        <label>Kelas</label>
                                        <div class="d-flex align-items-center flex-wrap gap-2">
                                            <button type="button" class="btn btn-outline-primary btn-sm"
                                                id="btnPilihKelas">
                                                Pilih Kelas
                                            </button>
                                            <span id="kelasSummary" class="text-muted">Belum ada kelas dipilih.</span>
                                        </div>
                                        <div id="kelasHiddenInputs"></div>
                                        <small class="text-muted d-block mt-1">Pilih satu atau beberapa kelas, atau pilih
                                            "Semua Kelas".</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label for="no_hp_pembimbing">No HP</label>
                                        <input type="text" name="no_hp_pembimbing" id="no_hp_pembimbing"
                                            class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-primary btn-simpan">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="modalImport" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <form action="{{ route('pembimbing.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Import Data Pembimbing</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted mb-3">
                                <a href="{{ route('pembimbing.download-template') }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-download"></i> Download Format Template
                                </a>
                            </p>
                            <div class="form-group mb-0">
                                <label for="file">File Excel</label>
                                <input type="file" name="file" id="file" class="form-control"
                                    accept=".xlsx,.xls" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Import</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="modalKelasPicker" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Pilih Kelas</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-0">
                            <label for="kelas_picker">Kelas</label>
                            <select id="kelas_picker" class="form-control select2" multiple
                                data-placeholder="Pilih kelas"></select>
                            <small class="text-muted d-block mt-2" id="kelasPickerHint">
                                Pilih kelas yang sesuai dengan jurusan.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="btnSimpanKelas">Simpan Kelas</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--multiple {
            min-height: calc(1.5em + .75rem + 2px);
            border: 1px solid #ced4da;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            padding: 2px 8px;
        }
        }

        #kelasWrapper .select2-selection__choice {
            white-space: normal;
        }

        #kelasSummary {
            min-height: 24px;
        }

        .pembimbing-table-wrap,
        .pembimbing-table-wrap .dataTables_wrapper,
        .pembimbing-table-wrap .dataTables_scroll,
        .pembimbing-table-wrap .dataTables_scrollHead,
        .pembimbing-table-wrap .dataTables_scrollHeadInner,
        .pembimbing-table-wrap .dataTables_scrollBody,
        #pembimbingTable {
            width: 100% !important;
        }
    </style>
@endsection

@section('js')
    @include('sweetalert::alert')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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

            function showGenerateLoading(titleText) {
                Swal.fire({
                    title: titleText,
                    text: 'Mohon tunggu, proses sedang berjalan...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            }


            $('#btnGenerateAkunPembimbing').click(function() {
                Swal.fire({
                    title: 'Generate akun pembimbing?',
                    text: 'Username akan menggunakan NIP dan password default guru12345.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, generate',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    showGenerateLoading('Sedang generate akun pembimbing');

                    $.ajax({
                        url: '{{ route('pembimbing.generate-accounts') }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON?.message ||
                                    'Terjadi kesalahan saat generate akun pembimbing.',
                            });
                        }
                    });
                });
            });

            $('#btnImport').click(function() {
                $('#modalImport').modal('show');
            });

            $('#kelas_picker').select2({
                dropdownParent: $('#modalKelasPicker'),
                width: '100%',
                placeholder: $('#kelas_picker').data('placeholder') || 'Pilih kelas',
                allowClear: true
            });

            const kelasOptions = @json($kelasOptions);
            let selectedKelasIds = [];

            function isProduktif() {
                return $('#jenis_guru').val() === 'guru_produktif';
            }

            function getFilteredKelasOptions() {
                const jurusanId = $('#jurusan_id').val();

                if (!isProduktif()) {
                    return kelasOptions;
                }

                if (!jurusanId) {
                    return [];
                }

                return kelasOptions.filter(function(option) {
                    return option.jurusan_id === String(jurusanId);
                });
            }

            function renderKelasPicker() {
                const filteredOptions = getFilteredKelasOptions();
                const selectedSet = new Set((selectedKelasIds || []).map(String));
                const html = [];

                if (!isProduktif()) {
                    html.push('<option value="all">Semua Kelas</option>');
                } else if (!$('#jurusan_id').val()) {
                    html.push('<option value="" disabled>Pilih jurusan terlebih dahulu</option>');
                } else {
                    html.push('<option value="all">Semua Kelas</option>');
                }

                filteredOptions.forEach(function(option) {
                    const selected = selectedSet.has(option.id) ? ' selected' : '';
                    html.push('<option value="' + option.id + '"' + selected + '>' + option.text +
                        '</option>');
                });

                $('#kelas_picker').html(html.join(''));
                $('#kelas_picker').val(selectedKelasIds.map(String)).trigger('change.select2');

                const selectedLabels = selectedKelasIds.length ?
                    selectedKelasIds.map(function(value) {
                        if (value === 'all') {
                            return 'Semua Kelas';
                        }

                        const found = kelasOptions.find(function(option) {
                            return option.id === String(value);
                        });

                        return found ? found.text : value;
                    }) : [];

                $('#kelasSummary').text(selectedLabels.length ? selectedLabels.join(', ') :
                    'Belum ada kelas dipilih.');
                const hiddenInputs = selectedKelasIds.map(function(value) {
                    return '<input type="hidden" name="kelas_ids[]" value="' + value + '">';
                });
                $('#kelasHiddenInputs').html(hiddenInputs.join(''));
            }


            function updateTotalJumlahSiswaCard(tableInstance) {
                const dataRows = tableInstance.rows({
                    search: 'applied'
                }).data().toArray();

                const totalJumlahSiswa = dataRows.reduce(function(total, row) {
                    return total + Number(row.jumlah_siswa || 0);
                }, 0);

                $('#totalJumlahSiswaCard').text(totalJumlahSiswa);
            }

            let table = $('#pembimbingTable').DataTable({
                ajax: {
                    url: '{{ route('pembimbing.data') }}',
                    data: function(d) {
                        d.account_status = $('#filterStatusAkunPembimbing').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'nama_pembimbing',
                        name: 'nama_pembimbing'
                    },
                    {
                        data: 'nip_pembimbing',
                        name: 'nip_pembimbing'
                    },
                    {
                        data: 'jumlah_jam',
                        name: 'jumlah_jam'
                    },
                    {
                        data: 'jumlah_siswa',
                        name: 'jumlah_siswa',
                        searchable: false
                    },
                    {
                        data: 'kelas_nama',
                        name: 'kelas_nama',
                        orderable: false,
                        searchable: false,
                        className: 'kelas-column'
                    },
                    {
                        data: 'no_hp_pembimbing',
                        name: 'no_hp_pembimbing'
                    },
                    {
                        data: 'status_akun',
                        name: 'status_akun',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'aksi',
                        name: 'aksi',
                        orderable: false,
                        searchable: false
                    }
                ],
                autoWidth: false,
                responsive: true,
                drawCallback: function() {
                    const api = this.api();
                    updateTotalJumlahSiswaCard(api);
                    api.columns.adjust();
                    if (api.responsive) {
                        api.responsive.recalc();
                    }
                },
                initComplete: function() {
                    const api = this.api();
                    updateTotalJumlahSiswaCard(api);
                    api.columns.adjust();
                    if (api.responsive) {
                        api.responsive.recalc();
                    }
                }
            });

            $('#filterStatusAkunPembimbing').change(function() {
                table.ajax.reload(function() {
                    table.columns.adjust();
                    if (table.responsive) {
                        table.responsive.recalc();
                    }
                });
            });

            $('#btnTambah').click(function() {
                $('#modalForm').modal('show');
                $('#modalFormLabel').html('Tambah Data Pembimbing');
                $('#formPembimbing').trigger('reset');
                selectedKelasIds = [];
                renderKelasPicker();
                toggleJurusanField();
                toggleKelasField();
            });

            function toggleJurusanField() {
                const jenisGuru = $('#jenis_guru').val();
                const isProduktif = jenisGuru === 'guru_produktif';

                $('#jurusan_id').prop('disabled', !isProduktif);
                $('#jurusan_id').prop('required', isProduktif);

                if (!isProduktif) {
                    $('#jurusan_id').val('');
                }
            }

            function toggleKelasField() {
                $('#kelasWrapper').show();
                $('#btnPilihKelas').prop('disabled', isProduktif() && !$('#jurusan_id').val());
                renderKelasPicker();
            }

            $('#jenis_guru').change(function() {
                toggleJurusanField();
                toggleKelasField();
            });

            $('#jurusan_id').change(function() {
                if (isProduktif()) {
                    selectedKelasIds = [];
                    renderKelasPicker();
                } else {
                    renderKelasPicker();
                }
            });

            $('#btnPilihKelas').click(function() {
                if (isProduktif() && !$('#jurusan_id').val()) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Pilih jurusan dulu',
                        text: 'Untuk guru produktif, kelas difilter berdasarkan jurusan yang dipilih.',
                    });
                    return;
                }

                renderKelasPicker();
                $('#kelasPickerHint').text(isProduktif() ?
                    'Kelas difilter berdasarkan jurusan yang dipilih.' :
                    'Pilih satu atau beberapa kelas, atau pilih semua kelas.');
                $('#modalKelasPicker').modal('show');
            });

            $('#kelas_picker').on('change', function() {
                const values = $(this).val() || [];

                if (values.includes('all') && values.length > 1) {
                    const withoutAll = values.filter(function(value) {
                        return value !== 'all';
                    });
                    $(this).val(withoutAll).trigger('change.select2');
                    return;
                }

                selectedKelasIds = $(this).val() ? $(this).val().map(String) : [];
                if (selectedKelasIds.includes('all')) {
                    selectedKelasIds = ['all'];
                }

                const selectedLabels = selectedKelasIds.length ?
                    selectedKelasIds.map(function(value) {
                        if (value === 'all') {
                            return 'Semua Kelas';
                        }

                        const found = kelasOptions.find(function(option) {
                            return option.id === String(value);
                        });

                        return found ? found.text : value;
                    }) : [];

                $('#kelasSummary').text(selectedLabels.length ? selectedLabels.join(', ') :
                    'Belum ada kelas dipilih.');

                const hiddenInputs = selectedKelasIds.map(function(value) {
                    return '<input type="hidden" name="kelas_ids[]" value="' + value + '">';
                });
                $('#kelasHiddenInputs').html(hiddenInputs.join(''));
            });

            $('#btnSimpanKelas').click(function() {
                selectedKelasIds = ($('#kelas_picker').val() || []).map(String);

                if (selectedKelasIds.includes('all')) {
                    selectedKelasIds = ['all'];
                }

                renderKelasPicker();
                $('#modalKelasPicker').modal('hide');
            });

            function normalizeJenisKelamin(value) {
                const normalized = String(value || '').trim().toLowerCase();

                if (['laki-laki', 'laki laki', 'lakilaki'].includes(normalized)) {
                    return 'Laki-laki';
                }

                if (normalized === 'perempuan') {
                    return 'Perempuan';
                }

                return '';
            }

            $(document).on('click', '.btnEdit', function() {
                let data = $(this).data();
                // alert(data.jenis);
                $('#nama').val(data.nama);
                $('#nip').val(data.nip);
                $('#id').val(data.id);
                $('#jabatan_pembimbing').val(data.jabatan);
                $('#jenis_kelamin').val(normalizeJenisKelamin(data.jenis));
                $('#jenis_guru').val(data.jenisGuru);
                $('#jurusan_id').val(data.jurusanId);
                $('#jumlah_jam').val(data.jumlahJam);
                $('#jumlah_siswa').val(data.jumlahSiswa);
                $('#no_hp_pembimbing').val(data.nohp);
                toggleJurusanField();
                const kelasIds = Array.isArray(data.kelasIds) ? data.kelasIds : (data.kelasIds ? JSON.parse(
                    data.kelasIds) : []);
                selectedKelasIds = kelasIds.map(String);
                renderKelasPicker();
                toggleKelasField();
                $('#modalForm').modal('show');
                $('#modalFormLabel').html('Edit Data Pembimbing');
            });

            $(document).on('click', '.btn-simpan', function() {
                let id = $('#id').val();
                let url = id ? `/pembimbing/${id}` : '{{ route('pembimbing.store') }}';
                let method = id ? 'PUT' : 'POST';
                const formData = $('#formPembimbing').serializeArray();

                const kelasIdsForSubmit = $('#kelasHiddenInputs input[name="kelas_ids[]"]')
                    .map(function() {
                        return $(this).val();
                    })
                    .get();

                if (kelasIdsForSubmit.length) {
                    selectedKelasIds = kelasIdsForSubmit.map(String);
                }

                const payload = {};
                formData.forEach(function(item) {
                    if (item.name === 'kelas_ids[]') {
                        if (!payload.kelas_ids) {
                            payload.kelas_ids = [];
                        }

                        payload.kelas_ids.push(item.value);
                        return;
                    }

                    payload[item.name] = item.value;
                });

                if (!payload.kelas_ids || !payload.kelas_ids.length) {
                    payload.kelas_ids = selectedKelasIds;
                }

                payload._token = '{{ csrf_token() }}';
                payload.nama_pembimbing = $('#nama').val();
                payload.nip_pembimbing = $('#nip').val();
                payload.jabatan_pembimbing = $('#jabatan_pembimbing').val();
                payload.jenis_kelamin = $('#jenis_kelamin').val();
                payload.jenis_guru = $('#jenis_guru').val();
                payload.jurusan_id = $('#jurusan_id').val();
                payload.jumlah_jam = $('#jumlah_jam').val();
                payload.jumlah_siswa = $('#jumlah_siswa').val();
                payload.no_hp_pembimbing = $('#no_hp_pembimbing').val();

                $.ajax({
                    url: url,
                    type: method,
                    data: payload,
                    success: function() {
                        $('#modalForm').modal('hide');
                        table.ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Data telah disimpan.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Terjadi kesalahan.',
                        });
                    }
                });
            });

            //btnHapus
            $(document).on('click', '.btnHapus', function() {
                let id = $(this).data('id');
                let url = '{{ route('pembimbing.destroy', ':id') }}';
                url = url.replace(':id', id);
                Swal.fire({
                    title: 'Yakin hapus data ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}',
                            },
                            success: function() {
                                table.ajax.reload();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: 'Data telah dihapus.',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            },
                            error: function(xhr) {
                                console.log(xhr.responseText);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'Terjadi kesalahan.',
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
