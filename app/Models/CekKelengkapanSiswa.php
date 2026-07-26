<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CekKelengkapanSiswa extends Model
{
    use HasFactory;

    protected $table = 'cek_kelengkapan_siswas';

    protected $fillable = [
        'pembimbing_id',
        'siswa_id',
        'tanggal_cek',
        'sesi_cek',
        'item_checks',
        'is_lengkap',
        'catatan',
    ];

    protected $casts = [
        'tanggal_cek' => 'date',
        'item_checks' => 'array',
        'is_lengkap' => 'boolean',
    ];

    public function pembimbing()
    {
        return $this->belongsTo(Pembimbing::class, 'pembimbing_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
}