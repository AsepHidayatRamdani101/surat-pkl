<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NilaiTugasPembekalan extends Model
{
    use HasFactory;

    protected $table = 'nilai_tugas_pembekalans';

    protected $fillable = [
        'jawaban_tugas_siswa_id',
        'pembimbing_id',
        'nilai',
        'catatan',
        'dinilai_at',
    ];

    public function jawabanTugasSiswa()
    {
        return $this->belongsTo(JawabanTugasSiswa::class, 'jawaban_tugas_siswa_id');
    }

    public function pembimbing()
    {
        return $this->belongsTo(Pembimbing::class, 'pembimbing_id');
    }
}
