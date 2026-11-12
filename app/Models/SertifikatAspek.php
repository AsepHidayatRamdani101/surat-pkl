<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SertifikatAspek extends Model
{
    protected $table = 'sertifikat_aspeks';
    protected $fillable = ['jurusan_id', 'nama_aspek', 'urutan'];

    public function jurusan() { return $this->belongsTo(Jurusan::class); }
}
