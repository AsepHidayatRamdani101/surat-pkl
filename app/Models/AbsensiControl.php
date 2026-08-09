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
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
