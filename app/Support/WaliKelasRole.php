<?php

namespace App\Support;

use App\Models\Kelas;
use Illuminate\Database\QueryException;

class WaliKelasRole
{
    public const ROLE = 'wali_kelas';
    public const LABEL = 'Wali Kelas';

    public static function is(?string $role): bool
    {
        return $role === self::ROLE;
    }

    /**
     * ID kelas yang diampu pembimbing (berdasarkan NIP). Kosong bila bukan wali kelas.
     */
    public static function kelasIdsForNip(?string $nip): array
    {
        if (!$nip) {
            return [];
        }

        try {
            return Kelas::query()
                ->whereHas('waliKelas', fn ($q) => $q->where('nip_pembimbing', $nip))
                ->pluck('id')
                ->all();
        } catch (QueryException $e) {
            // Kolom wali_kelas_id belum ada (migrasi belum dijalankan).
            return [];
        }
    }

    public static function forNip(?string $nip): bool
    {
        return self::kelasIdsForNip($nip) !== [];
    }

    public static function label(?string $role): string
    {
        return $role === self::ROLE ? self::LABEL : RoleRegistry::label($role);
    }
}
