@extends('adminlte::page')

@section('title', 'Kriteria Sidang')

@section('content')
    <div class="container-fluid pt-4">
        <div class="card sidang-card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center my-1">
                    <span class="card-title-icon"><i class="fas fa-list-ol"></i></span>
                    <div>
                        <h4>Kriteria Penilaian Sidang</h4>
                        <small>Nilai akhir = &Sigma;(nilai &times; bobot) / &Sigma;bobot &mdash; total bobot sebaiknya 100%</small>
                    </div>
                </div>
                <div class="my-1">
                    <span class="badge badge-pill badge-soft-primary mr-2 py-2 px-3" style="font-size: 13px;">
                        Total Bobot: <strong><span id="totalBobot">{{ $totalBobot }}</span>%</strong>
                    </span>
                    <button class="btn btn-primary" id="btnTambah">
                        <i class="fas fa-plus mr-1"></i> Tambah Kriteria
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table id="kriteriaTable" class="table table-striped table-hover" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th style="width: 20%;">Kriteria</th>
                            <th style="width: 90px;" class="text-center">Bobot</th>
                            <th>Deskripsi</th>
                            <th style="width: 32%;">Rubrik Penilaian</th>
                            <th style="width: 100px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        <div class="modal fade" id="modalForm" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <form id="formKriteria">
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTitle">Tambah Kriteria</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Nama Kriteria <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nama_kriteria" id="nama_kriteria"
                                        placeholder="Contoh: Penguasaan Materi" required>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>Bobot (%) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="bobot" id="bobot" min="1"
                                        max="100" required>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>Urutan</label>
                                    <input type="number" class="form-control" name="urutan" id="urutan" min="0"
                                        value="0">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Deskripsi</label>
                                <textarea class="form-control" name="deskripsi" id="deskripsi" rows="2"></textarea>
                            </div>

                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong>Rubrik Penilaian</strong>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnTambahRubrik">
                                    <i class="fas fa-plus mr-1"></i> Tambah Baris Rubrik
                                </button>
                            </div>
                            <table class="table table-sm table-bordered mb-0" id="rubrikTable">
                                <thead>
                                    <tr>
                                        <th style="width: 22%;">Label</th>
                                        <th style="width: 13%;">Nilai Min</th>
                                        <th style="width: 13%;">Nilai Max</th>
                                        <th>Deskripsi</th>
                                        <th style="width: 50px;"></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
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
    </div>
@endsection

@section('css')
    @include('sidang.partials.table_styles')
@endsection

