<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RuanganSidang extends Model
{
    use HasFactory;

    protected $table = 'ruangan_sidangs';

    protected $fillable = [
        'nama_ruangan',
        'lokasi',
        'kapasitas',
        'keterangan',
    ];

    public function jadwalSidang()
    {
        return $this->hasMany(JadwalSidang::class, 'ruangan_sidang_id');
    }
}
