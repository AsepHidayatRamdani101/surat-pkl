<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FormatsExcelSheets;
use App\Models\Perusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;

class PerusahaanController extends Controller
{
    use FormatsExcelSheets;

    
    public function index()
    {
        $perusahaan = Perusahaan::all();
        return view('perusahaan.index', compact('perusahaan'));
    }

    public function data(Request $request)
    {
        $perusahaan = Perusahaan::all();

        return DataTables::of($perusahaan)
            ->addColumn('checkbox', function ($perusahaan) {
                return '<input type="checkbox" class="form-check checkbox-perusahaan" value="' . $perusahaan->id . '">';
            })
            ->addColumn('aksi', function ($perusahaan) {
                return '
                    <a href="javascript:void(0)" class="btn btn-sm btn-warning btnEdit" 
                    data-id="' . $perusahaan->id . '"
                    data-nama="' . $perusahaan->nama_perusahaan . '"
                    data-alamat="' . $perusahaan->alamat . '"
                    data-provinsi-id="' . e($perusahaan->provinsi_id) . '"
                    data-kabupaten-kota-id="' . e($perusahaan->kabupaten_kota_id) . '"
                    data-kecamatan-id="' . e($perusahaan->kecamatan_id) . '"
                    data-desa-id="' . e($perusahaan->desa_id) . '"
                    data-nama-pemilik="' . $perusahaan->nama_pemilik_perusahaan . '"
                    data-telepon-pemilik="' . $perusahaan->telepon_pemilik_perusahaan . '"
                    data-latitude="' . e($perusahaan->latitude) . '"
                    data-longitude="' . e($perusahaan->longitude) . '"
                    data-maps-url="' . e($perusahaan->maps_url) . '"
                    >Edit</a>
                    <a href="javascript:void(0)" class="btn btn-sm btn-danger btnHapus" data-id="' . $perusahaan->id . '">Hapus</a>
                ';
            })
            ->addColumn('alamat', function ($perusahaan) {
                return $this->formatAlamatLengkap($perusahaan);
            })
            ->addColumn('maps', function ($perusahaan) {
                if ($perusahaan->latitude !== null && $perusahaan->longitude !== null) {
                    $url = 'https://www.google.com/maps?q=' . $perusahaan->latitude . ',' . $perusahaan->longitude;
                    return '<a href="' . e($url) . '" target="_blank" rel="noopener" class="btn btn-xs btn-outline-success"><i class="fas fa-map-marker-alt mr-1"></i>Lihat Maps</a>';
                }
                if ($perusahaan->maps_url) {
                    return '<a href="' . e($perusahaan->maps_url) . '" target="_blank" rel="noopener" class="btn btn-xs btn-outline-success"><i class="fas fa-map-marker-alt mr-1"></i>Lihat Maps</a>';
                }
                return '<span class="text-muted">Belum diatur</span>';
            })
            ->addIndexColumn()
            ->rawColumns(['checkbox', 'aksi', 'alamat', 'maps'])
            ->make(true);
    }

