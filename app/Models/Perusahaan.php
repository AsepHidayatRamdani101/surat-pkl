<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perusahaan extends Model
{
    use HasFactory;

    protected $table = 'perusahaan';
    protected $fillable = ['id', 'nama_perusahaan', 'alamat'];

    public function tempatPkl()
    {
        return $this->hasMany(TempatPkl::class);
    }

    public function siswa()
    {
        return $this->hasMany(Siswa::class);
    }
}
