{{-- Card monitoring PKL (reusable): kehadiran, upload absensi jurnal, upload resume --}}
<div class="row" id="monitoringCards">
    <div class="col-lg-3 col-md-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3 id="cardTotalSiswa">-</h3>
                <p class="mb-0">Total Siswa PKL</p>
            </div>
            <div class="icon"><i class="fas fa-users"></i></div>
            <span class="small-box-footer" id="cardTotalInfo">&nbsp;</span>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3><span id="cardHadir">-</span> <sup style="font-size: 18px;" id="cardHadirPersen"></sup></h3>
                <p class="mb-0">Kehadiran Siswa Hari Ini</p>
            </div>
            <div class="icon"><i class="fas fa-user-check"></i></div>
            <a href="javascript:void(0)" class="small-box-footer monitoring-detail" data-kategori="kehadiran">
                <span id="cardHadirInfo">Lihat detail</span> <i class="fas fa-arrow-circle-right ml-1"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><span id="cardAbsensi">-</span> <sup style="font-size: 18px;" id="cardAbsensiPersen"></sup></h3>
                <p class="mb-0">Sudah Upload Absensi Jurnal</p>
            </div>
            <div class="icon"><i class="fas fa-upload"></i></div>
            <a href="javascript:void(0)" class="small-box-footer monitoring-detail" data-kategori="absensi">
                <span id="cardAbsensiInfo">Lihat detail</span> <i class="fas fa-arrow-circle-right ml-1"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3><span id="cardResume">-</span> <sup style="font-size: 18px;" id="cardResumePersen"></sup></h3>
                <p class="mb-0">Sudah Upload Resume Bulanan</p>
            </div>
            <div class="icon"><i class="fas fa-file-alt"></i></div>
            <a href="javascript:void(0)" class="small-box-footer monitoring-detail" data-kategori="resume">
                <span id="cardResumeInfo">Lihat detail</span> <i class="fas fa-arrow-circle-right ml-1"></i>
            </a>
        </div>
    </div>
</div>

<div class="modal fade" id="monitoringDetailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalTitle">Detail Monitoring</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="detailLoading" class="text-center py-4">
                    <span class="spinner-border spinner-border-sm mr-2"></span> Memuat detail...
                </div>
                <div id="detailContent" class="d-none">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#tabSudah" role="tab">
                                <i class="fas fa-check-circle text-success mr-1"></i>
                                Sudah (<span id="detailJumlahSudah">0</span>)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tabBelum" role="tab">
                                <i class="fas fa-times-circle text-danger mr-1"></i>
                                Belum (<span id="detailJumlahBelum">0</span>)
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="tabSudah" role="tabpanel">
                            <div class="table-responsive" style="max-height: 50vh;">
                                <table class="table table-sm table-striped table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 40px;">No</th>
                                            <th>Siswa</th>
                                            <th>Kelas</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detailBodySudah"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tabBelum" role="tabpanel">
                            <div class="table-responsive" style="max-height: 50vh;">
                                <table class="table table-sm table-striped table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 40px;">No</th>
                                            <th>Siswa</th>
                                            <th>Kelas</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detailBodyBelum"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    function initMonitoringCards(options) {
        const settings = $.extend({
            tanggal: () => null
        }, options || {});

        const escapeHtml = value => $('<div>').text(value ?? '').html();

        function muatMonitoring() {
            $.getJSON('{{ route('absensi-selfie.monitoring') }}', {
                tanggal: settings.tanggal() || null
            }, function(stats) {
                $('#cardTotalSiswa').text(stats.total_siswa);
                $('#cardTotalInfo').text(`Periode ${stats.bulan}`);

                $('#cardHadir').text(stats.kehadiran.jumlah);
                $('#cardHadirPersen').text(`${stats.kehadiran.persentase}%`);
                $('#cardHadirInfo').text(
                    `${stats.kehadiran.persentase}% dari total siswa (${stats.tanggal}) — lihat detail`);

                $('#cardAbsensi').text(stats.upload_absensi.jumlah);
                $('#cardAbsensiPersen').text(`${stats.upload_absensi.persentase}%`);
                $('#cardAbsensiInfo').text(
                    `${stats.upload_absensi.persentase}% dari total siswa (${stats.bulan}) — lihat detail`);

                $('#cardResume').text(stats.upload_resume.jumlah);
                $('#cardResumePersen').text(`${stats.upload_resume.persentase}%`);
                $('#cardResumeInfo').text(
                    `${stats.upload_resume.persentase}% dari total siswa (${stats.bulan}) — lihat detail`);
            });
        }

        $(document).on('click', '.monitoring-detail', function() {
            const kategori = $(this).data('kategori');
            $('#detailModalTitle').text('Detail Monitoring');
            $('#detailLoading').removeClass('d-none');
            $('#detailContent').addClass('d-none');
            $('#monitoringDetailModal').modal('show');

            $.getJSON('{{ route('absensi-selfie.monitoring.detail') }}', {
                kategori: kategori,
                tanggal: settings.tanggal() || null
            }, function(detail) {
                $('#detailModalTitle').text(detail.judul);
                $('#detailJumlahSudah').text(detail.jumlah_sudah);
                $('#detailJumlahBelum').text(detail.jumlah_belum);

                $('#detailBodySudah').html((detail.sudah || []).map((row, index) => `<tr>
                    <td>${index + 1}</td>
                    <td>${escapeHtml(row.nama_siswa)}</td>
                    <td>${escapeHtml(row.kelas ?? '-')}</td>
                    <td>${escapeHtml(row.keterangan ?? '-')}</td>
                </tr>`).join('') ||
                    '<tr><td colspan="4" class="text-center text-muted">Belum ada siswa.</td></tr>');

                $('#detailBodyBelum').html((detail.belum || []).map((row, index) => `<tr>
                    <td>${index + 1}</td>
                    <td>${escapeHtml(row.nama_siswa)}</td>
                    <td>${escapeHtml(row.kelas ?? '-')}</td>
                </tr>`).join('') ||
                    '<tr><td colspan="3" class="text-center text-muted">Semua siswa sudah melakukan aktivitas ini.</td></tr>');

                $('#detailLoading').addClass('d-none');
                $('#detailContent').removeClass('d-none');
            }).fail(function() {
                $('#monitoringDetailModal').modal('hide');
                Swal.fire('Gagal', 'Detail monitoring tidak dapat dimuat.', 'error');
            });
        });

        $('#monitoringDetailModal').appendTo('body');
        muatMonitoring();

        return { reload: muatMonitoring };
    }
</script>
