<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class MenuKegiatanSetting extends Model
{
    protected $table = 'menu_kegiatan_settings';

    protected $fillable = ['kode', 'aktif'];

    protected $casts = ['aktif' => 'boolean'];

    public static function isActive(string $kode): bool
    {
        if (!Schema::hasTable('menu_kegiatan_settings')) {
            return true;
        }

        return static::where('kode', $kode)->value('aktif') ?? true;
    }
}