    public function exportExcel()
    {
        $perusahaanList = Perusahaan::orderBy('nama_perusahaan')->get();

        if ($perusahaanList->isEmpty()) {
            return response()->json(['message' => 'Tidak ada data untuk diekspor'], 404);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Perusahaan');

        $headers = [
            'No',
            'Nama Perusahaan',
            'Nama Pemilik (PIC)',
            'No. Telp Pemilik',
            'Alamat',
            'Desa',
            'Kecamatan',
            'Kabupaten/Kota',
            'Provinsi',
            'Link Maps',
        ];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        $row = 2;
        foreach ($perusahaanList as $index => $perusahaan) {
            $provinsi = $this->findWilayahName(
                'wilayah:provinces',
                'https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json',
                $perusahaan->provinsi_id
            );
            $kabupaten = $this->findWilayahName(
                "wilayah:regencies:{$perusahaan->provinsi_id}",
                "https://www.emsifa.com/api-wilayah-indonesia/api/regencies/{$perusahaan->provinsi_id}.json",
                $perusahaan->kabupaten_kota_id
            );
            $kecamatan = $this->findWilayahName(
                "wilayah:districts:{$perusahaan->kabupaten_kota_id}",
                "https://www.emsifa.com/api-wilayah-indonesia/api/districts/{$perusahaan->kabupaten_kota_id}.json",
                $perusahaan->kecamatan_id
            );
            $desa = $this->findWilayahName(
                "wilayah:villages:{$perusahaan->kecamatan_id}",
                "https://www.emsifa.com/api-wilayah-indonesia/api/villages/{$perusahaan->kecamatan_id}.json",
                $perusahaan->desa_id
            );

            $mapsUrl = null;
            if ($perusahaan->latitude !== null && $perusahaan->longitude !== null) {
                $mapsUrl = 'https://www.google.com/maps?q=' . $perusahaan->latitude . ',' . $perusahaan->longitude;
            } elseif ($perusahaan->maps_url) {
                $mapsUrl = $perusahaan->maps_url;
            }

            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $perusahaan->nama_perusahaan ?? '-');
            $sheet->setCellValue('C' . $row, $perusahaan->nama_pemilik_perusahaan ?? '-');
            $sheet->setCellValueExplicit('D' . $row, $perusahaan->telepon_pemilik_perusahaan ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('E' . $row, $perusahaan->alamat ?? '-');
            $sheet->setCellValue('F' . $row, $desa ?? '-');
            $sheet->setCellValue('G' . $row, $kecamatan ?? '-');
            $sheet->setCellValue('H' . $row, $kabupaten ?? '-');
            $sheet->setCellValue('I' . $row, $provinsi ?? '-');
            $sheet->setCellValue('J' . $row, $mapsUrl ?? '-');
            if ($mapsUrl) {
                $sheet->getCell('J' . $row)->getHyperlink()->setUrl($mapsUrl);
            }
            $row++;
        }

        $this->applyExcelTableFormatting($sheet, 'J', $row - 1);

        $fileName = 'data_perusahaan_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        $filePath = storage_path('app/public/' . $fileName);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_perusahaan' => 'required',
            'alamat' => 'required',
            'provinsi_id' => 'required',
            'kabupaten_kota_id' => 'required',
            'kecamatan_id' => 'required',
            'desa_id' => 'required',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180|required_with:latitude',
            'maps_url' => 'nullable|string|max:500',
        ]);

        $koordinat = $this->resolveKoordinat($request);

        Perusahaan::create([
            'nama_perusahaan' => $request->nama_perusahaan,
            'nama_pemilik_perusahaan' => $request->nama_pemilik_perusahaan,
            'telepon_pemilik_perusahaan' => $request->telepon_pemilik_perusahaan,
            'alamat' => $request->alamat,
            'provinsi_id' => $request->provinsi_id,
            'kabupaten_kota_id' => $request->kabupaten_kota_id,
            'kecamatan_id' => $request->kecamatan_id,
            'desa_id' => $request->desa_id,
            'latitude' => $koordinat['latitude'],
            'longitude' => $koordinat['longitude'],
            'maps_url' => $request->maps_url,
        ]);

        $this->forgetRekapWilayahCache();

