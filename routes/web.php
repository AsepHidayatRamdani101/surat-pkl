<?php

use App\Http\Controllers\JurusanController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\KelompokBimbinganController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\PembimbingController;
use App\Http\Controllers\PembimbingPerusahaanController;
use App\Http\Controllers\PerusahaanController;
use App\Http\Controllers\PembekalanController;
use App\Http\Controllers\MateriController;
use App\Http\Controllers\TugasPembekalanController;
use App\Http\Controllers\AbsensiPembekalanController;
use App\Http\Controllers\PembinaanPembekalanController;
use App\Http\Controllers\JawabanTugasSiswaController;
use App\Http\Controllers\NilaiTugasPembekalanController;
use App\Http\Controllers\NilaiSikapPembekalanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleManagementController;
use App\Http\Controllers\SekolahController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\SuratIzinOrtuController;
use App\Http\Controllers\TempatPklController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');
Route::get('/dashboard/siswa/absensi', [DashboardController::class, 'siswaAbsensi'])->middleware(['auth'])->name('dashboard.siswa.absensi');
Route::get('/dashboard/siswa/materi', [DashboardController::class, 'siswaMateri'])->middleware(['auth'])->name('dashboard.siswa.materi');
Route::get('/dashboard/siswa/materi/{materi}', [DashboardController::class, 'siswaMateriDetail'])->middleware(['auth'])->name('dashboard.siswa.materi.detail');
Route::get('/dashboard/siswa/tugas', [DashboardController::class, 'siswaTugas'])->middleware(['auth'])->name('dashboard.siswa.tugas');
Route::get('/dashboard/siswa/kerjakan-tugas', [DashboardController::class, 'siswaKerjakanTugas'])->middleware(['auth'])->name('dashboard.siswa.kerjakan-tugas');
Route::post('/dashboard/siswa/kerjakan-tugas', [DashboardController::class, 'siswaKerjakanTugasStore'])->middleware(['auth'])->name('dashboard.siswa.kerjakan-tugas.store');
Route::get('/dashboard/siswa/nilai', [DashboardController::class, 'siswaNilai'])->middleware(['auth'])->name('dashboard.siswa.nilai');
Route::get('/dashboard/siswa/sikap', [DashboardController::class, 'siswaSikap'])->middleware(['auth'])->name('dashboard.siswa.sikap');
Route::post('/dashboard/siswa/bimbingan/{id}/submit-tugas', [DashboardController::class, 'submitTugas'])->middleware(['auth'])->name('dashboard.siswa.submit-tugas');
Route::get('/dashboard/siswa/cetak-sertifikat', [DashboardController::class, 'cetakSertifikatPembekalan'])->middleware(['auth'])->name('dashboard.siswa.cetak-sertifikat');
Route::get('/dashboard/siswa/cetak-sertifikat/download', [DashboardController::class, 'downloadSertifikatPembekalan'])->middleware(['auth'])->name('dashboard.siswa.download-sertifikat');
Route::post('/dashboard/pembimbing/bimbingan/{id}/update-nilai', [DashboardController::class, 'updateNilaiTugasPembimbing'])->middleware(['auth'])->name('dashboard.pembimbing.update-nilai');
Route::post('/dashboard/pembimbing/bimbingan/{id}/update-evaluasi', [DashboardController::class, 'updateEvaluasiSiswaPembimbing'])->middleware(['auth'])->name('dashboard.pembimbing.update-evaluasi');

Route::middleware('auth')->group(function () {
    Route::view('/evaluasi/jurnal', 'evaluasi.penilaian_jurnal')->name('evaluasi.jurnal');
    Route::view('/evaluasi/laporan-ppt', 'evaluasi.penilaian_laporan_ppt')->name('evaluasi.laporan-ppt');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::view('/tempat-pkl', 'tempat_pkl');
    Route::view('/surat-izin', 'surat_izin');
    Route::view('/upload-kesediaan', 'tempat_pkl.upload_kesediaan');
    Route::view('/daftar-tempat', 'daftar_tempat');
});

