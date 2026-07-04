<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materi extends Model
{
    use HasFactory;

    protected $table = 'materis';

    protected $fillable = [
        'tanggal_materi',
        'topik',
        'tipe_materi',
        'isi_materi',
        'file_pdf_path',
        'video_url',
        'catatan',
    ];

    public function tugasPembekalan()
    {
        return $this->hasOne(TugasPembekalan::class, 'materi_id');
    }
}
