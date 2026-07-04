<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KelompokBimbingan extends Model
{
    use HasFactory;

    protected $table = 'kelompok_bimbingan';

    protected $fillable = [
        'nama_kelompok',
        'pembimbing_id',
        'metode',
        'created_by',
    ];

    public function pembimbing()
    {
        return $this->belongsTo(Pembimbing::class, 'pembimbing_id');
    }

    public function siswa()
    {
        return $this->belongsToMany(Siswa::class, 'kelompok_bimbingan_siswa', 'kelompok_bimbingan_id', 'siswa_id');
    }
}