Route::middleware(['auth'])->group(function () {

    // Halaman utama
    Route::get('/surat-izin-ortu', [SuratIzinOrtuController::class, 'index'])->name('surat-izin-ortu.index');

    // Untuk DataTables AJAX
    Route::get('/izin-ortu/data', [SuratIzinOrtuController::class, 'data'])->name('izin-ortu.data');

    // Simpan data (tambah)
    Route::post('/izin-ortu', [SuratIzinOrtuController::class, 'store'])->name('izin-ortu.store');

    // Edit data
    Route::get('/izin-ortu/{id}/edit', [SuratIzinOrtuController::class, 'edit'])->name('izin-ortu.edit');

    // Update data
    Route::put('/izin-ortu/{id}', [SuratIzinOrtuController::class, 'update'])->name('izin-ortu.update');

    // Hapus data
    Route::delete('/izin-ortu/{id}', [SuratIzinOrtuController::class, 'destroy'])->name('izin-ortu.destroy');

    Route::get('/izin-ortu/cetak/{id}', [SuratIzinOrtuController::class, 'cetak'])->name('izin-ortu.cetak');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/tempat-pkl', [TempatPklController::class, 'index'])->name('tempat-pkl.index');
    Route::get('/tempat-pkl/data', [TempatPklController::class, 'data'])->name('tempat-pkl.data');
    Route::post('/tempat-pkl', [TempatPklController::class, 'store'])->name('tempat-pkl.store');
    Route::get('/tempat-pkl/{id}/edit', [TempatPklController::class, 'edit']);
    Route::put('/tempat-pkl/{id}', [TempatPklController::class, 'update']);
    Route::put('/tempat-pkl/{id}/editPembimbing', [TempatPklController::class, 'editPembimbing'])->name('tempat-pkl.edit-pembimbing');
    Route::get('/tempat-pkl/cetak', [TempatPklController::class, 'index_cetak'])->name('tempat-pkl.index_cetak');
    Route::get('/tempat-pkl/set-tanggal', [TempatPklController::class, 'setTanggal'])->name('tempat-pkl.set-tanggal');
    Route::delete('/tempat-pkl/{id}', [TempatPklController::class, 'destroy'])->name('tempat-pkl.destroy');
    Route::get('/tempat-pkl/{id}/cetak', [TempatPklController::class, 'cetak'])->name('tempat-pkl.cetak');
    Route::get('/tempat-pkl/upload_kesediaan', [TempatPklController::class, 'upload_kesediaan'])->name('upload-kesediaan');
    Route::put('/tempat-pkl/update_kesediaan/{id}', [TempatPklController::class, 'update_kesediaan'])->name('update-kesediaan');
    Route::get('/tempat-pkl/cetak-amplop/{id}', [TempatPklController::class, 'cetakAmplop'])->name('tempat-pkl.cetak-amplop');
    Route::get('/tempat-pkl/cetak-amplop-word/{id}', [TempatPklController::class, 'cetakAmplopWord'])->name('tempat-pkl.cetak-amplop-word');
    Route::get('/tempat-pkl/export-excel', [TempatPklController::class, 'exportExcel'])->name('tempat-pkl.export-excel');

    Route::get('/sekolah', [SekolahController::class, 'index'])->name('sekolah.index');
    Route::get('/sekolah/data', [SekolahController::class, 'data'])->name('sekolah.data');
    Route::post('/sekolah', [SekolahController::class, 'store'])->name('sekolah.store');
    Route::get('/sekolah/{id}/edit', [SekolahController::class, 'edit'])->name('sekolah.edit');
    Route::put('/sekolah/{id}', [SekolahController::class, 'update'])->name('sekolah.update');
    Route::delete('/sekolah/{id}', [SekolahController::class, 'destroy'])->name('sekolah.destroy');
    Route::delete('/sekolah', [SekolahController::class, 'destroyMultiple'])->name('sekolah.destroyMultiple');
});

