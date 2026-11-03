<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SertifikatSidang extends Model
{
    use HasFactory;

    protected $table = 'sertifikat_sidangs';

    protected $fillable = [
        'siswa_id',
        'nomor_sertifikat',
        'nilai_akhir',
        'predikat',
        'generated_at',
        'generated_by',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
}
