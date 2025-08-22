<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    protected $fillable = ['nis', 'kelas_id', 'nama_siswa', 'alamat_siswa', 'no_hp_siswa', 'nama_ortu', 'alamat_ortu', 'no_hp_ortu', 'status'];
    protected $table = 'siswa';

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function suratIzin()
    {
        return $this->hasOne(TempatPkl::class);
    }
}
