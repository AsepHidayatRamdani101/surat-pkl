<?php

namespace App\Support;

use Carbon\Carbon;

class JamKerjaSidang
{
    public const MULAI = '06:30';

    public const SELESAI = '15:00';

    public const ISTIRAHAT = [
        ['mulai' => '09:00', 'selesai' => '09:30', 'nama' => 'Istirahat'],
        ['mulai' => '12:00', 'selesai' => '12:30', 'nama' => 'Shalat Dzuhur'],
    ];

    public static function pada(Carbon $tanggal, string $jam): Carbon
    {
        return Carbon::parse($tanggal->toDateString() . ' ' . $jam);
    }

    /**
     * Majukan waktu mulai bila slot berdurasi $durasi menit menabrak jam istirahat.
     */
    public static function geser(Carbon $mulai, int $durasi): Carbon
    {
        $mulai = $mulai->copy();

        foreach (self::ISTIRAHAT as $istirahat) {
            $awal = self::pada($mulai, $istirahat['mulai']);
            $akhir = self::pada($mulai, $istirahat['selesai']);

            if ($mulai->lt($akhir) && $mulai->copy()->addMinutes($durasi)->gt($awal)) {
                $mulai = $akhir;
            }
        }

        return $mulai;
    }

    /**
     * Susun $jumlah slot berurutan; slot yang menabrak istirahat dimajukan ke akhir istirahat.
     *
     * @return array<int, array{0: Carbon, 1: Carbon}>
     */
    public static function susun(Carbon $mulai, int $jumlah, int $durasi): array
    {
        $slots = [];
        $waktu = $mulai->copy();

        for ($i = 0; $i < $jumlah; $i++) {
            $waktu = self::geser($waktu, $durasi);
            $selesai = $waktu->copy()->addMinutes($durasi);
            $slots[] = [$waktu, $selesai];
            $waktu = $selesai;
        }

        return $slots;
    }

    /**
     * Pesan kesalahan bila satu slot sidang berada di luar jam kerja atau menabrak istirahat.
     */
    public static function validasiSlot(Carbon $mulai, Carbon $selesai): ?string
    {
        if ($mulai->lt(self::pada($mulai, self::MULAI))) {
            return 'Sidang baru boleh dimulai pukul ' . self::MULAI . ' (jam kerja sekolah ' . self::MULAI . '–' . self::SELESAI . ').';
        }

        foreach (self::ISTIRAHAT as $istirahat) {
            $awal = self::pada($mulai, $istirahat['mulai']);
            $akhir = self::pada($mulai, $istirahat['selesai']);

            if ($mulai->lt($akhir) && $selesai->gt($awal)) {
                return "Waktu sidang {$mulai->format('H:i')}–{$selesai->format('H:i')} bertabrakan dengan "
                    . "{$istirahat['nama']} ({$istirahat['mulai']}–{$istirahat['selesai']}). Pilih waktu lain.";
            }
        }

        if ($selesai->gt(self::pada($mulai, self::SELESAI))) {
            return "Sidang {$mulai->format('H:i')}–{$selesai->format('H:i')} melewati jam kerja sekolah (tutup " . self::SELESAI . '). Pilih waktu lebih awal atau tambah hari.';
        }

        return null;
    }

    public static function untukJs(): array
    {
        return [
            'mulai' => self::MULAI,
            'selesai' => self::SELESAI,
            'istirahat' => self::ISTIRAHAT,
        ];
    }
}
