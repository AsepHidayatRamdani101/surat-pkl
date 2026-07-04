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
                        <button class="btn btn-sm btn-secondary" id="btnImport">Import Excel</button>
                    </div>
                    <div class="card-body">
                        <table id="pembimbingTable" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>NIP</th>
                                    <th>Jabatan</th>
                                    <th>Jenis Guru</th>
                                    <th>Jurusan</th>
                                    <th>Jumlah Jam</th>
                                    <th>Jumlah Siswa</th>
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

        <div class="modal fade" id="modalForm" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <form id="formPembimbing">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalFormLabel">Form Pembimbing</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id" id="id">

                            <div class="form-group">
                                <label for="nama">Nama</label>
                                <input type="text" name="nama" id="nama" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="nip">NIP</label>
                                <input type="text" name="nip" id="nip" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="jabatan_pembimbing">Jabatan</label>
                                <select name="jabatan_pembimbing" id="jabatan_pembimbing" class="form-control" required>
                                    <option value="">Pilih Jabatan</option>
                                    <option value="Guru">Guru</option>
                                    <option value="Wakil Kepala Sekolah">Wakil Kepala Sekolah</option>
                                    <option value="Kepala Program">Kepala Program</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="jenis_kelamin">Jenis Kelamin</label>
                                <select name="jenis_kelamin" id="jenis_kelamin" class="form-control" required>
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="jenis_guru">Jenis Guru</label>
                                <select name="jenis_guru" id="jenis_guru" class="form-control" required>
                                    <option value="">Pilih Jenis Guru</option>
                                    <option value="adaptif_normatif">Adaptif Normatif</option>
                                    <option value="guru_produktif">Guru Produktif</option>
                                </select>
                            </div>
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
                            <div class="form-group">
                                <label for="jumlah_jam">Jumlah Jam</label>
                                <input type="number" name="jumlah_jam" id="jumlah_jam" class="form-control" min="0"
                                    required>
                            </div>
                            <div class="form-group mb-0">
                                <label for="no_hp_pembimbing">No HP</label>
                                <input type="text" name="no_hp_pembimbing" id="no_hp_pembimbing" class="form-control"
                                    required>
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
    </div>
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('js')
    @include('sweetalert::alert')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
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
                        data: 'jabatan_pembimbing',
                        name: 'jabatan_pembimbing'
                    },
                    {
                        data: 'jenis_guru_label',
                        name: 'jenis_guru'
                    },
                    {
                        data: 'jurusan_nama',
                        name: 'jurusan_nama'
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
                ]
            });

            $('#filterStatusAkunPembimbing').change(function() {
                table.ajax.reload();
            });

            $('#btnTambah').click(function() {
                $('#modalForm').modal('show');
                $('#modalFormLabel').html('Tambah Data Pembimbing');
                $('#formPembimbing').trigger('reset');
                toggleJurusanField();
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

            $('#jenis_guru').change(function() {
                toggleJurusanField();
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
                $('#no_hp_pembimbing').val(data.nohp);
                toggleJurusanField();
                $('#modalForm').modal('show');
                $('#modalFormLabel').html('Edit Data Pembimbing');
            });

            $(document).on('click', '.btn-simpan', function() {
                let id = $('#id').val();
                let url = id ? `/pembimbing/${id}` : '{{ route('pembimbing.store') }}';
                let method = id ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    type: method,
                    data: {
                        _token: '{{ csrf_token() }}',
                        nama_pembimbing: $('#nama').val(),
                        nip_pembimbing: $('#nip').val(),
                        jabatan_pembimbing: $('#jabatan_pembimbing').val(),
                        jenis_kelamin: $('#jenis_kelamin').val(),
                        jenis_guru: $('#jenis_guru').val(),
                        jurusan_id: $('#jurusan_id').val(),
                        jumlah_jam: $('#jumlah_jam').val(),
                        no_hp_pembimbing: $('#no_hp_pembimbing').val(),
                    },
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
