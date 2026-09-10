@extends('adminlte::page')

@section('title', $title)

@section('content_header')
    <h1>
        {{ $title }}</h1>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            @if ($type === 'setting')
                <form id="settingForm" class="form-row align-items-end">
                    <div class="col-md-3 form-group"><label>Status Absensi</label><select name="is_active"
                            class="form-control">
                            <option value="1" {{ optional($setting)->is_active ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ !optional($setting)->is_active ? 'selected' : '' }}>Nonaktif</option>
                        </select></div>
                    <div class="col-md-3 form-group"><label>Periode Dimulai</label><input type="date"
                            name="periode_dimulai_pada" class="form-control"
                            value="{{ optional(optional($setting)->periode_dimulai_pada)->format('Y-m-d') }}"></div>
                    <div class="col-md-3 form-group"><label>Interval</label><input type="number" name="interval_hari"
                            class="form-control" value="14" readonly></div>
                    <div class="col-md-3 form-group"><button class="btn btn-primary" type="submit">Simpan Setting</button>
                    </div>
                </form>
            @else
                <div class="d-flex justify-content-between mb-3">
                    <h5>{{ $title }}</h5><button class="btn btn-primary btn-sm" id="addRecord"><i
                            class="fas fa-plus mr-1"></i> Tambah Data</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="pklTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                @if (!$isSiswa)
                                    <th>Siswa</th>
                                    <th>Kelas</th>
                                    @endif @if ($type === 'absensi')
                                        <th>Pembimbing</th>
                                        <th>Periode</th>
                                        <th>Status</th>
                                        <th>Keterangan</th>
                                    @elseif ($type === 'resume')
                                        <th>Bulan</th>
                                        <th>Judul</th>
                                        <th>Resume</th>
                                    <th>Status</th>@else<th>Tanggal</th>
                                        <th>Judul</th>
                                        <th>Keterangan</th>
                                        <th>Berkas</th>
                                        <th>Status</th>
                                    @endif
                                    <th>
                                        Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection

