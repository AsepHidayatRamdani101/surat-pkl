<?php

namespace App\Http\Controllers;

use App\Models\SertifikatSetting;
use App\Models\SertifikatFont;
use App\Models\Jurusan;
use App\Models\SertifikatAspek;
use App\Models\Siswa;
use App\Models\Sekolah;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SertifikatSettingController extends Controller
{
    public function edit()
    {
        $setting = SertifikatSetting::current();
        $fonts = SertifikatFont::orderBy('name')->get();
        $jurusans = Jurusan::with(['aspeks' => fn ($q) => $q->orderBy('urutan')->orderBy('id')])->orderBy('nama_jurusan')->get();
        $sampleSiswa = Siswa::with(['kelas.jurusan', 'suratIzin.perusahaan'])->orderBy('nama_siswa')->first();
        $sampleTempat = $sampleSiswa?->suratIzin;
        $sampleSekolah = Sekolah::latest('id')->first();
        $sampleMulai = $sampleTempat?->tanggal_mulai ? Carbon::parse($sampleTempat->tanggal_mulai)->locale('id') : null;
        $sampleSelesai = $sampleTempat?->tanggal_selesai ? Carbon::parse($sampleTempat->tanggal_selesai)->locale('id') : null;
        $sampleBulan = $sampleMulai && $sampleSelesai
            ? (($sampleSelesai->year - $sampleMulai->year) * 12 + $sampleSelesai->month - $sampleMulai->month + 1)
            : null;
        $sampleValues = [
            'nama' => strtoupper($sampleSiswa?->nama_siswa ?? 'NAMA SISWA'),
            'identitas' => ($sampleSiswa?->nis ?? '123456') . ' | ' . ($sampleSiswa?->kelas?->jurusan?->nama_jurusan ?? 'JURUSAN / KOMPETENSI'),
            'pkl_intro' => 'Telah melakukan Praktek Kerja Lapangan (PKL) di',
            'perusahaan' => $sampleTempat?->perusahaan?->nama_perusahaan ?? 'NAMA PERUSAHAAN',
            'pkl_period' => $sampleMulai && $sampleSelesai
                ? 'selama ' . $sampleBulan . ' bulan terhitung pada tanggal ' . $sampleMulai->translatedFormat('d F Y') . ' - ' . $sampleSelesai->translatedFormat('d F Y')
                : 'selama - bulan terhitung pada tanggal -',
            'pimpinan_title' => 'Pimpinan / Pembimbing DUDIKA',
            'kepala_title' => 'Kepala SMK Negeri 8 Garut',
            'pimpinan' => $sampleTempat?->nama_pemilik_instansi
                ?: ($sampleTempat?->perusahaan?->nama_pemilik_perusahaan ?: 'PIMPINAN PERUSAHAAN'),
            'pimpinan_nip' => 'NIP. ' . ($sampleTempat?->nip_pemilik_instansi ?: '-'),
            'kepala_sekolah' => $sampleSekolah?->nama_kepala_sekolah ?? 'KEPALA SEKOLAH',
            'kepala_nip' => 'NIP. ' . ($sampleSekolah?->nip_kepala_sekolah ?? '-'),
        ];
        return view('sertifikat.setting', compact('setting', 'fonts', 'jurusans', 'sampleValues', 'sampleSiswa'));
    }

    public function update(Request $request)
    {
        $current = SertifikatSetting::query()->first();
        $fontOptions = [
            'Georgia, "Times New Roman", serif', 'Arial, Helvetica, sans-serif',
            '"Times New Roman", Times, serif', '"Courier New", Courier, monospace',
            'Verdana, Geneva, sans-serif',
        ];
        $fontOptions = array_merge($fontOptions, SertifikatFont::pluck('name')->all());
        if ($current?->custom_font_name) $fontOptions[] = $current->custom_font_name;
        $validated = $request->validate([
            'font_family' => ['required', 'string', 'max:120', Rule::in($fontOptions)],
            'background_front' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'background_back' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'custom_fonts' => ['nullable', 'array', 'max:10'],
            'custom_fonts.*' => [
                'nullable',
                'file',
                'max:2048',
                function (string $attribute, $file, \Closure $fail) {
                    $extension = strtolower((string) $file->getClientOriginalExtension());
                    if (!in_array($extension, ['ttf', 'otf', 'woff', 'woff2'], true)) {
                        $fail('Font harus berformat TTF, OTF, WOFF, atau WOFF2.');
                    }
                },
            ],
            'design_layout' => ['nullable', 'json'],
        ], [
            'background_front.image' => 'Background depan harus berupa gambar.',
            'background_back.image' => 'Background belakang harus berupa gambar.',
            'background_front.max' => 'Ukuran background depan maksimal 5 MB.',
            'background_back.max' => 'Ukuran background belakang maksimal 5 MB.',
            'custom_font.max' => 'Ukuran font maksimal 2 MB.',
        ]);

        $setting = $current ?? new SertifikatSetting();
        $setting->font_family = $validated['font_family'];
        if (!empty($validated['design_layout'])) {
            $layout = json_decode($validated['design_layout'], true);
            $layout['_canvas']['offset_x'] = max(-200, min(200, (float) ($layout['_canvas']['offset_x'] ?? -75)));
            foreach ($layout as $key => &$item) {
                if ($key === '_canvas') {
                    continue;
                }
                $item['x'] = max(0, min(96, (float) ($item['x'] ?? 0)));
                $item['y'] = max(0, min(94, (float) ($item['y'] ?? 0)));
                $item['size'] = max(8, min(72, (float) ($item['size'] ?? 14)));
                $item['font'] = in_array($item['font'] ?? null, $fontOptions, true)
                    ? $item['font']
                    : $validated['font_family'];
                $item['weight'] = in_array((int) ($item['weight'] ?? 400), [300, 400, 500, 600, 700, 800], true)
                    ? (int) $item['weight']
                    : 400;
                $item['italic'] = filter_var($item['italic'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $item['color'] = preg_match('/^#[0-9A-Fa-f]{6}$/', (string) ($item['color'] ?? ''))
                    ? strtolower($item['color'])
                    : '#1f2937';
            }
            $setting->design_layout = $layout;
        }

        foreach (['background_front', 'background_back'] as $field) {
            if ($request->hasFile($field)) {
                $column = $field . '_path';
                if ($setting->{$column}) {
                    Storage::disk('public')->delete($setting->{$column});
                }
                $setting->{$column} = $request->file($field)->store('sertifikat/backgrounds', 'public');
            }
        }

        if ($request->hasFile('custom_fonts')) {
            $uploadedFonts = [];
            foreach ($request->file('custom_fonts') as $file) {
                $name = preg_replace('/[^A-Za-z0-9 _-]/', '', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'Font Custom';
                $name = SertifikatFont::where('name', $name)->exists() ? $name . ' ' . now()->format('His') : $name;
                $font = SertifikatFont::create(['name' => $name, 'path' => $file->store('sertifikat/fonts', 'public')]);
                $uploadedFonts[] = $font;
            }
            if ($uploadedFonts) {
                $setting->font_family = $uploadedFonts[0]->name;
            }
        }

        $setting->save();
        return back()->with('success', 'Pengaturan sertifikat berhasil disimpan.');
    }

    public function removeBackground(Request $request)
    {
        $setting = SertifikatSetting::query()->first();
        $side = $request->input('side', 'front');
        $column = $side === 'back' ? 'background_back_path' : 'background_front_path';
        if ($setting?->{$column}) {
            Storage::disk('public')->delete($setting->{$column});
            $updates = [$column => null];
            if ($side === 'front') {
                $updates['background_path'] = null;
            }
            $setting->update($updates);
        }
        return back()->with('success', 'Background ' . ($side === 'back' ? 'belakang' : 'depan') . ' dihapus.');
    }

    public function removeFont(SertifikatFont $font)
    {
        $setting = SertifikatSetting::query()->first();
        Storage::disk('public')->delete($font->path);
        if ($setting?->font_family === $font->name) {
            $setting->update(['font_family' => 'Georgia, "Times New Roman", serif']);
        }
        $font->delete();
        return back()->with('success', 'Font custom dihapus.');
    }

    public function storeAspek(Request $request)
    {
        $data = $request->validate(['jurusan_id' => ['required', 'exists:jurusan,id'], 'nama_aspek' => ['required', 'string', 'max:120']]);
        $data['urutan'] = (int) SertifikatAspek::where('jurusan_id', $data['jurusan_id'])->max('urutan') + 1;
        SertifikatAspek::create($data);
        return back()->with('success', 'Aspek penilaian ditambahkan.');
    }

    public function destroyAspek(SertifikatAspek $aspek)
    {
        $aspek->delete();
        return back()->with('success', 'Aspek penilaian dihapus.');
    }
}