Route::middleware(['auth'])->group(function () {

    Route::get('/siswa', [SiswaController::class, 'index'])->name('siswa.index');
    Route::get('/siswa/data', [SiswaController::class, 'data'])->name('siswa.data');
    Route::post('/siswa/generate-accounts', [SiswaController::class, 'generateAccounts'])->name('siswa.generate-accounts');
    Route::post('/siswa', [SiswaController::class, 'store'])->name('siswa.store');
    Route::get('/siswa/{id}/edit', [SiswaController::class, 'edit'])->name('siswa.edit');
    Route::put('/siswa/{id}', [SiswaController::class, 'update'])->name('siswa.update');
    Route::delete('/siswa/{id}', [SiswaController::class, 'destroy'])->name('siswa.destroy');
    Route::delete('/siswa', [SiswaController::class, 'destroyMultiple'])->name('siswa.destroyMultiple');
    Route::post('/siswa/import', [SiswaController::class, 'import'])->name('siswa.import');
    Route::get('/siswa/export-pdf', [SiswaController::class, 'exportPdf'])->name('siswa.export-pdf');
    Route::get('/siswa/download/template', [SiswaController::class, 'downloadTemplate'])->name('siswa.downloadTemplate');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/perusahaan', [PerusahaanController::class, 'index'])->name('perusahaan.index');
    Route::get('/perusahaan/data', [PerusahaanController::class, 'data'])->name('perusahaan.data');
    Route::get('/perusahaan/rekap-wilayah', [PerusahaanController::class, 'rekapWilayah'])->name('perusahaan.rekap-wilayah');
    Route::get('/perusahaan/{id}/wilayah', [PerusahaanController::class, 'wilayahPerusahaan'])->name('perusahaan.wilayah');
    Route::get('/wilayah/provinsi', [PerusahaanController::class, 'provinsi'])->name('wilayah.provinsi');
    Route::get('/wilayah/kabupaten/{provinceId}', [PerusahaanController::class, 'kabupatenKota'])->name('wilayah.kabupaten');
    Route::get('/wilayah/kecamatan/{regencyId}', [PerusahaanController::class, 'kecamatan'])->name('wilayah.kecamatan');
    Route::get('/wilayah/desa/{districtId}', [PerusahaanController::class, 'desa'])->name('wilayah.desa');
    Route::post('/perusahaan', [PerusahaanController::class, 'store'])->name('perusahaan.store');
    Route::get('/perusahaan/{id}/edit', [PerusahaanController::class, 'edit'])->name('perusahaan.edit');
    Route::put('/perusahaan/{id}', [PerusahaanController::class, 'update'])->name('perusahaan.update');
    Route::delete('/perusahaan/{id}', [PerusahaanController::class, 'destroy'])->name('perusahaan.destroy');
    Route::delete('/perusahaan', [PerusahaanController::class, 'destroyMultiple'])->name('perusahaan.destroyMultiple');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/kelompok-bimbingan', [KelompokBimbinganController::class, 'index'])->name('kelompok-bimbingan.index');
    Route::get('/kelompok-bimbingan/penentuan', function () {
        return redirect()->route('kelompok-bimbingan.index');
    })->name('kelompok-bimbingan.penentuan');
    Route::post('/kelompok-bimbingan/generate-kelompok', [KelompokBimbinganController::class, 'generateKelompokKosong'])->name('kelompok-bimbingan.generate-kelompok');
    Route::post('/kelompok-bimbingan/otomatis', [KelompokBimbinganController::class, 'generateAutomatic'])->name('kelompok-bimbingan.generate-automatic');
    Route::post('/kelompok-bimbingan/manual', [KelompokBimbinganController::class, 'storeManual'])->name('kelompok-bimbingan.store-manual');
    Route::get('/kelompok-bimbingan/export-excel', [KelompokBimbinganController::class, 'exportExcel'])->name('kelompok-bimbingan.export-excel');
    Route::get('/kelompok-bimbingan/export-pdf', [KelompokBimbinganController::class, 'exportPdf'])->name('kelompok-bimbingan.export-pdf');
    Route::post('/kelompok-bimbingan/{id}/tambah-anggota', [KelompokBimbinganController::class, 'addAnggota'])->name('kelompok-bimbingan.add-anggota');
    Route::post('/kelompok-bimbingan/{id}/keluarkan-siswa', [KelompokBimbinganController::class, 'removeAnggota'])->name('kelompok-bimbingan.remove-anggota');
    Route::delete('/kelompok-bimbingan/reset', [KelompokBimbinganController::class, 'reset'])->name('kelompok-bimbingan.reset');
    Route::delete('/kelompok-bimbingan/{id}', [KelompokBimbinganController::class, 'destroy'])->name('kelompok-bimbingan.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/pembimbing', [PembimbingController::class, 'index'])->name('pembimbing.index');
    Route::get('/pembimbing/data', [PembimbingController::class, 'data'])->name('pembimbing.data');
    Route::post('/pembimbing/generate-accounts', [PembimbingController::class, 'generateAccounts'])->name('pembimbing.generate-accounts');
    Route::post('/pembimbing', [PembimbingController::class, 'store'])->name('pembimbing.store');
    Route::get('/pembimbing/{id}/edit', [PembimbingController::class, 'edit'])->name('pembimbing.edit');
    Route::put('/pembimbing/{id}', [PembimbingController::class, 'update'])->name('pembimbing.update');
    Route::delete('/pembimbing/{id}', [PembimbingController::class, 'destroy'])->name('pembimbing.destroy');
    Route::get('/pembimbing/export-excel', [PembimbingController::class, 'exportExcel'])->name('pembimbing.export-excel');
    Route::get('/pembimbing/export-pdf', [PembimbingController::class, 'exportPdf'])->name('pembimbing.export-pdf');
    Route::get('/pembimbing/download-template', [PembimbingController::class, 'downloadTemplate'])->name('pembimbing.download-template');
    Route::post('/pembimbing/import', [PembimbingController::class, 'import'])->name('pembimbing.import');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/pembimbing-perusahaan', [PembimbingPerusahaanController::class, 'index'])->name('pembimbing-perusahaan.index');
    Route::get('/pembimbing-perusahaan/data', [PembimbingPerusahaanController::class, 'data'])->name('pembimbing-perusahaan.data');
    Route::post('/pembimbing-perusahaan', [PembimbingPerusahaanController::class, 'store'])->name('pembimbing-perusahaan.store');
    Route::get('/pembimbing-perusahaan/{id}/edit', [PembimbingPerusahaanController::class, 'edit'])->name('pembimbing-perusahaan.edit');
    Route::put('/pembimbing-perusahaan/{id}', [PembimbingPerusahaanController::class, 'update'])->name('pembimbing-perusahaan.update');
    Route::delete('/pembimbing-perusahaan/{id}', [PembimbingPerusahaanController::class, 'destroy'])->name('pembimbing-perusahaan.destroy');
    Route::get('/pembimbing-perusahaan/cetak/{id}', [PembimbingPerusahaanController::class, 'cetak'])->name('pembimbing-perusahaan.cetak');
    Route::get('/pembimbing-perusahaan/export-excel', [PembimbingPerusahaanController::class, 'exportExcel'])->name('pembimbing-perusahaan.export-excel');
    Route::post('/pembimbing-perusahaan/import', [PembimbingPerusahaanController::class, 'import'])->name('pembimbing-perusahaan.import');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/pembekalan/materi', [MateriController::class, 'index'])->name('pembekalan.materi');
    Route::post('/pembekalan/materi', [MateriController::class, 'store'])->middleware('can:panitia')->name('pembekalan.materi.store');
    Route::put('/pembekalan/materi/{materi}', [MateriController::class, 'update'])->middleware('can:panitia')->name('pembekalan.materi.update');
    Route::delete('/pembekalan/materi/{materi}', [MateriController::class, 'destroy'])->middleware('can:panitia')->name('pembekalan.materi.destroy');
    Route::get('/pembekalan/tugas', [TugasPembekalanController::class, 'pageIndex'])->name('pembekalan.tugas');
    Route::post('/pembekalan/tugas', [TugasPembekalanController::class, 'pageStore'])->middleware('can:panitia')->name('pembekalan.tugas.store');
    Route::put('/pembekalan/tugas/{tugasPembekalan}', [TugasPembekalanController::class, 'pageUpdate'])->middleware('can:panitia')->name('pembekalan.tugas.update');
    Route::delete('/pembekalan/tugas/{tugasPembekalan}', [TugasPembekalanController::class, 'pageDestroy'])->middleware('can:panitia')->name('pembekalan.tugas.destroy');
    Route::get('/pembekalan/absensi', [AbsensiPembekalanController::class, 'pageIndex'])->name('pembekalan.absensi');
    Route::get('/pembekalan/absensi/input', [AbsensiPembekalanController::class, 'pageInput'])->name('pembekalan.absensi.input');
    Route::get('/pembekalan/absensi/input/students', [AbsensiPembekalanController::class, 'pageInputStudents'])->name('pembekalan.absensi.input.students');
    Route::get('/pembekalan/absensi/riwayat', [AbsensiPembekalanController::class, 'pageRiwayat'])->name('pembekalan.absensi.riwayat');
    Route::get('/pembekalan/absensi/formulir', [AbsensiPembekalanController::class, 'pageFormulir'])->name('pembekalan.absensi.formulir');
    Route::get('/pembekalan/absensi/formulir/pdf', [AbsensiPembekalanController::class, 'pageFormulirPdf'])->name('pembekalan.absensi.formulir.pdf');
    Route::get('/pembekalan/pembinaan', [PembinaanPembekalanController::class, 'index'])->name('pembekalan.pembinaan');
    Route::get('/pembekalan/pembinaan/export-excel', [PembinaanPembekalanController::class, 'exportExcel'])->name('pembekalan.pembinaan.export-excel');
    Route::get('/pembekalan/pembinaan/export-pdf', [PembinaanPembekalanController::class, 'exportPdf'])->name('pembekalan.pembinaan.export-pdf');
    Route::post('/pembekalan/pembinaan', [PembinaanPembekalanController::class, 'store'])->name('pembekalan.pembinaan.store');
    Route::put('/pembekalan/pembinaan/{pembinaanPembekalan}', [PembinaanPembekalanController::class, 'update'])->name('pembekalan.pembinaan.update');
    Route::delete('/pembekalan/pembinaan/{pembinaanPembekalan}', [PembinaanPembekalanController::class, 'destroy'])->name('pembekalan.pembinaan.destroy');
    Route::get('/pembekalan/pembinaan/{pembinaanPembekalan}/print', [PembinaanPembekalanController::class, 'print'])->name('pembekalan.pembinaan.print');
    Route::get('/pembekalan/pembinaan/{pembinaanPembekalan}/pdf', [PembinaanPembekalanController::class, 'pdf'])->name('pembekalan.pembinaan.pdf');
    Route::post('/pembekalan/absensi/bulk', [AbsensiPembekalanController::class, 'pageBulkStore'])->name('pembekalan.absensi.bulk-store');
    Route::post('/pembekalan/absensi', [AbsensiPembekalanController::class, 'pageStore'])->name('pembekalan.absensi.store');
    Route::put('/pembekalan/absensi/{absensiPembekalan}', [AbsensiPembekalanController::class, 'pageUpdate'])->name('pembekalan.absensi.update');
    Route::delete('/pembekalan/absensi/{absensiPembekalan}', [AbsensiPembekalanController::class, 'pageDestroy'])->name('pembekalan.absensi.destroy');
    Route::get('/pembekalan/sikap', [NilaiSikapPembekalanController::class, 'pageIndex'])->name('pembekalan.sikap');
    Route::get('/pembekalan/sikap/input', [NilaiSikapPembekalanController::class, 'pageInput'])->name('pembekalan.sikap.input');
    Route::get('/pembekalan/sikap/input/students', [NilaiSikapPembekalanController::class, 'pageInputStudents'])->name('pembekalan.sikap.input.students');
    Route::get('/pembekalan/sikap/riwayat', [NilaiSikapPembekalanController::class, 'pageRiwayat'])->name('pembekalan.sikap.riwayat');
    Route::post('/pembekalan/sikap/bulk', [NilaiSikapPembekalanController::class, 'pageBulkStore'])->name('pembekalan.sikap.bulk-store');
    Route::post('/pembekalan/sikap', [NilaiSikapPembekalanController::class, 'pageStore'])->name('pembekalan.sikap.store');
    Route::put('/pembekalan/sikap/{nilaiSikapPembekalan}', [NilaiSikapPembekalanController::class, 'pageUpdate'])->name('pembekalan.sikap.update');
    Route::delete('/pembekalan/sikap/{nilaiSikapPembekalan}', [NilaiSikapPembekalanController::class, 'pageDestroy'])->name('pembekalan.sikap.destroy');
    Route::get('/pembekalan/jawaban-siswa', [JawabanTugasSiswaController::class, 'pageIndex'])->name('pembekalan.jawaban-siswa');
    Route::post('/pembekalan/jawaban-siswa/{jawabanTugasSiswa}/nilai', [NilaiTugasPembekalanController::class, 'pageStore'])->middleware('can:pembimbing')->name('pembekalan.jawaban-siswa.nilai.store');
    Route::get('/pembekalan', [PembekalanController::class, 'index'])->name('pembekalan.index');
    Route::get('/pembekalan/laporan', [PembekalanController::class, 'laporan'])->name('pembekalan.laporan');
    Route::get('/pembekalan/laporan/export-excel', [PembekalanController::class, 'exportExcel'])->name('pembekalan.laporan.export-excel');
    Route::get('/pembekalan/laporan/export-pdf', [PembekalanController::class, 'exportPdf'])->name('pembekalan.laporan.export-pdf');

    // Domain terpisah pembekalan (versi normalized)
    Route::prefix('pembekalan-domain')->group(function () {
        Route::apiResource('materi', MateriController::class);
        Route::apiResource('tugas', TugasPembekalanController::class);
        Route::apiResource('absensi', AbsensiPembekalanController::class);
        Route::apiResource('jawaban-siswa', JawabanTugasSiswaController::class);
        Route::apiResource('nilai-tugas', NilaiTugasPembekalanController::class);
        Route::apiResource('nilai-sikap', NilaiSikapPembekalanController::class);
    });

    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    Route::get('/monitoring/data', [MonitoringController::class, 'data'])->name('monitoring.data');
    Route::get('/monitoring/lihatdata/{id}', [MonitoringController::class, 'lihatdata'])->name('monitoring.lihatdata');
    Route::get('/monitoring/export-excel', [MonitoringController::class, 'exportExcel'])->name('monitoring.exportExcel');

    Route::get('/monitoring/set-tanggal', [MonitoringController::class, 'setTanggal'])->name('monitoring.set-tanggal');
    Route::post('/monitoring', [MonitoringController::class, 'store'])->name('monitoring.store');
    Route::get('/monitoring/{id}/edit', [MonitoringController::class, 'edit'])->name('monitoring.edit');
    Route::put('/monitoring/{id}', [MonitoringController::class, 'update'])->name('monitoring.update');
    Route::delete('/monitoring/{id}', [MonitoringController::class, 'destroy'])->name('monitoring.destroy');
    Route::get('/monitoring/data', [MonitoringController::class, 'data'])->name('monitoring.data');
    Route::get('/monitoring/cetak', [MonitoringController::class, 'index_cetak'])->name('monitoring.index_cetak');
    Route::get('/monitoring/cetak-monitoring/{id}', [MonitoringController::class, 'cetakMonitoring'])->name('monitoring.cetak-monitoring');
    Route::get('/monitoring/cetak-sppd/{id}', [MonitoringController::class, 'cetakSppd'])->name('monitoring.cetak-sppd');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/jurusan', [JurusanController::class, 'index'])->name('jurusan.index');
    Route::get('/jurusan/data', [JurusanController::class, 'data'])->name('jurusan.data');
    Route::post('/jurusan', [JurusanController::class, 'store'])->name('jurusan.store');
    Route::get('/jurusan/{id}/edit', [JurusanController::class, 'edit'])->name('jurusan.edit');
    Route::put('/jurusan/{id}', [JurusanController::class, 'update'])->name('jurusan.update');
    Route::delete('/jurusan/{id}', [JurusanController::class, 'destroy'])->name('jurusan.destroy');
    Route::post('/jurusan/import', [JurusanController::class, 'import'])->name('jurusan.import');
    Route::get('/jurusan/download/template', [JurusanController::class, 'downloadTemplate'])->name('jurusan.downloadTemplate');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/kelas', [KelasController::class, 'index'])->name('kelas.index');
    Route::get('/kelas/data', [KelasController::class, 'data'])->name('kelas.data');
    Route::post('/kelas', [KelasController::class, 'store'])->name('kelas.store');
    Route::post('/kelas/switch-xi-xii', [KelasController::class, 'switchXiToXii'])->name('kelas.switch-xi-xii');
    Route::post('/kelas/switch-xii-xi', [KelasController::class, 'switchXiiToXi'])->name('kelas.switch-xii-xi');
    Route::get('/kelas/{id}/edit', [KelasController::class, 'edit'])->name('kelas.edit');
    Route::put('/kelas/{id}', [KelasController::class, 'update'])->name('kelas.update');
    Route::delete('/kelas/{id}', [KelasController::class, 'destroy'])->name('kelas.destroy');
    Route::post('/kelas/import', [KelasController::class, 'import'])->name('kelas.import');
    Route::get('/kelas/download/template', [KelasController::class, 'downloadTemplate'])->name('kelas.downloadTemplate');
});

