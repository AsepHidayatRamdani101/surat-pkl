@extends('adminlte::page')

@section('title', 'Penilaian Sidang PKL')

@section('content')
    <div class="container-fluid pt-4">
        <div class="card sidang-card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center my-1">
                    <span class="card-title-icon"><i class="fas fa-star"></i></span>
                    <div>
                        <h4>Penilaian Sidang PKL</h4>
                        <small>
                            @if ($role === 'panitia')
                                Input nilai per kriteria & generate sertifikat hasil sidang
                            @else
                                Input nilai sidang siswa bimbingan Anda sesuai kriteria
                            @endif
                        </small>
                    </div>
                </div>
                @if ($role === 'panitia')
                    <div class="my-1">
                        <button class="btn btn-success" id="btnGenerateSertifikat" disabled>
                            <i class="fas fa-certificate mr-1"></i> Generate Sertifikat Terpilih
                        </button>
                    </div>
                @endif
            </div>
            <div class="card-body">
                @if ($kriteriaList->isEmpty())
                    <div class="alert alert-warning mb-0">
                        Belum ada kriteria sidang. Hubungi admin/panitia untuk menginput kriteria penilaian.
                    </div>
                @else
                    <table class="table table-striped table-hover" id="penilaianTable" style="width: 100%;">
                        <thead>
                            <tr>
                                @if ($role === 'panitia')
                                    <th style="width: 34px;" class="text-center">
                                        <input type="checkbox" id="checkAll">
                                    </th>
                                @endif
                                <th style="width: 50px;">No</th>
                                <th>Siswa</th>
                                <th>Kelas</th>
                                <th>Laporan</th>
                                <th style="width: 140px;">Progres Nilai</th>
                                <th style="width: 140px;">Nilai Akhir</th>
                                <th style="width: 130px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                @endif
            </div>
        </div>

        {{-- Modal input nilai --}}
        <div class="modal fade" id="modalNilai" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="nilaiModalTitle">Input Nilai Sidang</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="nilaiSiswaId">
                        <div id="laporanInfo" class="mb-3"></div>
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th style="width: 22%;">Kriteria</th>
                                    <th style="width: 8%;" class="text-center">Bobot</th>
                                    <th>Rubrik</th>
                                    <th style="width: 12%;">Nilai (0-100)</th>
                                    <th style="width: 20%;">Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($kriteriaList as $kriteria)
                                    <tr>
                                        <td>
                                            <strong>{{ $kriteria->nama_kriteria }}</strong>
                                            @if ($kriteria->deskripsi)
                                                <br><small class="text-muted">{{ $kriteria->deskripsi }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-soft-primary px-2 py-1">{{ $kriteria->bobot }}%</span>
                                        </td>
                                        <td>
                                            @if ($kriteria->rubriks->isNotEmpty())
                                                @foreach ($kriteria->rubriks as $rubrik)
                                                    <span class="badge badge-rubrik" title="{{ $rubrik->deskripsi }}">
                                                        {{ $rubrik->label }}
                                                        <span class="text-muted">{{ $rubrik->nilai_min }}&ndash;{{ $rubrik->nilai_max }}</span>
                                                    </span>
                                                @endforeach
                                            @else
                                                <small class="text-muted">-</small>
                                            @endif
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm nilai-input"
                                                data-kriteria="{{ $kriteria->id }}" min="0" max="100"
                                                step="0.01">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm catatan-input"
                                                data-kriteria="{{ $kriteria->id }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="text-right">
                            <span class="badge badge-pill badge-soft-primary py-2 px-3" style="font-size: 14px;">
                                Perkiraan Nilai Akhir: <strong id="previewNilaiAkhir">-</strong>
                            </span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="btnSimpanNilai">
                            <i class="fas fa-save mr-1"></i> Simpan Nilai
                        </button>
                    </div>
                </div>
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
            const isPanitia = @json($role === 'panitia');
            const bobotMap = @json($kriteriaList->pluck('bobot', 'id'));
            let dataCache = [];

            if (!$('#penilaianTable').length) return;

            const columns = [];
            if (isPanitia) {
                columns.push({
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: id => `<input type="checkbox" class="check-siswa" value="${id}">`
                });
            }
            columns.push(
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: (data, type, row, meta) => meta.row + 1
                },
                {
                    data: 'nama_siswa',
                    render: nama => `<strong>${escapeHtml(nama)}</strong>`
                },
                { data: 'kelas', defaultContent: '-' },
                {
                    data: 'laporan',
                    orderable: false,
                    render: laporan => laporan
                        ? `<a href="${laporan.url}" target="_blank">
                            <i class="fas fa-file-${laporan.tipe_file === 'pdf' ? 'pdf text-danger' : 'powerpoint text-warning'} mr-1"></i>
                            ${escapeHtml(laporan.judul)}</a>`
                        : '<span class="text-muted">Belum upload</span>'
                },
                {
                    data: null,
                    orderable: false,
                    render: row => {
                        const lengkap = row.total_kriteria > 0 && row.kriteria_dinilai >= row.total_kriteria;
                        return `${row.kriteria_dinilai}/${row.total_kriteria} kriteria
                            ${lengkap ? '<i class="fas fa-check-circle text-success ml-1"></i>' : ''}`;
                    }
                },
                {
                    data: null,
                    orderable: false,
                    render: row => `${row.nilai_akhir !== null ? `<strong>${row.nilai_akhir}</strong>` : '-'}
                        ${row.sertifikat ? '<span class="badge badge-soft-success ml-1 px-2 py-1">Sertifikat</span>' : ''}`
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: row => aksiButtons([
                        `<button type="button" class="btn btn-outline-primary btn-sm btnNilai" title="Input nilai"
                            data-id="${row.id}" style="width: auto; padding-left: 10px; padding-right: 10px;">
                            <i class="fas fa-pen mr-1"></i> Nilai</button>`,
                        ...(row.sertifikat && isPanitia ? [
                            `<a class="btn btn-outline-success btn-sm" title="Lihat sertifikat"
                                href="{{ url('sidang/sertifikat/cetak') }}/${row.id}" target="_blank">
                                <i class="fas fa-certificate"></i></a>`
                        ] : []),
                    ])
                }
            );

            const table = $('#penilaianTable').DataTable({
                processing: true,
                ajax: {
                    url: '{{ route('sidang.penilaian.data') }}',
                    dataSrc: function(response) {
                        dataCache = response.data || [];
                        return dataCache;
                    }
                },
                ordering: false,
                columns: columns,
                drawCallback: function() {
                    $('#checkAll').prop('checked', false);
                    $('#btnGenerateSertifikat').prop('disabled', true);
                }
            });

            function hitungPreview() {
                let jumlah = 0, totalBobot = 0;
                $('.nilai-input').each(function() {
                    const nilai = parseFloat($(this).val());
                    if (isNaN(nilai)) return;
                    const bobot = parseInt(bobotMap[$(this).data('kriteria')]) || 0;
                    jumlah += nilai * bobot;
                    totalBobot += bobot;
                });
                $('#previewNilaiAkhir').text(totalBobot > 0 ? (jumlah / totalBobot).toFixed(2) : '-');
            }

            $(document).on('input', '.nilai-input', hitungPreview);

            $(document).on('click', '.btnNilai', function() {
                const row = dataCache.find(item => item.id === $(this).data('id'));
                if (!row) return;

                $('#nilaiSiswaId').val(row.id);
                $('#nilaiModalTitle').text(`Input Nilai Sidang — ${row.nama_siswa}`);
                $('#laporanInfo').html(row.laporan
                    ? `<div class="alert alert-light border mb-0 py-2">
                        <i class="fas fa-file-${row.laporan.tipe_file === 'pdf' ? 'pdf text-danger' : 'powerpoint text-warning'} mr-1"></i>
                        Laporan: <a href="${row.laporan.url}" target="_blank">${escapeHtml(row.laporan.judul)}</a></div>`
                    : '<div class="alert alert-warning mb-0 py-2">Siswa belum mengupload laporan PKL.</div>');

                $('.nilai-input, .catatan-input').val('');
                Object.entries(row.nilai || {}).forEach(([kriteriaId, item]) => {
                    $(`.nilai-input[data-kriteria="${kriteriaId}"]`).val(item.nilai);
                    $(`.catatan-input[data-kriteria="${kriteriaId}"]`).val(item.catatan ?? '');
                });
                hitungPreview();
                $('#modalNilai').modal('show');
            });

            $('#btnSimpanNilai').on('click', function() {
                const nilai = [];
                $('.nilai-input').each(function() {
                    const value = $(this).val();
                    if (value === '') return;
                    const kriteriaId = $(this).data('kriteria');
                    nilai.push({
                        kriteria_sidang_id: kriteriaId,
                        nilai: value,
                        catatan: $(`.catatan-input[data-kriteria="${kriteriaId}"]`).val() || null,
                    });
                });

                if (!nilai.length) {
                    Swal.fire('Perhatian', 'Isi minimal satu nilai kriteria.', 'warning');
                    return;
                }

                $.post('{{ route('sidang.penilaian.store') }}', {
                        siswa_id: $('#nilaiSiswaId').val(),
                        nilai: nilai,
                    })
                    .done(() => {
                        $('#modalNilai').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire('Berhasil', 'Nilai sidang tersimpan.', 'success');
                    })
                    .fail(xhr => Swal.fire('Gagal',
                        xhr.responseJSON?.message || 'Nilai tidak dapat disimpan.', 'error'));
            });

            if (isPanitia) {
                $(document).on('change', '#checkAll', function() {
                    $('.check-siswa').prop('checked', this.checked).trigger('change');
                });

                $(document).on('change', '.check-siswa', function() {
                    $('#btnGenerateSertifikat').prop('disabled', !$('.check-siswa:checked').length);
                });

                $('#btnGenerateSertifikat').on('click', function() {
                    const ids = $('.check-siswa:checked').map(function() { return $(this).val(); }).get();
                    if (!ids.length) return;

                    Swal.fire({
                        title: `Generate sertifikat untuk ${ids.length} siswa?`,
                        text: 'Hanya siswa dengan nilai lengkap di semua kriteria yang diproses.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, generate',
                        cancelButtonText: 'Batal'
                    }).then(result => {
                        if (!result.isConfirmed) return;
                        $.post('{{ route('sidang.sertifikat.generate') }}', { siswa_ids: ids })
                            .done(response => {
                                table.ajax.reload(null, false);
                                Swal.fire('Selesai', response.message, 'success');
                            })
                            .fail(xhr => Swal.fire('Gagal',
                                xhr.responseJSON?.message || 'Sertifikat tidak dapat digenerate.', 'error'));
                    });
                });
            }
        });
    </script>
@endsection
