<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiJurnalRekap extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id',
        'periode_mulai',
        'periode_selesai',
        'jumlah_hadir',
        'jumlah_izin',
        'jumlah_sakit',
        'jumlah_alpa',
        'updated_by',
    ];

    protected $casts = [
        'periode_mulai' => 'date',
        'periode_selesai' => 'date',
    ];

    public function siswa() { return $this->belongsTo(Siswa::class); }
}
