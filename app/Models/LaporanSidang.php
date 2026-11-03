<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanSidang extends Model
{
    use HasFactory;

    protected $table = 'laporan_sidangs';

    protected $fillable = [
        'siswa_id',
        'judul',
        'file_path',
        'tipe_file',
        'keterangan',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
}
