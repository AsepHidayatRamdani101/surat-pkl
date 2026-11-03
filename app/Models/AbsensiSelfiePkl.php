<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiSelfiePkl extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id',
        'perusahaan_id',
        'tanggal',
        'jenis',
        'waktu',
        'foto_path',
        'latitude',
        'longitude',
        'akurasi_gps',
        'jarak_meter',
        'persentase_lokasi',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function siswa() { return $this->belongsTo(Siswa::class); }
    public function perusahaan() { return $this->belongsTo(Perusahaan::class); }
}
