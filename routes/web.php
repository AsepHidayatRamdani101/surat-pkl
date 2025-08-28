<?php

use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\PembimbingController;
use App\Http\Controllers\PembimbingPerusahaanController;
use App\Http\Controllers\PerusahaanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\SuratIzinOrtuController;
use App\Http\Controllers\TempatPklController;
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
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::view('/dashboard', 'dashboard');
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
    Route::delete('/tempat-pkl/{id}', [TempatPklController::class, 'destroy'])->name('tempat-pkl.destroy');
    Route::get('/tempat-pkl/{id}/cetak', [TempatPklController::class, 'cetak'])->name('tempat-pkl.cetak');
    Route::get('/tempat-pkl/upload_kesediaan', [TempatPklController::class, 'upload_kesediaan'])->name('upload-kesediaan');
    Route::put('/tempat-pkl/update_kesediaan/{id}', [TempatPklController::class, 'update_kesediaan'])->name('update-kesediaan');
    Route::get('/tempat-pkl/cetak-amplop/{id}', [TempatPklController::class, 'cetakAmplop'])->name('tempat-pkl.cetak-amplop');
    Route::get('/tempat-pkl/cetak-amplop-word/{id}', [TempatPklController::class, 'cetakAmplopWord'])->name('tempat-pkl.cetak-amplop-word');
    Route::get('/tempat-pkl/export-excel', [TempatPklController::class, 'exportExcel'])->name('tempat-pkl.export-excel');
});

Route::middleware(['auth'])->group(function () {

    Route::get('/siswa', [SiswaController::class, 'index'])->name('siswa.index');
    Route::get('/siswa/data', [SiswaController::class, 'data'])->name('siswa.data');
    Route::post('/siswa', [SiswaController::class, 'store'])->name('siswa.store');
    Route::get('/siswa/{id}/edit', [SiswaController::class, 'edit'])->name('siswa.edit');
    Route::put('/siswa/{id}', [SiswaController::class, 'update'])->name('siswa.update');
    Route::delete('/siswa/{id}', [SiswaController::class, 'destroy'])->name('siswa.destroy');
    Route::post('/siswa/import', [SiswaController::class, 'import'])->name('siswa.import');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/perusahaan', [PerusahaanController::class, 'index'])->name('perusahaan.index');
    Route::get('/perusahaan/data', [PerusahaanController::class, 'data'])->name('perusahaan.data');
    Route::post('/perusahaan', [PerusahaanController::class, 'store'])->name('perusahaan.store');
    Route::get('/perusahaan/{id}/edit', [PerusahaanController::class, 'edit'])->name('perusahaan.edit');
    Route::put('/perusahaan/{id}', [PerusahaanController::class, 'update'])->name('perusahaan.update');
    Route::delete('/perusahaan/{id}', [PerusahaanController::class, 'destroy'])->name('perusahaan.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/pembimbing', [PembimbingController::class, 'index'])->name('pembimbing.index');
    Route::get('/pembimbing/data', [PembimbingController::class, 'data'])->name('pembimbing.data');
    Route::post('/pembimbing', [PembimbingController::class, 'store'])->name('pembimbing.store');
    Route::get('/pembimbing/{id}/edit', [PembimbingController::class, 'edit'])->name('pembimbing.edit');
    Route::put('/pembimbing/{id}', [PembimbingController::class, 'update'])->name('pembimbing.update');
    Route::delete('/pembimbing/{id}', [PembimbingController::class, 'destroy'])->name('pembimbing.destroy');
    Route::get('/pembimbing/export-excel', [PembimbingController::class, 'exportExcel'])->name('pembimbing.export-excel');
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
    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    Route::get('/monitoring/data', [MonitoringController::class, 'data'])->name('monitoring.data');
    Route::get('/monitoring/lihatdata/{id}', [MonitoringController::class, 'lihatdata'])->name('monitoring.lihatdata');

    Route::post('/monitoring', [MonitoringController::class, 'store'])->name('monitoring.store');
    Route::get('/monitoring/{id}/edit', [MonitoringController::class, 'edit'])->name('monitoring.edit');
    Route::put('/monitoring/{id}', [MonitoringController::class, 'update'])->name('monitoring.update');
    Route::delete('/monitoring/{id}', [MonitoringController::class, 'destroy'])->name('monitoring.destroy');
    Route::get('/monitoring/data', [MonitoringController::class, 'data'])->name('monitoring.data');
    Route::get('/monitoring/cetak', [MonitoringController::class, 'index_cetak'])->name('monitoring.index_cetak');
    Route::get('/monitoring/cetak-monitoring/{id}', [MonitoringController::class, 'cetakMonitoring'])->name('monitoring.cetak-monitoring');
    Route::get('/monitoring/cetak-sppd/{id}', [MonitoringController::class, 'cetakSppd'])->name('monitoring.cetak-sppd');
});
