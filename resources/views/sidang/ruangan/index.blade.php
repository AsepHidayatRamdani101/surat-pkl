@extends('adminlte::page')

@section('title', 'Ruangan Sidang')

@section('content')
    <div class="container-fluid pt-4">
        <div class="card sidang-card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center my-1">
                    <span class="card-title-icon"><i class="fas fa-door-open"></i></span>
                    <div>
                        <h4>Ruangan Sidang</h4>
                        <small>Kelola ruangan untuk bimbingan & sidang PKL</small>
                    </div>
                </div>
                <div class="my-1">
                    <button class="btn btn-primary" id="btnTambah">
                        <i class="fas fa-plus mr-1"></i> Tambah Ruangan
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table id="ruanganTable" class="table table-striped table-hover" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Nama Ruangan</th>
                            <th>Lokasi</th>
                            <th style="width: 110px;">Kapasitas</th>
                            <th>Keterangan</th>
                            <th style="width: 100px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        <div class="modal fade" id="modalForm" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <form id="formRuangan">
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTitle">Tambah Ruangan</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Nama Ruangan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama_ruangan" id="nama_ruangan" required>
                            </div>
                            <div class="form-group">
                                <label>Lokasi</label>
                                <input type="text" class="form-control" name="lokasi" id="lokasi"
                                    placeholder="Contoh: Gedung A Lantai 2">
                            </div>
                            <div class="form-group">
                                <label>Kapasitas</label>
                                <input type="number" class="form-control" name="kapasitas" id="kapasitas" min="1">
                            </div>
                            <div class="form-group">
                                <label>Keterangan</label>
                                <textarea class="form-control" name="keterangan" id="keterangan" rows="2"></textarea>
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
    </div>
@endsection

@section('css')
    @include('sidang.partials.table_styles')
@endsection

@section('js')
    @include('sidang.partials.table_scripts')
    <script>
        $(document).ready(function() {
            const table = $('#ruanganTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('sidang.ruangan.data') }}',
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'nama_ruangan' },
                    { data: 'lokasi', defaultContent: '-' },
                    { data: 'kapasitas', defaultContent: '-', className: 'text-center' },
                    { data: 'keterangan', defaultContent: '-' },
                    { data: 'aksi', orderable: false, searchable: false, className: 'text-center' },
                ],
            });

            $('#btnTambah').on('click', function() {
                $('#formRuangan')[0].reset();
                $('#id').val('');
                $('#modalTitle').text('Tambah Ruangan');
                $('#modalForm').modal('show');
            });

            $(document).on('click', '.btnEdit', function() {
                $('#id').val($(this).data('id'));
                $('#nama_ruangan').val($(this).data('nama_ruangan'));
                $('#lokasi').val($(this).data('lokasi'));
                $('#kapasitas').val($(this).data('kapasitas'));
                $('#keterangan').val($(this).data('keterangan'));
                $('#modalTitle').text('Edit Ruangan');
                $('#modalForm').modal('show');
            });

            $('#formRuangan').on('submit', function(e) {
                e.preventDefault();
                const id = $('#id').val();
                const data = $(this).serializeArray();
                if (id) data.push({ name: '_method', value: 'PUT' });

                $.post(id ? `{{ url('sidang/ruangan') }}/${id}` : '{{ route('sidang.ruangan.store') }}',
                        $.param(data))
                    .done(() => {
                        $('#modalForm').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire('Berhasil', 'Data ruangan tersimpan.', 'success');
                    })
                    .fail(xhr => Swal.fire('Gagal',
                        xhr.responseJSON?.message || 'Data tidak dapat disimpan.', 'error'));
            });

            $(document).on('click', '.btnHapus', function() {
                const id = $(this).data('id');
                konfirmasiHapus({
                    title: 'Hapus ruangan ini?',
                    text: 'Jadwal yang memakai ruangan ini akan kehilangan ruangannya.'
                }, () => {
                    $.ajax({ url: `{{ url('sidang/ruangan') }}/${id}`, type: 'DELETE' })
                        .done(() => {
                            table.ajax.reload(null, false);
                            Swal.fire('Terhapus', 'Ruangan berhasil dihapus.', 'success');
                        })
                        .fail(() => Swal.fire('Gagal', 'Ruangan tidak dapat dihapus.', 'error'));
                });
            });
        });
    </script>
@endsection
