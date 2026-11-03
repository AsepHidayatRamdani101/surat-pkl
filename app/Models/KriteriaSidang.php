<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KriteriaSidang extends Model
{
    use HasFactory;

    protected $table = 'kriteria_sidangs';

    protected $fillable = [
        'nama_kriteria',
        'bobot',
        'deskripsi',
        'urutan',
    ];

    public function rubriks()
    {
        return $this->hasMany(RubrikKriteriaSidang::class, 'kriteria_sidang_id')->orderByDesc('nilai_min');
    }

    public function nilaiSidang()
    {
        return $this->hasMany(NilaiSidang::class, 'kriteria_sidang_id');
    }
}