Route::middleware(['auth', 'can:panitia'])->group(function () {
    Route::get('/manajemen-user', [UserManagementController::class, 'index'])->name('user-management.index');
    Route::get('/manajemen-user/data', [UserManagementController::class, 'data'])->name('user-management.data');
    Route::post('/manajemen-user', [UserManagementController::class, 'store'])->name('user-management.store');
    Route::get('/manajemen-user/{user}/edit', [UserManagementController::class, 'edit'])->name('user-management.edit');
    Route::put('/manajemen-user/{user}', [UserManagementController::class, 'update'])->name('user-management.update');
    Route::delete('/manajemen-user/{user}', [UserManagementController::class, 'destroy'])->name('user-management.destroy');

    Route::get('/manajemen-role', [RoleManagementController::class, 'index'])->name('role-management.index');
    Route::get('/manajemen-role/data', [RoleManagementController::class, 'data'])->name('role-management.data');
    Route::post('/manajemen-role', [RoleManagementController::class, 'store'])->name('role-management.store');
    Route::get('/manajemen-role/{role}/edit', [RoleManagementController::class, 'edit'])->name('role-management.edit');
    Route::put('/manajemen-role/{role}', [RoleManagementController::class, 'update'])->name('role-management.update');
    Route::delete('/manajemen-role/{role}', [RoleManagementController::class, 'destroy'])->name('role-management.destroy');
});
