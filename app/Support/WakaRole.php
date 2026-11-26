<?php

namespace App\Support;

use App\Models\Pembimbing;
use Illuminate\Database\QueryException;

class WakaRole
{
    /**
     * Semua peran yang bisa dipilih pembimbing: pembimbing itu sendiri, waka, dan wali kelas.
     * Berisi satu item saja bila pembimbing tidak punya peran tambahan.
     */
    public static function optionsFor(?string $nip): array
    {
        $options = ['pembimbing' => 'Pembimbing'];

        if ($waka = self::forNip($nip)) {
            $options[$waka] = RoleRegistry::label($waka);
        }

        if (WaliKelasRole::forNip($nip)) {
            $options[WaliKelasRole::ROLE] = WaliKelasRole::LABEL;
        }

        return $options;
    }

    /**
     * Role waka milik pembimbing (berdasarkan NIP), atau null bila belum diaktifkan.
     */
    public static function forNip(?string $nip): ?string
    {
        if (!$nip) {
            return null;
        }

        try {
            $role = Pembimbing::query()->where('nip_pembimbing', $nip)->value('waka_role');
        } catch (QueryException $e) {
            // Kolom waka_role belum ada (migrasi belum dijalankan).
            return null;
        }

        return RoleRegistry::isWaka($role) ? $role : null;
    }
}
