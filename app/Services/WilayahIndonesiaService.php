<?php

namespace App\Services;

use App\Models\Perusahaan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Pool;
use Throwable;

class WilayahIndonesiaService
{
    private const BASE_URL = 'https://www.emsifa.com/api-wilayah-indonesia/api';

    public function formatAlamatPerusahaan(?Perusahaan $perusahaan): string
    {
        if (! $perusahaan) {
            return '-';
        }

        $this->primeRegionCache($perusahaan);

        $parts = [
            $perusahaan->alamat,
            $this->findName(
                "wilayah:villages:{$perusahaan->kecamatan_id}",
                self::BASE_URL . "/villages/{$perusahaan->kecamatan_id}.json",
                $perusahaan->desa_id
            ),
            $this->findName(
                "wilayah:districts:{$perusahaan->kabupaten_kota_id}",
                self::BASE_URL . "/districts/{$perusahaan->kabupaten_kota_id}.json",
                $perusahaan->kecamatan_id
            ),
            $this->findName(
                "wilayah:regencies:{$perusahaan->provinsi_id}",
                self::BASE_URL . "/regencies/{$perusahaan->provinsi_id}.json",
                $perusahaan->kabupaten_kota_id
            ),
            $this->findName(
                'wilayah:provinces',
                self::BASE_URL . '/provinces.json',
                $perusahaan->provinsi_id
            ),
        ];

        return collect($parts)
            ->filter(fn ($part) => filled($part))
            ->implode(', ');
    }

    public function namaKecamatan($kabupatenKotaId, $kecamatanId): ?string
    {
        return $this->findName(
            "wilayah:districts:{$kabupatenKotaId}",
            self::BASE_URL . "/districts/{$kabupatenKotaId}.json",
            $kecamatanId
        );
    }

    public function namaKabupatenKota($provinsiId, $kabupatenKotaId): ?string
    {
        return $this->findName(
            "wilayah:regencies:{$provinsiId}",
            self::BASE_URL . "/regencies/{$provinsiId}.json",
            $kabupatenKotaId
        );
    }

    private function findName(string $cacheKey, string $url, $targetId): ?string
    {
        if (blank($targetId)) {
            return null;
        }

        $region = collect($this->cachedJson($cacheKey, $url))
            ->first(fn ($item) => (string) ($item['id'] ?? '') === (string) $targetId);

        return $region['name'] ?? null;
    }

    private function cachedJson(string $cacheKey, string $url): array
    {
        return Cache::remember($cacheKey, now()->addDay(), function () use ($url) {
            try {
                $response = Http::connectTimeout(2)->timeout(4)->get($url);

                return $response->successful() ? ($response->json() ?? []) : [];
            } catch (Throwable) {
                return [];
            }
        });
    }

    private function primeRegionCache(Perusahaan $perusahaan): void
    {
        $requests = array_filter([
            "wilayah:villages:{$perusahaan->kecamatan_id}" => $perusahaan->kecamatan_id
                ? self::BASE_URL . "/villages/{$perusahaan->kecamatan_id}.json"
                : null,
            "wilayah:districts:{$perusahaan->kabupaten_kota_id}" => $perusahaan->kabupaten_kota_id
                ? self::BASE_URL . "/districts/{$perusahaan->kabupaten_kota_id}.json"
                : null,
            "wilayah:regencies:{$perusahaan->provinsi_id}" => $perusahaan->provinsi_id
                ? self::BASE_URL . "/regencies/{$perusahaan->provinsi_id}.json"
                : null,
            'wilayah:provinces' => $perusahaan->provinsi_id
                ? self::BASE_URL . '/provinces.json'
                : null,
        ]);

        $missing = array_filter($requests, fn ($url, $key) => !Cache::has($key), ARRAY_FILTER_USE_BOTH);
        if ($missing === []) {
            return;
        }

        try {
            $responses = Http::pool(fn (Pool $pool) => collect($missing)
                ->map(fn ($url, $key) => $pool->as($key)->connectTimeout(2)->timeout(4)->get($url))
                ->all());

            foreach ($missing as $key => $url) {
                $response = $responses[$key] ?? null;
                Cache::put(
                    $key,
                    $response && $response->successful() ? ($response->json() ?? []) : [],
                    $response && $response->successful() ? now()->addDay() : now()->addHour()
                );
            }
        } catch (Throwable) {
            foreach (array_keys($missing) as $key) {
                Cache::put($key, [], now()->addHour());
            }
        }
    }
}
