<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bimbingan extends Model
{
    use HasFactory;

    protected $fillable = [
        'pembimbing_id',
        'siswa_id',
        'tanggal_bimbingan',
        'topik_pembekalan',
        'materi_tipe',
        'materi_isi',
        'materi_file_path',
        'materi_video_url',
        'status_absensi',
        'tugas',
        'tugas_siswa',
        'nilai_tugas',
        'penilaian_sikap',
        'catatan',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function pembimbing()
    {
        return $this->belongsTo(Pembimbing::class, 'pembimbing_id');
    }
}
