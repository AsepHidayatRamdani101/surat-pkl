<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempatPkl extends Model
{
    use HasFactory;

    protected $table = 'tempat_pkl';

    protected $guarded = ['id'];

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function getSuratKesediaanPathAttribute($value)
    {
        return asset('storage/' . $value);
    }


    public function pembimbing()
    {
        return $this->belongsTo(Pembimbing::class, 'pembimbing_id');
    }

    public function pembimbingPerusahaan()
    {
        return $this->belongsTo(Pembimbing_perusahaan::class, 'pembimbing_perusahaan_id');
    }
}
