<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResumePklBulanan extends Model
{
    use HasFactory;

    protected $fillable = ['siswa_id', 'bulan', 'judul', 'isi_resume', 'file_path', 'status'];

    protected $casts = ['bulan' => 'date'];

    public function siswa() { return $this->belongsTo(Siswa::class); }
}