<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SertifikatSetting extends Model
{
    private array $dataUriCache = [];

    protected $fillable = [
        'background_path', 'background_front_path', 'background_front_pembekalan_path', 'background_back_path',
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

        if (array_key_exists($path, $this->dataUriCache)) {
            return $this->dataUriCache[$path];
        }

        $mime = Storage::disk('public')->mimeType($path) ?: 'image/png';
        return $this->dataUriCache[$path] = 'data:' . $mime . ';base64,' . base64_encode(Storage::disk('public')->get($path));
    }

    public function fileUrl(?string $path): ?string
    {
        return $path && Storage::disk('public')->exists($path)
            ? asset('storage/' . $path)
            : null;
    }

    public function filePreviewUrl(?string $path): ?string
    {
        if (!$path || !Storage::disk('public')->exists($path)) {
            return null;
        }

        $previewPath = 'sertifikat/previews/' . sha1($path . '|' . Storage::disk('public')->lastModified($path)) . '.jpg';
        if (!Storage::disk('public')->exists($previewPath) && function_exists('imagecreatefromstring')) {
            $source = @imagecreatefromstring(Storage::disk('public')->get($path));
            if ($source) {
                $width = imagesx($source);
                $height = imagesy($source);
                $targetWidth = min(1400, $width);
                $targetHeight = max(1, (int) round($height * ($targetWidth / $width)));
                $preview = imagecreatetruecolor($targetWidth, $targetHeight);
                imagecopyresampled($preview, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
                ob_start();
                imagejpeg($preview, null, 82);
                $contents = ob_get_clean();
                imagedestroy($preview);
                imagedestroy($source);
                if ($contents !== false) {
                    Storage::disk('public')->put($previewPath, $contents);
                }
            }
        }

        return $this->fileUrl(Storage::disk('public')->exists($previewPath) ? $previewPath : $path);
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

    public function backgroundFrontUrl(): ?string
    {
        return $this->fileUrl($this->background_front_path ?: $this->background_path)
            ?: asset('depan.jpg');
    }

    public function backgroundFrontPreviewUrl(): ?string
    {
        return $this->filePreviewUrl($this->background_front_path ?: $this->background_path)
            ?: asset('depan.jpg');
    }

    public function backgroundFrontPembekalanDataUri(): ?string
    {
        return $this->fileDataUri($this->background_front_pembekalan_path)
            ?: $this->backgroundFrontDataUri();
    }

    public function backgroundFrontPembekalanUrl(): ?string
    {
        return $this->fileUrl($this->background_front_pembekalan_path)
            ?: $this->backgroundFrontUrl();
    }

    public function backgroundFrontPembekalanPreviewUrl(): ?string
    {
        return $this->filePreviewUrl($this->background_front_pembekalan_path)
            ?: $this->backgroundFrontPreviewUrl();
    }

    public function backgroundBackDataUri(): ?string
    {
        return $this->fileDataUri($this->background_back_path)
            ?: $this->publicImageDataUri('belakang.jpg');
    }

    public function backgroundBackUrl(): ?string
    {
        return $this->fileUrl($this->background_back_path)
            ?: asset('belakang.jpg');
    }

    public function backgroundBackPreviewUrl(): ?string
    {
        return $this->filePreviewUrl($this->background_back_path)
            ?: asset('belakang.jpg');
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

    public function usedCustomFontNames(): array
    {
        $names = [];
        $walk = function ($value) use (&$walk, &$names): void {
            if (!is_array($value)) {
                return;
            }
            if (isset($value['font']) && is_string($value['font'])) {
                $names[] = $value['font'];
            }
            foreach ($value as $child) {
                $walk($child);
            }
        };
        $walk($this->design_layout ?: []);
        $names[] = $this->font_family;

        return array_values(array_unique(array_filter($names)));
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
            'nomor_sertifikat' => ['x' => 0, 'y' => 34, 'size' => 13, 'font' => $this->font_family, 'weight' => 400, 'italic' => false, 'color' => '#1f2937', 'text' => 'Nomor: SPL/{nis}/{tahun}'],
            'nama' => ['x' => 25, 'y' => 40, 'size' => 34, 'font' => $this->font_family, 'weight' => 700, 'italic' => false, 'color' => '#1f2937'],
            'identitas' => $identitas,
            'pkl_intro' => ['x' => 30, 'y' => 53, 'size' => 14, 'font' => $this->font_family, 'weight' => 400, 'italic' => false, 'color' => '#1f2937'],
            'perusahaan' => ['x' => 30, 'y' => 61, 'size' => 17, 'font' => $this->font_family, 'weight' => 600, 'italic' => false, 'color' => '#1f2937'],
            'pkl_period' => ['x' => 25, 'y' => 63, 'size' => 13, 'font' => $this->font_family, 'weight' => 400, 'italic' => false, 'color' => '#1f2937'],
            'alamat_perusahaan' => ['x' => 0, 'y' => 66, 'size' => 12, 'font' => $this->font_family, 'weight' => 400, 'italic' => false, 'color' => '#1f2937'],
            'tanggal_cetak' => ['x' => 0, 'y' => 70, 'size' => 12, 'font' => $this->font_family, 'weight' => 400, 'italic' => false, 'color' => '#1f2937'],
            'pimpinan_title' => ['x' => $pimpinan['x'], 'y' => max(0, $pimpinan['y'] - 16), 'size' => 12, 'font' => $this->font_family, 'weight' => 400, 'italic' => false, 'color' => '#1f2937'],
            'kepala_title' => ['x' => $kepalaSekolah['x'], 'y' => max(0, $kepalaSekolah['y'] - 16), 'size' => 12, 'font' => $this->font_family, 'weight' => 400, 'italic' => false, 'color' => '#1f2937'],
            'kepala_sekolah' => ['x' => 62, 'y' => 82, 'size' => 13, 'font' => $this->font_family, 'weight' => 600, 'italic' => false, 'color' => '#1f2937'],
            'kepala_nip' => ['x' => $kepalaSekolah['x'], 'y' => min(96, $kepalaSekolah['y'] + 3), 'size' => 11, 'font' => $this->font_family, 'weight' => 400, 'italic' => false, 'color' => '#1f2937'],
            'pimpinan' => ['x' => 16, 'y' => 82, 'size' => 13, 'font' => $this->font_family, 'weight' => 600, 'italic' => false, 'color' => '#1f2937'],
            'pimpinan_nip' => ['x' => $pimpinan['x'], 'y' => min(96, $pimpinan['y'] + 3), 'size' => 11, 'font' => $this->font_family, 'weight' => 400, 'italic' => false, 'color' => '#1f2937'],
        ];

        $layout = array_replace_recursive($defaults, $saved);
        unset($layout['nis'], $layout['jurusan'], $layout['_back'], $layout['_pembekalan'], $layout['_pembekalan_back']);
        $layout['nama']['x'] = 0;
        $layout['nomor_sertifikat']['x'] = 0;
        $layout['perusahaan']['x'] = 0;
        $layout['alamat_perusahaan']['x'] = 0;
        $layout['tanggal_cetak']['x'] = 0;
        $layout['pimpinan_nip']['x'] = $layout['pimpinan']['x'];
        $layout['kepala_nip']['x'] = $layout['kepala_sekolah']['x'];

        return $layout;
    }

    public function backLayout(): array
    {
        $saved = $this->design_layout['_back'] ?? [];
        $saved = is_array($saved) ? $saved : [];
        $font = $this->font_family ?: 'Georgia, "Times New Roman", serif';
        $defaults = [
            '_table' => [
                'header_color' => '#e5e7eb',
                'header' => ['size' => 11, 'font' => $font, 'weight' => 700, 'italic' => false, 'color' => '#1f2937'],
                'body' => ['size' => 11, 'font' => $font, 'weight' => 400, 'italic' => false, 'color' => '#1f2937'],
            ],
            'title' => ['x' => 50, 'y' => 18, 'size' => 20, 'font' => $font, 'weight' => 700, 'italic' => false, 'color' => '#1f2937'],
            'legend' => ['x' => 12, 'y' => 72, 'size' => 10, 'font' => $font, 'weight' => 400, 'italic' => false, 'color' => '#1f2937'],
            'date' => ['x' => 72, 'y' => 74, 'size' => 10, 'font' => $font, 'weight' => 400, 'italic' => false, 'color' => '#1f2937'],
            'signature_title' => ['x' => 72, 'y' => 78, 'size' => 10, 'font' => $font, 'weight' => 400, 'italic' => false, 'color' => '#1f2937'],
            'signature_name' => ['x' => 72, 'y' => 88, 'size' => 10, 'font' => $font, 'weight' => 600, 'italic' => false, 'color' => '#1f2937'],
            'signature_nip' => ['x' => 72, 'y' => 92, 'size' => 10, 'font' => $font, 'weight' => 400, 'italic' => false, 'color' => '#1f2937'],
        ];

        // Konversi pengaturan lama agar posisi yang pernah disimpan tetap mendekati tampilan sebelumnya.
        if (isset($saved['content_top']) && !isset($saved['title'])) {
            $defaults['title']['y'] = (float) $saved['content_top'];
        }
        if (isset($saved['title_size']) && !isset($saved['title'])) {
            $defaults['title']['size'] = (float) $saved['title_size'];
        }

        $layout = array_replace_recursive($defaults, array_intersect_key($saved, $defaults));
        $layout['signature_nip']['x'] = $layout['signature_name']['x'];

        return $layout;
    }

    public function pembekalanLayout(): array
    {
        $saved = $this->design_layout['_pembekalan'] ?? [];
        $saved = is_array($saved) ? $saved : [];
        $font = $this->font_family ?: 'Georgia, "Times New Roman", serif';
        $defaults = [
            '_canvas' => ['offset_x' => -75],
            'nomor_sertifikat' => ['x' => 0, 'y' => 34, 'size' => 13, 'font' => $font, 'weight' => 400, 'italic' => false, 'color' => '#1f2937', 'text' => 'Nomor: SPB/{nis}/{tahun}'],
            'nama' => ['x' => 0, 'y' => 42, 'size' => 34, 'font' => $font, 'weight' => 700, 'italic' => false, 'color' => '#1f2937'],
            'identitas' => ['x' => 0, 'y' => 51, 'size' => 15, 'font' => $font, 'weight' => 400, 'italic' => false, 'color' => '#1f2937'],
            'pembekalan_intro' => ['x' => 0, 'y' => 56, 'size' => 13, 'font' => $font, 'weight' => 400, 'italic' => false, 'color' => '#1f2937'],
            'pembekalan_title' => ['x' => 0, 'y' => 60, 'size' => 17, 'font' => $font, 'weight' => 700, 'italic' => false, 'color' => '#1f2937'],
            'pembekalan_result' => ['x' => 0, 'y' => 65, 'size' => 12, 'font' => $font, 'weight' => 400, 'italic' => false, 'color' => '#1f2937'],
            'tanggal_cetak' => ['x' => 66, 'y' => 70, 'size' => 12, 'font' => $font, 'weight' => 400, 'italic' => false, 'color' => '#1f2937'],
            'kepala_title' => ['x' => 66, 'y' => 74, 'size' => 12, 'font' => $font, 'weight' => 400, 'italic' => false, 'color' => '#1f2937'],
            'kepala_sekolah' => ['x' => 66, 'y' => 86, 'size' => 13, 'font' => $font, 'weight' => 600, 'italic' => false, 'color' => '#1f2937'],
            'kepala_nip' => ['x' => 66, 'y' => 89, 'size' => 11, 'font' => $font, 'weight' => 400, 'italic' => false, 'color' => '#1f2937'],
        ];

        $layout = array_replace_recursive($defaults, array_intersect_key($saved, $defaults));
        $layout['kepala_nip']['x'] = $layout['kepala_sekolah']['x'];

        return $layout;
    }

    public function pembekalanBackLayout(): array
    {
        $saved = $this->design_layout['_pembekalan_back'] ?? [];
        $saved = is_array($saved) ? $saved : [];
        $defaults = $this->backLayout();

        $layout = array_replace_recursive($defaults, array_intersect_key($saved, $defaults));
        $layout['signature_nip']['x'] = $layout['signature_name']['x'];

        return $layout;
    }
}
