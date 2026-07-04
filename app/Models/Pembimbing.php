<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembimbing extends Model
{
    use HasFactory;

    protected $table = 'pembimbings';

    protected $fillable = [
        'nama_pembimbing',
        'jenis_kelamin',
        'jabatan_pembimbing',
        'nip_pembimbing',
        'no_hp_pembimbing',
        'jumlah_jam',
        'jenis_guru',
        'jurusan_id',
    ];

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }

    public function tempatPkl()
    {
        return $this->hasMany(TempatPkl::class, 'pembimbing_id');
    }

    public function kelompokBimbingan()
    {
        return $this->hasMany(KelompokBimbingan::class, 'pembimbing_id');
    }
}
