<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NilaiSidang extends Model
{
    use HasFactory;

    protected $table = 'nilai_sidangs';

    protected $fillable = [
        'siswa_id',
        'kriteria_sidang_id',
        'pembimbing_id',
        'nilai',
        'catatan',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function kriteria()
    {
        return $this->belongsTo(KriteriaSidang::class, 'kriteria_sidang_id');
    }

    public function pembimbing()
    {
        return $this->belongsTo(Pembimbing::class, 'pembimbing_id');
    }
}
