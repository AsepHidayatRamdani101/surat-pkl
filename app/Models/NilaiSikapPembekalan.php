<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NilaiSikapPembekalan extends Model
{
    use HasFactory;

    protected $table = 'nilai_sikap_pembekalans';

    protected $fillable = [
        'pembimbing_id',
        'siswa_id',
        'materi_id',
        'tanggal_penilaian',
        'nilai_sikap',
        'catatan',
    ];

    public function materi()
    {
        return $this->belongsTo(Materi::class, 'materi_id');
    }

    public function pembimbing()
    {
        return $this->belongsTo(Pembimbing::class, 'pembimbing_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
}
