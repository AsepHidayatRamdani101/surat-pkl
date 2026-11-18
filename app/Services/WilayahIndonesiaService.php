<?php

namespace App\Services;

use App\Models\Perusahaan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class WilayahIndonesiaService
{
    private const BASE_URL = 'https://www.emsifa.com/api-wilayah-indonesia/api';

    public function formatAlamatPerusahaan(?Perusahaan $perusahaan): string
    {
        if (! $perusahaan) {
            return '-';
        }

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
                $response = Http::timeout(15)->retry(2, 150)->get($url);

                return $response->successful() ? ($response->json() ?? []) : [];
            } catch (Throwable) {
                return [];
            }
        });
    }
}
