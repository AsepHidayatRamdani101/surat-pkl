<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\NilaiTugasPembekalan;

class JawabanTugasSiswa extends Model
{
    use HasFactory;

    protected $table = 'jawaban_tugas_siswas';

    protected $fillable = [
        'tugas_pembekalan_id',
        'siswa_id',
        'jawaban_text',
        'lampiran_path',
        'submitted_at',
    ];

    public function tugasPembekalan()
    {
        return $this->belongsTo(TugasPembekalan::class, 'tugas_pembekalan_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function nilaiTugas()
    {
        return $this->hasOne(NilaiTugasPembekalan::class, 'jawaban_tugas_siswa_id');
    }
}
