<?php

namespace App\Support;

/**
 * Daftar role pimpinan/bidang sekolah. Role ini melihat data lintas jurusan
 * (read-only), kecuali Sarana & Prasarana yang juga mengelola data ruangan.
 */
class RoleRegistry
{
    public const ROLES = [
        'kepala_sekolah' => 'Kepala Sekolah',
        'waka_kurikulum' => 'Waka Kurikulum',
        'waka_hubin' => 'Waka Hubin',
        'waka_kesiswaan' => 'Waka Kesiswaan',
        'waka_sarpras' => 'Sarana dan Prasarana',
        'waka_bina_program' => 'Waka Bina Program',
    ];

    // Bidang waka yang bisa diberikan ke pembimbing mana pun. Kepala Sekolah memakai akun khusus.
    public static function wakaRoles(): array
    {
        return array_diff_key(self::ROLES, ['kepala_sekolah' => true]);
    }

    public static function isWaka(?string $role): bool
    {
        return $role !== null && array_key_exists($role, self::wakaRoles());
    }

    // Saran bidang waka dari jabatan pembimbing (dipakai untuk mengisi pilihan otomatis di form).
    public const JABATAN_WAKA = [
        'WAKASEK Kurikulum' => 'waka_kurikulum',
        'WAKASEK HUMAS HUBIN' => 'waka_hubin',
        'WAKASEK Sarana Prasarana' => 'waka_sarpras',
        'WAKASEK Kesiswaan' => 'waka_kesiswaan',
        'WAKASEK Bina Program' => 'waka_bina_program',
    ];

    public static function fromJabatan(?string $jabatan): ?string
    {
        return self::JABATAN_WAKA[$jabatan] ?? null;
    }

    public static function keys(): array
    {
        return array_keys(self::ROLES);
    }

    public static function has(?string $role): bool
    {
        return $role !== null && array_key_exists($role, self::ROLES);
    }

    public static function label(?string $role): string
    {
        return self::ROLES[$role] ?? (string) $role;
    }
}