        return response()->json(['success' => true]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_perusahaan' => 'required',
            'nama_pemilik_perusahaan' => 'required',
            'telepon_pemilik_perusahaan' => 'required',
            'alamat' => 'required',
            'provinsi_id' => 'required',
            'kabupaten_kota_id' => 'required',
            'kecamatan_id' => 'required',
            'desa_id' => 'required',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180|required_with:latitude',
            'maps_url' => 'nullable|string|max:500',
        ]);

        $koordinat = $this->resolveKoordinat($request);

        $perusahaan = Perusahaan::find($id);
        $perusahaan->update([
            'nama_perusahaan' => $request->nama_perusahaan,
            'nama_pemilik_perusahaan' => $request->nama_pemilik_perusahaan,
            'telepon_pemilik_perusahaan' => $request->telepon_pemilik_perusahaan,
            'alamat' => $request->alamat,
            'provinsi_id' => $request->provinsi_id,
            'kabupaten_kota_id' => $request->kabupaten_kota_id,
            'kecamatan_id' => $request->kecamatan_id,
            'desa_id' => $request->desa_id,
            'latitude' => $koordinat['latitude'],
            'longitude' => $koordinat['longitude'],
            'maps_url' => $request->maps_url,
        ]);

        $this->forgetRekapWilayahCache();

        return response()->json(['success' => true]);
    }

    public function provinsi()
    {
        return response()->json($this->cachedWilayahJson(
            'wilayah:provinces',
            'https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json'
        ));
    }

    public function kabupatenKota($provinceId)
    {
        return response()->json($this->cachedWilayahJson(
            "wilayah:regencies:{$provinceId}",
            "https://www.emsifa.com/api-wilayah-indonesia/api/regencies/{$provinceId}.json"
        ));
    }

    public function kecamatan($regencyId)
    {
        return response()->json($this->cachedWilayahJson(
            "wilayah:districts:{$regencyId}",
            "https://www.emsifa.com/api-wilayah-indonesia/api/districts/{$regencyId}.json"
        ));
    }

    public function desa($districtId)
    {
        return response()->json($this->cachedWilayahJson(
            "wilayah:villages:{$districtId}",
            "https://www.emsifa.com/api-wilayah-indonesia/api/villages/{$districtId}.json"
        ));
    }

    public function wilayahPerusahaan($id)
    {
        $perusahaan = Perusahaan::findOrFail($id);

        $provinsi = $this->cachedWilayahJson(
            'wilayah:provinces',
            'https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json'
        );

        $kabupaten = $perusahaan->provinsi_id
            ? $this->cachedWilayahJson(
                "wilayah:regencies:{$perusahaan->provinsi_id}",
                "https://www.emsifa.com/api-wilayah-indonesia/api/regencies/{$perusahaan->provinsi_id}.json"
            )
            : [];

        $kecamatan = $perusahaan->kabupaten_kota_id
            ? $this->cachedWilayahJson(
                "wilayah:districts:{$perusahaan->kabupaten_kota_id}",
                "https://www.emsifa.com/api-wilayah-indonesia/api/districts/{$perusahaan->kabupaten_kota_id}.json"
            )
            : [];

        $desa = $perusahaan->kecamatan_id
            ? $this->cachedWilayahJson(
                "wilayah:villages:{$perusahaan->kecamatan_id}",
                "https://www.emsifa.com/api-wilayah-indonesia/api/villages/{$perusahaan->kecamatan_id}.json"
            )
            : [];

        return response()->json([
            'provinsi' => $provinsi,
            'kabupaten' => $kabupaten,
            'kecamatan' => $kecamatan,
            'desa' => $desa,
            'selected' => [
                'provinsi_id' => $perusahaan->provinsi_id,
                'kabupaten_kota_id' => $perusahaan->kabupaten_kota_id,
                'kecamatan_id' => $perusahaan->kecamatan_id,
                'desa_id' => $perusahaan->desa_id,
            ],
        ]);
    }

    public function rekapWilayah()
    {
        $rows = Cache::remember('perusahaan:rekap-wilayah:v5', now()->addHours(12), function () {
            $perusahaanList = Perusahaan::select('id', 'nama_perusahaan', 'provinsi_id', 'kabupaten_kota_id', 'kecamatan_id')->get();
            $grouped = [];

            foreach ($perusahaanList as $perusahaan) {
                $kecamatanName = $this->resolveKecamatanName($perusahaan->kecamatan_id);

                $normalized = $kecamatanName ? Str::upper(trim($kecamatanName)) : null;
                $isSelaawiGroup = $normalized && in_array($normalized, ['SELAAWI', 'BALUBUR LIMBANGAN', 'BLUBUR LIMBANGAN', 'CIBUGEL','CIBIUK'], true);

                if ($isSelaawiGroup) {
                    $groupKey = 'wilayah-selaawi';
                    $label = 'Wilayah Selaawi';
                    $kecamatanDisplay = 'Kec. Selaawi, Kec. Blubur Limbangan, Kec. Cibugel, Kec. Cibiuk';
                } else {
                    $kabupatenKotaName = $this->resolveKabupatenKotaName($perusahaan->provinsi_id, $perusahaan->kabupaten_kota_id) ?? 'Kabupaten/Kota Tidak Diketahui';
                    $groupKey = 'kab-' . ($perusahaan->kabupaten_kota_id ?: Str::slug($kabupatenKotaName));
                    $label = 'Kabupaten/Kota ' . $kabupatenKotaName;
                    $kecamatanDisplay = 'Kabupaten/Kota ' . $kabupatenKotaName;
                }

                if (! isset($grouped[$groupKey])) {
                    $grouped[$groupKey] = [
                        'wilayah' => $label,
                        'kecamatan' => $kecamatanDisplay,
                        'jumlah' => 0,
                    ];
                }

                $grouped[$groupKey]['jumlah']++;
            }

            return collect($grouped)->values()->sortBy('wilayah')->values();
        });

        return view('perusahaan.rekap_wilayah', compact('rows'));
    }

    private function cachedWilayahJson(string $cacheKey, string $url): array
    {
        return Cache::remember($cacheKey, now()->addDay(), function () use ($url) {
            $response = Http::timeout(15)->retry(2, 150)->get($url);

            if (! $response->successful()) {
                return [];
            }

            return $response->json() ?? [];
        });
    }

    private function formatAlamatLengkap($perusahaan): string
    {
        $provinsi = $this->findWilayahName(
            'wilayah:provinces',
            'https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json',
            $perusahaan->provinsi_id
        );

        $kabupaten = $this->findWilayahName(
            "wilayah:regencies:{$perusahaan->provinsi_id}",
            "https://www.emsifa.com/api-wilayah-indonesia/api/regencies/{$perusahaan->provinsi_id}.json",
            $perusahaan->kabupaten_kota_id
        );

        $kecamatan = $this->findWilayahName(
            "wilayah:districts:{$perusahaan->kabupaten_kota_id}",
            "https://www.emsifa.com/api-wilayah-indonesia/api/districts/{$perusahaan->kabupaten_kota_id}.json",
            $perusahaan->kecamatan_id
        );

        $desa = $this->findWilayahName(
            "wilayah:villages:{$perusahaan->kecamatan_id}",
            "https://www.emsifa.com/api-wilayah-indonesia/api/villages/{$perusahaan->kecamatan_id}.json",
            $perusahaan->desa_id
        );

        $wilayah = array_filter([
            $desa,
            $kecamatan,
            $kabupaten,
            $provinsi,
        ]);

        $alamat = trim((string) $perusahaan->alamat);

        if (empty($wilayah)) {
            return e($alamat);
        }

        $alamatLengkap = $alamat . '<br><small class="text-muted">' . e(implode(', ', $wilayah)) . '</small>';

        return $alamatLengkap;
    }

    private function findWilayahName(string $cacheKey, string $url, $targetId): ?string
    {
        if (empty($targetId)) {
            return null;
        }

        $data = $this->cachedWilayahJson($cacheKey, $url);

        foreach ($data as $item) {
            if ((string) ($item['id'] ?? '') === (string) $targetId) {
                return $item['name'] ?? null;
            }
        }

        return null;
    }

    private function resolveKecamatanName($kecamatanId): ?string
    {
        if (empty($kecamatanId)) {
            return null;
        }

        $regencyId = substr((string) $kecamatanId, 0, 4);
        $districts = $this->cachedWilayahJson(
            "wilayah:districts:{$regencyId}",
            "https://www.emsifa.com/api-wilayah-indonesia/api/districts/{$regencyId}.json"
        );

        foreach ($districts as $district) {
            if ((string) ($district['id'] ?? '') === (string) $kecamatanId) {
                return $district['name'] ?? null;
            }
        }

        return null;
    }

    private function resolveKabupatenKotaName($provinceId, $kabupatenKotaId): ?string
    {
        if (empty($provinceId) || empty($kabupatenKotaId)) {
            return null;
        }

        $regencies = $this->cachedWilayahJson(
            "wilayah:regencies:{$provinceId}",
            "https://www.emsifa.com/api-wilayah-indonesia/api/regencies/{$provinceId}.json"
        );

        foreach ($regencies as $regency) {
            if ((string) ($regency['id'] ?? '') === (string) $kabupatenKotaId) {
                return $regency['name'] ?? null;
            }
        }

        return null;
    }

    public function destroy(Request $request, $id)
    {
        $perusahaan = Perusahaan::find($id);
        $perusahaan->delete();

        $this->forgetRekapWilayahCache();

        return response()->json(['success' => true]);
    }

    public function destroyMultiple(Request $request)
    {
        try {
            $ids = $request->ids;
            
            if (!is_array($ids) || empty($ids)) {
                return response()->json(['message' => 'Pilih minimal satu data'], 422);
            }

            Perusahaan::whereIn('id', $ids)->delete();
            $this->forgetRekapWilayahCache();
            
            return response()->json(['message' => 'Data berhasil dihapus (' . count($ids) . ' data)']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }


    private function resolveKoordinat(Request $request): array
    {
        $latitude = $request->latitude;
        $longitude = $request->longitude;

        // Jika koordinat tidak diisi manual, coba ekstrak dari link Google Maps.
        if (($latitude === null || $longitude === null || $latitude === '' || $longitude === '') && $request->filled('maps_url')) {
            $koordinat = $this->ekstrakKoordinatDariUrl($request->maps_url)
                ?? $this->ekstrakKoordinatDariUrl($this->resolveShortMapsUrl($request->maps_url));

            if ($koordinat) {
                [$latitude, $longitude] = $koordinat;
            }
        }

        return [
            'latitude' => $latitude !== null && $latitude !== '' ? (float) $latitude : null,
            'longitude' => $longitude !== null && $longitude !== '' ? (float) $longitude : null,
        ];
    }

    private function ekstrakKoordinatDariUrl(?string $url): ?array
    {
        if (!$url) {
            return null;
        }

        $patterns = [
            '/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/',      // ...!3d-7.123!4d107.456 (titik marker, paling akurat)
            '/[?&]q=(-?\d+\.\d+),(-?\d+\.\d+)/',     // ...maps?q=-7.123,107.456
            '/@(-?\d+\.\d+),(-?\d+\.\d+)/',          // .../maps/@-7.123,107.456,17z (titik tengah peta)
            '/(-?\d{1,2}\.\d{4,}),\s*(-?\d{1,3}\.\d{4,})/', // koordinat polos
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return [(float) $matches[1], (float) $matches[2]];
            }
        }

        return null;
    }

    private function resolveShortMapsUrl(string $url): ?string
    {
        // Link pendek (maps.app.goo.gl / goo.gl) tidak memuat koordinat;
        // ikuti redirect untuk mendapatkan URL Google Maps lengkap.
        if (!preg_match('/^https?:\/\/(maps\.app\.goo\.gl|goo\.gl|g\.co)\//i', $url)) {
            return null;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->get($url);

            return $response->effectiveUri() ? (string) $response->effectiveUri() : null;
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function forgetRekapWilayahCache(): void
    {
        Cache::forget('perusahaan:rekap-wilayah:v5');
        Cache::forget('perusahaan:rekap-wilayah:v4');
        Cache::forget('perusahaan:rekap-wilayah:v3');
    }
}
