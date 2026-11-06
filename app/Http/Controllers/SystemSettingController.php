<?php

namespace App\Http\Controllers;

use App\Models\AbsensiSelfieSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SystemSettingController extends Controller
{
    public function index()
    {
        $absensiSetting = AbsensiSelfieSetting::current();

        $info = [
            'cache'    => $this->dirInfo(storage_path('framework/cache/data')),
            'sessions' => $this->dirInfo(storage_path('framework/sessions')),
            'views'    => $this->dirInfo(storage_path('framework/views')),
            'config'   => [
                'cached' => file_exists(base_path('bootstrap/cache/config.php')),
                'size'   => file_exists(base_path('bootstrap/cache/config.php'))
                    ? filesize(base_path('bootstrap/cache/config.php'))
                    : 0,
                'files'  => file_exists(base_path('bootstrap/cache/config.php')) ? 1 : 0,
            ],
            'routes'   => [
                'cached' => file_exists(base_path('bootstrap/cache/routes-v7.php')),
                'size'   => file_exists(base_path('bootstrap/cache/routes-v7.php'))
                    ? filesize(base_path('bootstrap/cache/routes-v7.php'))
                    : 0,
                'files'  => file_exists(base_path('bootstrap/cache/routes-v7.php')) ? 1 : 0,
            ],
        ];

        return view('setting.system', compact('info', 'absensiSetting'));
    }

    public function updateAbsensiWaktu(Request $request)
    {
        $data = $request->validate([
            'jam_datang_mulai' => ['required', 'date_format:H:i'],
            'jam_datang_tepat_waktu' => ['required', 'date_format:H:i', 'after_or_equal:jam_datang_mulai'],
            'jam_datang_selesai' => ['required', 'date_format:H:i', 'after:jam_datang_tepat_waktu'],
            'jam_pulang_mulai' => ['required', 'date_format:H:i', 'after:jam_datang_selesai'],
            'jam_pulang_selesai' => ['required', 'date_format:H:i', 'after:jam_pulang_mulai'],
        ], [
            'jam_datang_tepat_waktu.after_or_equal' => 'Batas tepat waktu tidak boleh sebelum jam mulai absen datang.',
            'jam_datang_selesai.after' => 'Jam tutup absen datang harus setelah batas tepat waktu.',
            'jam_pulang_mulai.after' => 'Jam buka absen pulang harus setelah jam tutup absen datang.',
            'jam_pulang_selesai.after' => 'Jam tutup absen pulang harus setelah jam buka absen pulang.',
        ]);

        $setting = AbsensiSelfieSetting::current();
        $setting->update([
            'jam_datang_mulai' => $data['jam_datang_mulai'] . ':00',
            'jam_datang_tepat_waktu' => $data['jam_datang_tepat_waktu'] . ':00',
            'jam_datang_selesai' => $data['jam_datang_selesai'] . ':00',
            'jam_pulang_mulai' => $data['jam_pulang_mulai'] . ':00',
            'jam_pulang_selesai' => $data['jam_pulang_selesai'] . ':00',
            'updated_by' => auth()->id(),
        ]);

        return back()->with('success', 'Pengaturan waktu absen datang & pulang berhasil disimpan.');
    }

    public function clearCache()
    {
        Artisan::call('cache:clear');
        return back()->with('success', 'Cache aplikasi berhasil dibersihkan.');
    }

    public function clearSessions()
    {
        $path = storage_path('framework/sessions');
        $count = 0;
        foreach (glob($path . '/*') as $file) {
            if (is_file($file)) {
                unlink($file);
                $count++;
            }
        }
        return back()->with('success', "{$count} file sesi berhasil dibersihkan.");
    }

    public function clearViews()
    {
        Artisan::call('view:clear');
        return back()->with('success', 'Cache view berhasil dibersihkan.');
    }

    public function clearConfig()
    {
        Artisan::call('config:clear');
        return back()->with('success', 'Cache konfigurasi berhasil dibersihkan.');
    }

    public function clearRoutes()
    {
        Artisan::call('route:clear');
        return back()->with('success', 'Cache route berhasil dibersihkan.');
    }

    public function clearAll()
    {
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');

        $path = storage_path('framework/sessions');
        foreach (glob($path . '/*') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        return back()->with('success', 'Semua cache & sesi berhasil dibersihkan.');
    }

    /** Returns file count and total byte size of a directory. */
    private function dirInfo(string $path): array
    {
        if (!is_dir($path)) {
            return ['files' => 0, 'size' => 0];
        }

        $files = 0;
        $size  = 0;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files++;
                $size += $file->getSize();
            }
        }

        return compact('files', 'size');
    }
}
