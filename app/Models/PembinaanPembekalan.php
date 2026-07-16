<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembinaanPembekalan extends Model
{
    use HasFactory;

    protected $table = 'pembinaan_pembekalan';

    protected $fillable = [
        'siswa_id',
        'pembimbing_id',
        'tanggal_formulir',
        'waktu_formulir',
        'tempat',
        'kronologi',
        'komitmen_peserta',
        'catatan_guru',
        'jenis_pembinaan',
        'jenis_pembinaan_lainnya',
        'tindakan_pembinaan',
        'tindakan_pembinaan_lainnya',
        'hasil_pembinaan',
        'tingkat_pembinaan',
    ];

    protected $casts = [
        'tanggal_formulir' => 'date',
        'jenis_pembinaan' => 'array',
        'tindakan_pembinaan' => 'array',
        'hasil_pembinaan' => 'array',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function pembimbing()
    {
        return $this->belongsTo(Pembimbing::class, 'pembimbing_id');
    }
}
