<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SertifikatPenilaian extends Model
{
    protected $table = 'sertifikat_penilaians';
    protected $fillable = ['siswa_id', 'sertifikat_aspek_id', 'nilai', 'predikat'];
}
