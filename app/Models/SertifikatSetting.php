<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SertifikatSetting extends Model
{
    protected $fillable = [
        'background_path', 'background_front_path', 'background_back_path',
        'font_family', 'custom_font_path', 'custom_font_name',
        'design_layout',
    ];

    protected $casts = ['design_layout' => 'array'];

    public static function current(): self
    {
        return static::query()->first() ?? new static([
            'font_family' => 'Georgia, "Times New Roman", serif',
        ]);
    }

    public function fileDataUri(?string $path): ?string
    {
        if (!$path || !Storage::disk('public')->exists($path)) {
            return null;
        }

        $mime = Storage::disk('public')->mimeType($path) ?: 'image/png';
        return 'data:' . $mime . ';base64,' . base64_encode(Storage::disk('public')->get($path));
    }

    public function backgroundDataUri(): ?string
    {
        return $this->fileDataUri($this->background_front_path ?: $this->background_path);
    }

    public function backgroundFrontDataUri(): ?string
    {
        return $this->fileDataUri($this->background_front_path ?: $this->background_path)
            ?: $this->publicImageDataUri('depan.jpg');
    }

    public function backgroundBackDataUri(): ?string
    {
        return $this->fileDataUri($this->background_back_path)
            ?: $this->publicImageDataUri('belakang.jpg');
    }

    public function customFontDataUri(): ?string
    {
        return $this->fileDataUri($this->custom_font_path);
    }

    public function customFontUrl(): ?string
    {
        return $this->custom_font_path && Storage::disk('public')->exists($this->custom_font_path)
            ? asset('storage/' . $this->custom_font_path)
            : null;
    }

    public function publicImageDataUri(string $filename): ?string
    {
        $path = public_path($filename);
        return is_file($path) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($path)) : null;
    }

    public function layout(): array
    {
        $saved = $this->design_layout ?: [];
        $pimpinan = $saved['pimpinan'] ?? ['x' => 16, 'y' => 82];
        $kepalaSekolah = $saved['kepala_sekolah'] ?? ['x' => 62, 'y' => 82];
        $identitas = array_replace(
            ['x' => 0, 'y' => 51, 'size' => 15, 'font' => $this->font_family, 'weight' => 400, 'italic' => false, 'color' => '#1f2937'],
            $saved['nis'] ?? [],
            ['x' => 0]
        );
        $defaults = [
            '_canvas' => ['offset_x' => -75],
            'nama' => ['x' => 25, 'y' => 40, 'size' => 34, 'font' => $this->font_family, 'weight' => 700, 'italic' => false, 'color' => '#1f2937'],
            'identitas' => $identitas,
            'pkl_intro' => ['x' => 30, 'y' => 53, 'size' => 14, 'font' => $this->font_family, 'weight' => 400, 'italic' => false, 'color' => '#1f2937'],
            'perusahaan' => ['x' => 30, 'y' => 61, 'size' => 17, 'font' => $this->font_family, 'weight' => 600, 'italic' => false, 'color' => '#1f2937'],
            'pkl_period' => ['x' => 25, 'y' => 66, 'size' => 13, 'font' => $this->font_family, 'weight' => 400, 'italic' => false, 'color' => '#1f2937'],
            'pimpinan_title' => ['x' => $pimpinan['x'], 'y' => max(0, $pimpinan['y'] - 16), 'size' => 12, 'font' => $this->font_family, 'weight' => 400, 'italic' => false, 'color' => '#1f2937'],
            'kepala_title' => ['x' => $kepalaSekolah['x'], 'y' => max(0, $kepalaSekolah['y'] - 16), 'size' => 12, 'font' => $this->font_family, 'weight' => 400, 'italic' => false, 'color' => '#1f2937'],
            'kepala_sekolah' => ['x' => 62, 'y' => 82, 'size' => 13, 'font' => $this->font_family, 'weight' => 600, 'italic' => false, 'color' => '#1f2937'],
            'kepala_nip' => ['x' => $kepalaSekolah['x'], 'y' => min(96, $kepalaSekolah['y'] + 3), 'size' => 11, 'font' => $this->font_family, 'weight' => 400, 'italic' => false, 'color' => '#1f2937'],
            'pimpinan' => ['x' => 16, 'y' => 82, 'size' => 13, 'font' => $this->font_family, 'weight' => 600, 'italic' => false, 'color' => '#1f2937'],
            'pimpinan_nip' => ['x' => $pimpinan['x'], 'y' => min(96, $pimpinan['y'] + 3), 'size' => 11, 'font' => $this->font_family, 'weight' => 400, 'italic' => false, 'color' => '#1f2937'],
        ];

        $layout = array_replace_recursive($defaults, $saved);
        unset($layout['nis'], $layout['jurusan']);
        $layout['nama']['x'] = 0;
        $layout['perusahaan']['x'] = 0;

        return $layout;
    }
}
