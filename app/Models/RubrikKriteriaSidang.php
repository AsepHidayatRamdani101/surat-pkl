<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RubrikKriteriaSidang extends Model
{
    use HasFactory;

    protected $table = 'rubrik_kriteria_sidangs';

    protected $fillable = [
        'kriteria_sidang_id',
        'label',
        'nilai_min',
        'nilai_max',
        'deskripsi',
    ];

    public function kriteria()
    {
        return $this->belongsTo(KriteriaSidang::class, 'kriteria_sidang_id');
    }
}
