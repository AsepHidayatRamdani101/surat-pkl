<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiControl extends Model
{
    use HasFactory;

    protected $table = 'absensi_controls';

    protected $fillable = [
        'id',
        'is_active',
        'periode_dimulai_pada',
        'interval_hari',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'periode_dimulai_pada' => 'date:Y-m-d',
    ];
}
