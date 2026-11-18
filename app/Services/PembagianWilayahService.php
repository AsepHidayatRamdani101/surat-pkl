<?php

namespace App\Services;

use App\Models\Perusahaan;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PembagianWilayahService
{
    private const KECAMATAN_WILAYAH_SELAAWI = [
        'SELAAWI',
        'BALUBUR LIMBANGAN',
        'BLUBUR LIMBANGAN',
        'CIBUGEL',
        'CIBIUK',
    ];

    public function __construct(private WilayahIndonesiaService $wilayah)
    {
    }

    public function rekap(): Collection
    {
        $perusahaanList = Perusahaan::select(
            'id',
            'nama_perusahaan',
            'provinsi_id',
            'kabupaten_kota_id',
            'kecamatan_id'
        )->whereHas('tempatPkl.siswa')->get();
        $grouped = [];

        foreach ($perusahaanList as $perusahaan) {
            $group = $this->groupFor($perusahaan);
            $key = $group['key'];

            if (! isset($grouped[$key])) {
                $grouped[$key] = $group + [
                    'jumlah' => 0,
                    'perusahaan_ids' => [],
                ];
            }

            $grouped[$key]['jumlah']++;
            $grouped[$key]['perusahaan_ids'][] = $perusahaan->id;
        }

        return collect($grouped)->values()->sortBy('wilayah')->values();
    }

    private function groupFor(Perusahaan $perusahaan): array
    {
        $kecamatanName = $this->wilayah->namaKecamatan(
            $perusahaan->kabupaten_kota_id,
            $perusahaan->kecamatan_id
        );
        $normalized = $kecamatanName ? Str::upper(trim($kecamatanName)) : null;

        if ($normalized && in_array($normalized, self::KECAMATAN_WILAYAH_SELAAWI, true)) {
            return [
                'key' => 'wilayah-selaawi',
                'wilayah' => 'Wilayah Selaawi',
                'kecamatan' => 'Kec. Selaawi, Kec. Blubur Limbangan, Kec. Cibugel, Kec. Cibiuk',
            ];
        }

        $kabupatenKotaName = $this->wilayah->namaKabupatenKota(
            $perusahaan->provinsi_id,
            $perusahaan->kabupaten_kota_id
        ) ?? 'Kabupaten/Kota Tidak Diketahui';

        return [
            'key' => 'kab-' . ($perusahaan->kabupaten_kota_id ?: Str::slug($kabupatenKotaName)),
            'wilayah' => 'Kabupaten/Kota ' . $kabupatenKotaName,
            'kecamatan' => 'Kabupaten/Kota ' . $kabupatenKotaName,
        ];
    }
}
