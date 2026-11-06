<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiSelfieSetting extends Model
{
    use HasFactory;

    protected $table = 'absensi_selfie_settings';

    protected $fillable = [
        'jam_datang_mulai',
        'jam_datang_tepat_waktu',
        'jam_datang_selesai',
        'jam_pulang_mulai',
        'jam_pulang_selesai',
        'updated_by',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            [
                'jam_datang_mulai' => '06:00:00',
                'jam_datang_tepat_waktu' => '07:30:00',
                'jam_datang_selesai' => '12:00:00',
                'jam_pulang_mulai' => '15:00:00',
                'jam_pulang_selesai' => '21:00:00',
            ]
        );
    }
}
