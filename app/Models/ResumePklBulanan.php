<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResumePklBulanan extends Model
{
    use HasFactory;

    protected $fillable = ['siswa_id', 'bulan', 'judul', 'isi_resume', 'file_path', 'file_paths', 'status'];

    protected $casts = [
        'bulan' => 'date:Y-m-d',
        'file_paths' => 'array',
    ];

    public function getAttachmentsAttribute(): array
    {
        return array_values(array_unique(array_filter(array_merge(
            $this->file_path ? [$this->file_path] : [],
            $this->file_paths ?? []
        ))));
    }

    public function siswa() { return $this->belongsTo(Siswa::class); }
}