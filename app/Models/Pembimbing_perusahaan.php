<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembimbing_perusahaan extends Model
{
    use HasFactory;
    protected $table = 'pembimbing_perusahaans';

    protected $fillable = [
        'nama_pembimbing',
        'perusahaan_id',
    ];


    public function tempatPkl()
    {
        return $this->hasMany(TempatPkl::class, 'pembimbing_perusahaan_id');
    }
}
