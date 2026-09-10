<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadAbsensiJurnalPkl extends Model
{
    use HasFactory;

    protected $fillable = ['siswa_id', 'tanggal', 'judul', 'keterangan', 'file_path', 'status'];

    protected $casts = ['tanggal' => 'date'];

    public function siswa() { return $this->belongsTo(Siswa::class); }
}