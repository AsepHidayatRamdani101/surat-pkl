<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratIzinOrtu extends Model
{
    use HasFactory;

    protected $table = 'surat_izin_ortu';

    protected $fillable = [
        
        'tanggal_surat',
        'siswa_id',
        'nama_ortu',
        'alamat_ortu',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
