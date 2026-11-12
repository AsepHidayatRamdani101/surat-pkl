<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SertifikatFont extends Model
{
    protected $fillable = ['name', 'path'];

    public function url(): ?string
    {
        return Storage::disk('public')->exists($this->path) ? asset('storage/' . $this->path) : null;
    }

    public function dataUri(): ?string
    {
        if (!Storage::disk('public')->exists($this->path)) {
            return null;
        }
        $mime = Storage::disk('public')->mimeType($this->path) ?: 'font/ttf';
        return 'data:' . $mime . ';base64,' . base64_encode(Storage::disk('public')->get($this->path));
    }
}