@if ($type !== 'setting')
    <div class="modal fade" id="recordModal">
        <div class="modal-dialog modal-lg">
            <form id="recordForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $title }}</h5><button type="button" class="close"
                            data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body"><input type="hidden" id="recordId">
                        @if (!$isSiswa)
                            <div class="form-group"><label>Siswa</label><select name="siswa_id" id="siswa_id"
                                    class="form-control" required>
                                    <option value="">Pilih siswa</option>
                                    @foreach ($siswaOptions as $siswa)
                                        <option value="{{ $siswa->id }}">{{ $siswa->nama_siswa }} -
                                            {{ $siswa->kelas->nama_kelas ?? '-' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        @if ($type === 'absensi')
                            @if ($isPanitia)
                                <div class="form-group"><label>Pembimbing</label><select name="pembimbing_id"
                                        id="pembimbing_id" class="form-control" required>
                                        <option value="">Pilih pembimbing</option>
                                        @foreach ($pembimbingOptions as $pembimbing)
                                            <option value="{{ $pembimbing->id }}">{{ $pembimbing->nama_pembimbing }}
                                            </option>
                                        @endforeach
                                    </select></div>
                            @endif
                            <div class="form-row">
                                <div class="form-group col-md-6"><label>Periode Mulai</label><input type="date"
                                        name="periode_mulai" id="periode_mulai" class="form-control" required></div>
                                <div class="form-group col-md-6"><label>Periode Selesai</label><input type="date"
                                        name="periode_selesai" id="periode_selesai" class="form-control" required></div>
                            </div>
                            <div class="form-group"><label>Status</label><select name="status" id="status"
                                    class="form-control">
                                    <option value="hadir">Hadir</option>
                                    <option value="izin">Izin</option>
                                    <option value="sakit">Sakit</option>
                                    <option value="alpa">Alpa</option>
                                </select></div>
                            <div class="form-group"><label>Keterangan</label>
                                <textarea name="keterangan" id="keterangan" class="form-control"></textarea>
                            </div>
                        @elseif ($type === 'resume')
                            <div class="form-row">
                                <div class="form-group col-md-6"><label>Bulan</label><input type="month"
                                        name="bulan" id="bulan" class="form-control" required></div>
                                <div class="form-group col-md-6"><label>Judul</label><input type="text"
                                        name="judul" id="judul" class="form-control" required></div>
                            </div>
                            <div class="form-group"><label>Isi Resume</label>
                                <textarea name="isi_resume" id="isi_resume" class="form-control" rows="5" required></textarea>
                            </div>
                            <div class="form-group"><label>Lampiran</label><input type="file" name="file"
                                    class="form-control-file" accept=".pdf,.doc,.docx"></div>
                        @else
                            <div class="form-row">
                                <div class="form-group col-md-6"><label>Tanggal</label><input type="date"
                                        name="tanggal" id="tanggal" class="form-control" required></div>
                                <div class="form-group col-md-6"><label>Judul Jurnal</label><input type="text"
                                        name="judul" id="judul" class="form-control" required></div>
                            </div>
                            <div class="form-group"><label>Keterangan</label>
                                <textarea name="keterangan" id="keterangan" class="form-control"></textarea>
                            </div>
                            <div class="form-group"><label>Berkas Jurnal</label><input type="file" name="file"
                                    class="form-control-file" accept=".pdf,.jpg,.jpeg,.png"></div>
                        @endif
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-dismiss="modal">Batal</button><button type="submit"
                            class="btn btn-primary">Simpan</button></div>
                </div>
            </form>
        </div>
    </div>
@endif

@section('js')
    <script>
        $(function() {
            const module = @json($module),
                type = @json($type),
                endpoint = `/pkl/${module}`,
                csrf = $('meta[name="csrf-token"]').attr('content'),
                dateValue = value => value ? value.substring(0, 10) : '';
            @if ($type === 'setting')
                $('#settingForm').on('submit', function(event) {
                    event.preventDefault();
                    $.ajax({
                        url: endpoint,
                        method: 'POST',
                        data: $(this).serialize(),
                        headers: {
                            'X-CSRF-TOKEN': csrf
                        }
                    }).done(() => Swal.fire({
                        icon: 'success',
                        title: 'Setting tersimpan',
                        timer: 1200,
                        showConfirmButton: false
                    })).fail(xhr => Swal.fire('Gagal', xhr.responseJSON?.message ||
                        'Data tidak dapat disimpan.', 'error'));
                });
                return;
            @endif
            const table = $('#pklTable').DataTable({
                ajax: {
                    url: `${endpoint}/data`,
                    dataSrc: ''
                },
                columns: [{
                        data: null,
                        render: (value, display, record, meta) => meta.row + 1
                    },
                    @if (!$isSiswa)
                        {
                            data: 'siswa.nama_siswa',
                            defaultContent: '-'
                        }, {
                            data: 'siswa.kelas.nama_kelas',
                            defaultContent: '-'
                        },
                    @endif
                    @if ($type === 'absensi')
                        {
                            data: 'pembimbing.nama_pembimbing',
                            defaultContent: '-'
                        }, {
                            data: null,
                            render: record =>
                                `${dateValue(record.periode_mulai)} s.d. ${dateValue(record.periode_selesai)}`
                        }, {
                            data: 'status'
                        }, {
                            data: 'keterangan',
                            defaultContent: '-'
                        },
                    @elseif ($type === 'resume') {
                            data: 'bulan',
                            render: dateValue
                        }, {
                            data: 'judul'
                        }, {
                            data: 'isi_resume'
                        }, {
                            data: 'status'
                        },
                    @else
                        {
                            data: 'tanggal',
                            render: dateValue
                        }, {
                            data: 'judul'
                        }, {
                            data: 'keterangan',
                            defaultContent: '-'
                        }, {
                            data: 'file_path',
                            render: value => value ?
                                `<a href="/storage/${value}" target="_blank">Lihat</a>` : '-'
                        }, {
                            data: 'status'
                        },
                    @endif {
                        data: null,
                        orderable: false,
                        render: () =>
                            '<button class="btn btn-xs btn-warning edit-record">Edit</button> <button class="btn btn-xs btn-danger delete-record">Hapus</button>'
                    }
                ]
            });
            $('#addRecord').on('click', () => {
                $('#recordForm')[0].reset();
                $('#recordId').val('');
                $('#recordModal').modal('show');
            });
            $('#pklTable').on('click', '.edit-record', function() {
                const record = table.row($(this).closest('tr')).data();
                $('#recordForm')[0].reset();
                $('#recordId').val(record.id);
                Object.entries(record).forEach(([key, value]) => {
                    const input = $(`#recordForm [name="${key}"]`);
                    if (input.length && value !== null) input.val(key === 'bulan' ? dateValue(value)
                        .slice(0, 7) : (key.includes('periode') || key === 'tanggal' ?
                            dateValue(value) : value));
                });
                $('#siswa_id').val(record.siswa_id);
                $('#pembimbing_id').val(record.pembimbing_id);
                $('#recordModal').modal('show');
            });
            $('#recordForm').on('submit', function(event) {
                event.preventDefault();
                const recordId = $('#recordId').val(),
                    formData = new FormData(this);
                if (recordId) formData.append('_method', 'PUT');
                $.ajax({
                    url: recordId ? `${endpoint}/${recordId}` : endpoint,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': csrf
                    }
                }).done(() => {
                    $('#recordModal').modal('hide');
                    table.ajax.reload(null, false);
                }).fail(xhr => Swal.fire('Gagal', xhr.responseJSON?.message ||
                    'Data tidak dapat disimpan.', 'error'));
            });
            $('#pklTable').on('click', '.delete-record', function() {
                const recordId = table.row($(this).closest('tr')).data().id;
                Swal.fire({
                    title: 'Hapus data?',
                    icon: 'warning',
                    showCancelButton: true
                }).then(result => {
                    if (result.isConfirmed) $.ajax({
                        url: `${endpoint}/${recordId}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrf
                        }
                    }).done(() => table.ajax.reload(null, false));
                });
            });
        });
    </script>
@endsection
