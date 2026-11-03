@extends('adminlte::page')

@section('title', 'Jadwal Sidang PKL')

@section('content')
    <div class="container-fluid pt-4">
        <div class="card sidang-card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center my-1">
                    <span class="card-title-icon"><i class="fas fa-calendar-alt"></i></span>
                    <div>
                        <h4>
                            @if ($role === 'siswa')
                                Jadwal Bimbingan & Sidang Saya
                            @else
                                Jadwal Bimbingan & Sidang PKL
                            @endif
                        </h4>
                        <small>
                            @if ($role === 'siswa')
                                Jadwal bimbingan laporan dan sidang PKL Anda
                            @elseif ($role === 'pembimbing')
                                Jadwal siswa yang Anda bimbing / uji
                            @else
                                Atur jadwal manual atau generate otomatis per ruangan
                            @endif
                        </small>
                    </div>
                </div>
                <div class="my-1 d-flex align-items-center">
                    <select class="form-control form-control-sm mr-2" id="filterJenis" style="width: auto;">
                        <option value="">Semua Jenis</option>
                        <option value="bimbingan">Bimbingan Sidang</option>
                        <option value="sidang">Sidang</option>
                    </select>
                    @if ($role === 'panitia')
                        <button class="btn btn-outline-primary mr-2" id="btnGenerate">
                            <i class="fas fa-magic mr-1"></i> Generate Otomatis
                        </button>
                        <button class="btn btn-primary" id="btnTambah">
                            <i class="fas fa-plus mr-1"></i> Tambah Jadwal
                        </button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="jadwalTable" class="table table-striped table-hover" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th style="width: 110px;">Jenis</th>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Pembimbing</th>
                            <th>Ruangan</th>
                            <th>Tanggal</th>
                            <th style="width: 120px;">Jam</th>
                            @if ($role === 'panitia')
                                <th style="width: 100px;" class="text-center">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        @if ($role === 'panitia')
            {{-- Modal jadwal manual --}}
            <div class="modal fade" id="modalForm" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg" role="document">
                    <form id="formJadwal">
                        @csrf
                        <input type="hidden" name="id" id="id">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalTitle">Tambah Jadwal</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>Siswa <span class="text-danger">*</span></label>
                                        <select class="form-control" name="siswa_id" id="siswa_id" required>
                                            <option value="">-- Pilih Siswa --</option>
                                            @foreach ($siswaList as $siswa)
                                                <option value="{{ $siswa->id }}">
                                                    {{ $siswa->nama_siswa }} ({{ $siswa->kelas->nama_kelas ?? '-' }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Pembimbing / Penguji</label>
                                        <select class="form-control" name="pembimbing_id" id="pembimbing_id">
                                            <option value="">-- Pilih Pembimbing --</option>
                                            @foreach ($pembimbingList as $pembimbing)
                                                <option value="{{ $pembimbing->id }}">{{ $pembimbing->nama_pembimbing }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 form-group">
                                        <label>Jenis <span class="text-danger">*</span></label>
                                        <select class="form-control" name="jenis" id="jenis" required>
                                            <option value="bimbingan">Bimbingan Sidang</option>
                                            <option value="sidang">Sidang</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Ruangan</label>
                                        <select class="form-control" name="ruangan_sidang_id" id="ruangan_sidang_id">
                                            <option value="">-- Pilih Ruangan --</option>
                                            @foreach ($ruangan as $item)
                                                <option value="{{ $item->id }}">{{ $item->nama_ruangan }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Tanggal <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="tanggal" id="tanggal" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 form-group">
                                        <label>Jam Mulai <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control" name="jam_mulai" id="jam_mulai" required>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Jam Selesai <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control" name="jam_selesai" id="jam_selesai"
                                            required>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Catatan</label>
                                        <input type="text" class="form-control" name="catatan" id="catatan">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i> Simpan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Modal generate otomatis --}}
            <div class="modal fade" id="modalGenerate" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <form id="formGenerate">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Generate Jadwal Otomatis</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <p class="text-muted">
                                    Jadwal dibuat berurutan untuk semua siswa yang belum terjadwal, dibagi ke
                                    ruangan terpilih. Pembimbing diambil dari kelompok bimbingan masing-masing siswa.
                                </p>
                                <div class="form-group">
                                    <label>Jenis Jadwal <span class="text-danger">*</span></label>
                                    <select class="form-control" name="jenis" id="gen_jenis" required>
                                        <option value="bimbingan">Bimbingan Sidang</option>
                                        <option value="sidang">Sidang</option>
                                    </select>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>Tanggal Mulai <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="tanggal_mulai" id="gen_tanggal"
                                            required>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Durasi per Siswa (menit) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="durasi_menit" id="gen_durasi"
                                            value="30" min="10" max="240" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>Jam Mulai Harian <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control" name="jam_mulai" id="gen_jam_mulai"
                                            value="08:00" required>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Jam Selesai Harian <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control" name="jam_selesai" id="gen_jam_selesai"
                                            value="15:00" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Ruangan yang Dipakai <span class="text-danger">*</span></label>
                                    <div>
                                        @forelse ($ruangan as $item)
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input gen-ruangan"
                                                    id="ruangan{{ $item->id }}" value="{{ $item->id }}">
                                                <label class="custom-control-label" for="ruangan{{ $item->id }}">
                                                    {{ $item->nama_ruangan }}
                                                    {{ $item->lokasi ? '(' . $item->lokasi . ')' : '' }}
                                                </label>
                                            </div>
                                        @empty
                                            <em class="text-danger">Belum ada ruangan — input ruangan sidang dahulu.</em>
                                        @endforelse
                                    </div>
                                </div>
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input" id="gen_lewati_weekend" checked>
                                    <label class="custom-control-label" for="gen_lewati_weekend">Lewati Sabtu &
                                        Minggu</label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="gen_timpa">
                                    <label class="custom-control-label" for="gen_timpa">
                                        Hapus jadwal lama jenis ini & buat ulang semua
                                    </label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-magic mr-1"></i> Generate
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('css')
    @include('sidang.partials.table_styles')
@endsection

@section('js')
    @include('sidang.partials.table_scripts')
    <script>
        $(document).ready(function() {
            const isPanitia = @json($role === 'panitia');

            const columns = [
                { data: 'DT_RowIndex', orderable: false, searchable: false },
                {
                    data: 'jenis',
                    render: jenis => jenis === 'sidang'
                        ? '<span class="badge badge-soft-primary px-2 py-1">Sidang</span>'
                        : '<span class="badge badge-soft-info px-2 py-1">Bimbingan</span>'
                },
                { data: 'nama_siswa' },
                { data: 'kelas' },
                { data: 'nama_pembimbing' },
                { data: 'nama_ruangan' },
                { data: 'tanggal' },
                { data: 'jam', orderable: false },
            ];
            if (isPanitia) {
                columns.push({ data: 'aksi', orderable: false, searchable: false, className: 'text-center' });
            }

            const table = $('#jadwalTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('sidang.jadwal.data') }}',
                    data: d => { d.jenis = $('#filterJenis').val(); }
                },
                columns: columns,
            });

            $('#filterJenis').on('change', () => table.ajax.reload());

            if (!isPanitia) return;

            $('#btnTambah').on('click', function() {
                $('#formJadwal')[0].reset();
                $('#id').val('');
                $('#modalTitle').text('Tambah Jadwal');
                $('#modalForm').modal('show');
            });

            $(document).on('click', '.btnEdit', function() {
                $('#id').val($(this).data('id'));
                $('#siswa_id').val($(this).data('siswa_id'));
                $('#pembimbing_id').val($(this).data('pembimbing_id'));
                $('#ruangan_sidang_id').val($(this).data('ruangan_sidang_id'));
                $('#jenis').val($(this).data('jenis'));
                $('#tanggal').val($(this).data('tanggal'));
                $('#jam_mulai').val($(this).data('jam_mulai'));
                $('#jam_selesai').val($(this).data('jam_selesai'));
                $('#catatan').val($(this).data('catatan'));
                $('#modalTitle').text('Edit Jadwal');
                $('#modalForm').modal('show');
            });

            $('#formJadwal').on('submit', function(e) {
                e.preventDefault();
                const id = $('#id').val();
                const data = $(this).serializeArray();
                if (id) data.push({ name: '_method', value: 'PUT' });

                $.post(id ? `{{ url('sidang/jadwal') }}/${id}` : '{{ route('sidang.jadwal.store') }}',
                        $.param(data))
                    .done(() => {
                        $('#modalForm').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire('Berhasil', 'Jadwal tersimpan.', 'success');
                    })
                    .fail(xhr => Swal.fire('Gagal',
                        xhr.responseJSON?.message || 'Jadwal tidak dapat disimpan.', 'error'));
            });

            $(document).on('click', '.btnHapus', function() {
                const id = $(this).data('id');
                konfirmasiHapus({ title: 'Hapus jadwal ini?' }, () => {
                    $.ajax({ url: `{{ url('sidang/jadwal') }}/${id}`, type: 'DELETE' })
                        .done(() => {
                            table.ajax.reload(null, false);
                            Swal.fire('Terhapus', 'Jadwal berhasil dihapus.', 'success');
                        })
                        .fail(() => Swal.fire('Gagal', 'Jadwal tidak dapat dihapus.', 'error'));
                });
            });

            $('#btnGenerate').on('click', () => $('#modalGenerate').modal('show'));

            $('#formGenerate').on('submit', function(e) {
                e.preventDefault();
                const ruanganIds = $('.gen-ruangan:checked').map(function() {
                    return $(this).val();
                }).get();

                if (!ruanganIds.length) {
                    Swal.fire('Perhatian', 'Pilih minimal satu ruangan.', 'warning');
                    return;
                }

                $.post('{{ route('sidang.jadwal.generate') }}', {
                        jenis: $('#gen_jenis').val(),
                        tanggal_mulai: $('#gen_tanggal').val(),
                        jam_mulai: $('#gen_jam_mulai').val(),
                        jam_selesai: $('#gen_jam_selesai').val(),
                        durasi_menit: $('#gen_durasi').val(),
                        ruangan_ids: ruanganIds,
                        lewati_akhir_pekan: $('#gen_lewati_weekend').is(':checked') ? 1 : 0,
                        timpa_jadwal: $('#gen_timpa').is(':checked') ? 1 : 0,
                    })
                    .done(response => {
                        $('#modalGenerate').modal('hide');
                        table.ajax.reload();
                        Swal.fire('Berhasil', response.message, 'success');
                    })
                    .fail(xhr => Swal.fire('Gagal',
                        xhr.responseJSON?.message || 'Jadwal tidak dapat digenerate.', 'error'));
            });
        });
    </script>
@endsection
