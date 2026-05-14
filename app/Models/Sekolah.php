<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sekolah extends Model
{
    use HasFactory;

    protected $table = 'sekolah';

    protected $fillable = [
        'nama_kepala_sekolah',
        'nip_kepala_sekolah',
        'tanggal_mulai_pkl',
        'tanggal_selesai_pkl',
        'cap_sekolah_path',
        'ttd_kepala_sekolah_path',
    ];

    protected $casts = [
        'tanggal_mulai_pkl' => 'date',
        'tanggal_selesai_pkl' => 'date',
    ];

    public function getCapSekolahUrlAttribute()
    {
        return $this->cap_sekolah_path ? asset('storage/' . $this->cap_sekolah_path) : null;
    }

    public function getTtdKepalaSekolahUrlAttribute()
    {
        return $this->ttd_kepala_sekolah_path ? asset('storage/' . $this->ttd_kepala_sekolah_path) : null;
    }
}