@section('js')
    @include('sidang.partials.table_scripts')
    <script>
        $(document).ready(function() {
            let kriteriaCache = [];

            const table = $('#kriteriaTable').DataTable({
                processing: true,
                ajax: {
                    url: '{{ route('sidang.kriteria.data') }}',
                    dataSrc: function(response) {
                        kriteriaCache = response.data || [];
                        $('#totalBobot').text(response.total_bobot);
                        return kriteriaCache;
                    }
                },
                ordering: false,
                columns: [
                    {
                        data: null,
                        render: (data, type, row, meta) => meta.row + 1
                    },
                    {
                        data: 'nama_kriteria',
                        render: nama => `<strong>${escapeHtml(nama)}</strong>`
                    },
                    {
                        data: 'bobot',
                        className: 'text-center',
                        render: bobot => `<span class="badge badge-soft-primary px-2 py-1">${bobot}%</span>`
                    },
                    {
                        data: 'deskripsi',
                        render: deskripsi => deskripsi
                            ? escapeHtml(deskripsi)
                            : '<span class="text-muted">-</span>'
                    },
                    {
                        data: 'rubriks',
                        render: rubriks => rubriks.length
                            ? rubriks.map(r =>
                                `<span class="badge badge-rubrik" title="${escapeHtml(r.deskripsi ?? '')}">
                                    ${escapeHtml(r.label)} <span class="text-muted">${r.nilai_min}&ndash;${r.nilai_max}</span>
                                </span>`).join(' ')
                            : '<span class="text-muted">Belum ada rubrik</span>'
                    },
                    {
                        data: 'id',
                        className: 'text-center',
                        render: id => aksiButtons([
                            btnEditHtml(`data-id="${id}"`),
                            btnHapusHtml(id),
                        ])
                    },
                ],
            });

            function rubrikRow(rubrik) {
                rubrik = rubrik || {};
                return `<tr>
                    <td><input type="text" class="form-control form-control-sm rubrik-label"
                        value="${escapeHtml(rubrik.label ?? '')}" placeholder="Sangat Baik" required></td>
                    <td><input type="number" class="form-control form-control-sm rubrik-min" min="0" max="100"
                        value="${rubrik.nilai_min ?? ''}" required></td>
                    <td><input type="number" class="form-control form-control-sm rubrik-max" min="0" max="100"
                        value="${rubrik.nilai_max ?? ''}" required></td>
                    <td><input type="text" class="form-control form-control-sm rubrik-deskripsi"
                        value="${escapeHtml(rubrik.deskripsi ?? '')}" placeholder="Deskripsi capaian"></td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btnHapusRubrik">
                        <i class="fas fa-times"></i></button></td>
                </tr>`;
            }

            $('#btnTambah').on('click', function() {
                $('#formKriteria')[0].reset();
                $('#id').val('');
                $('#rubrikTable tbody').html(
                    rubrikRow({ label: 'Sangat Baik', nilai_min: 90, nilai_max: 100 }) +
                    rubrikRow({ label: 'Baik', nilai_min: 80, nilai_max: 89 }) +
                    rubrikRow({ label: 'Cukup', nilai_min: 70, nilai_max: 79 }) +
                    rubrikRow({ label: 'Perlu Bimbingan', nilai_min: 0, nilai_max: 69 })
                );
                $('#modalTitle').text('Tambah Kriteria');
                $('#modalForm').modal('show');
            });

            $(document).on('click', '.btnEdit', function() {
                const item = kriteriaCache.find(k => k.id === $(this).data('id'));
                if (!item) return;
                $('#id').val(item.id);
                $('#nama_kriteria').val(item.nama_kriteria);
                $('#bobot').val(item.bobot);
                $('#urutan').val(item.urutan);
                $('#deskripsi').val(item.deskripsi);
                $('#rubrikTable tbody').html(item.rubriks.map(rubrikRow).join(''));
                $('#modalTitle').text('Edit Kriteria');
                $('#modalForm').modal('show');
            });

            $('#btnTambahRubrik').on('click', () => $('#rubrikTable tbody').append(rubrikRow()));
            $(document).on('click', '.btnHapusRubrik', function() {
                $(this).closest('tr').remove();
            });

            $('#formKriteria').on('submit', function(e) {
                e.preventDefault();
                const id = $('#id').val();
                const payload = {
                    nama_kriteria: $('#nama_kriteria').val(),
                    bobot: $('#bobot').val(),
                    urutan: $('#urutan').val(),
                    deskripsi: $('#deskripsi').val(),
                    rubriks: $('#rubrikTable tbody tr').map(function() {
                        return {
                            label: $(this).find('.rubrik-label').val(),
                            nilai_min: $(this).find('.rubrik-min').val(),
                            nilai_max: $(this).find('.rubrik-max').val(),
                            deskripsi: $(this).find('.rubrik-deskripsi').val(),
                        };
                    }).get(),
                };
                if (id) payload._method = 'PUT';

                $.post(id ? `{{ url('sidang/kriteria') }}/${id}` : '{{ route('sidang.kriteria.store') }}', payload)
                    .done(() => {
                        $('#modalForm').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire('Berhasil', 'Kriteria sidang tersimpan.', 'success');
                    })
                    .fail(xhr => Swal.fire('Gagal',
                        xhr.responseJSON?.message || 'Kriteria tidak dapat disimpan.', 'error'));
            });

            $(document).on('click', '.btnHapus', function() {
                const id = $(this).data('id');
                konfirmasiHapus({
                    title: 'Hapus kriteria ini?',
                    text: 'Nilai sidang yang sudah diinput untuk kriteria ini ikut terhapus.'
                }, () => {
                    $.ajax({ url: `{{ url('sidang/kriteria') }}/${id}`, type: 'DELETE' })
                        .done(() => {
                            table.ajax.reload(null, false);
                            Swal.fire('Terhapus', 'Kriteria berhasil dihapus.', 'success');
                        })
                        .fail(() => Swal.fire('Gagal', 'Kriteria tidak dapat dihapus.', 'error'));
                });
            });
        });
    </script>
@endsection
