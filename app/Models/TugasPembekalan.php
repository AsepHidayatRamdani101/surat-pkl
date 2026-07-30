<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\JawabanTugasSiswa;

class TugasPembekalan extends Model
{
    use HasFactory;

    protected $table = 'tugas_pembekalans';

    protected $fillable = [
        'materi_id',
        'tanggal_tugas',
        'judul_tugas',
        'soal_essay',
        'soal_files',
        'soal_parsed_prompts',
        'soal_parsed_at',
        'deskripsi_tugas',
        'deadline',
    ];

    protected $casts = [
        'soal_essay' => 'array',
        'soal_files' => 'array',
        'soal_parsed_prompts' => 'array',
        'soal_parsed_at' => 'datetime',
    ];

    public function materi()
    {
        return $this->belongsTo(Materi::class, 'materi_id');
    }

    public function jawabanSiswa()
    {
        return $this->hasMany(JawabanTugasSiswa::class, 'tugas_pembekalan_id');
    }
}
