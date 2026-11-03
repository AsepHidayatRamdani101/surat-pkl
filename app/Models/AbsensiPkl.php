<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiPkl extends Model
{
    use HasFactory;

    protected $fillable = ['siswa_id', 'pembimbing_id', 'periode_mulai', 'periode_selesai', 'status', 'keterangan'];

    protected $casts = ['periode_mulai' => 'date:Y-m-d', 'periode_selesai' => 'date:Y-m-d'];

    public function siswa() { return $this->belongsTo(Siswa::class); }
    public function pembimbing() { return $this->belongsTo(Pembimbing::class); }
}