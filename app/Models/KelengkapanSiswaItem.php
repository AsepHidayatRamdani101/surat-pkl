<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KelengkapanSiswaItem extends Model
{
    use HasFactory;

    protected $table = 'kelengkapan_siswa_items';

    protected $fillable = [
        'nama_item',
        'deskripsi',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}