<?php

namespace App\Http\Controllers;

use App\Models\SertifikatSetting;
use App\Models\SertifikatFont;
use App\Models\Jurusan;
use App\Models\SertifikatAspek;
use App\Models\Siswa;
use App\Models\Sekolah;
use App\Services\WilayahIndonesiaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SertifikatSettingController extends Controller
{
    public function edit(Request $request)
    {
        $requestedTemplate = $request->route('template') ?? $request->query('template');
        $activeTemplate = $requestedTemplate === 'pembekalan' ? 'pembekalan' : 'pelaksanaan';
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
        $pelaksanaanLayout = $setting->layout();
        $pembekalanLayout = $setting->pembekalanLayout();
        $numberPreviewReplacements = [
            '{nis}' => $sampleSiswa?->nis ?? '123456',
            '{tahun}' => now()->format('Y'),
        ];
        $formatNomor = fn (string $text) => strtr($text, $numberPreviewReplacements);
        $pelaksanaanSampleValues = [
            'nomor_sertifikat' => $formatNomor($pelaksanaanLayout['nomor_sertifikat']['text']),
            'nama' => mb_strtoupper($sampleSiswa?->nama_siswa ?? 'NAMA SISWA', 'UTF-8'),
            'identitas' => ($sampleSiswa?->nis ?? '123456') . ' | ' . ($sampleSiswa?->kelas?->jurusan?->nama_jurusan ?? 'JURUSAN / KOMPETENSI'),
            'pkl_intro' => 'Telah melakukan Praktek Kerja Lapangan (PKL) di',
            'perusahaan' => mb_strtoupper($sampleTempat?->perusahaan?->nama_perusahaan ?? 'NAMA PERUSAHAAN', 'UTF-8'),
            'pkl_period' => $sampleMulai && $sampleSelesai
                ? 'selama ' . $sampleBulan . ' bulan terhitung pada tanggal ' . $sampleMulai->translatedFormat('d F Y') . ' - ' . $sampleSelesai->translatedFormat('d F Y')
                : 'selama - bulan terhitung pada tanggal -',
            'alamat_perusahaan' => mb_convert_case(
                app(WilayahIndonesiaService::class)->formatAlamatPerusahaan($sampleTempat?->perusahaan),
                MB_CASE_TITLE,
                'UTF-8'
            ),
            'tanggal_cetak' => 'Garut, ' . now()->locale('id')->translatedFormat('d F Y'),
            'pimpinan_title' => 'Pimpinan / Pembimbing DUDIKA',
            'kepala_title' => 'Kepala SMK Negeri 8 Garut',
            'pimpinan' => $this->titleCasePerson($sampleTempat?->nama_pemilik_instansi
                ?: ($sampleTempat?->perusahaan?->nama_pemilik_perusahaan ?: 'PIMPINAN PERUSAHAAN')),
            'pimpinan_nip' => $this->formatNip($sampleTempat?->nip_pemilik_instansi),
            'kepala_sekolah' => $sampleSekolah?->nama_kepala_sekolah ?? 'KEPALA SEKOLAH',
            'kepala_nip' => 'NIP. ' . ($sampleSekolah?->nip_kepala_sekolah ?? '-'),
        ];
        $pembekalanSampleValues = [
            'nomor_sertifikat' => $formatNomor($pembekalanLayout['nomor_sertifikat']['text']),
            'nama' => mb_strtoupper($sampleSiswa?->nama_siswa ?? 'NAMA SISWA', 'UTF-8'),
            'identitas' => ($sampleSiswa?->nis ?? '123456') . ' | ' . ($sampleSiswa?->kelas?->jurusan?->nama_jurusan ?? 'JURUSAN / KOMPETENSI'),
            'pembekalan_intro' => 'atas partisipasi dan keberhasilannya dalam mengikuti kegiatan',
            'pembekalan_title' => 'PEMBEKALAN PRAKTIK KERJA LAPANGAN (PKL)',
            'pembekalan_result' => 'yang diselenggarakan oleh SMK Negeri 8 Garut',
            'tanggal_cetak' => 'Garut, ' . now()->locale('id')->translatedFormat('d F Y'),
            'kepala_title' => 'Kepala SMK Negeri 8 Garut',
            'kepala_sekolah' => $sampleSekolah?->nama_kepala_sekolah ?? 'KEPALA SEKOLAH',
            'kepala_nip' => 'NIP. ' . ($sampleSekolah?->nip_kepala_sekolah ?? '-'),
        ];
        $sampleValues = $activeTemplate === 'pembekalan' ? $pembekalanSampleValues : $pelaksanaanSampleValues;
        $frontLayout = $activeTemplate === 'pembekalan' ? $pembekalanLayout : $pelaksanaanLayout;
        $backLayout = $activeTemplate === 'pembekalan' ? $setting->pembekalanBackLayout() : $setting->backLayout();
        $centeredFrontKeys = $activeTemplate === 'pembekalan'
            ? ['nomor_sertifikat', 'nama', 'identitas', 'pembekalan_intro', 'pembekalan_title', 'pembekalan_result']
            : ['nomor_sertifikat', 'nama', 'identitas', 'perusahaan', 'alamat_perusahaan', 'tanggal_cetak'];
        $backSampleValues = [
            'title' => $activeTemplate === 'pembekalan'
                ? 'NILAI PEMBEKALAN PRAKTIK KERJA LAPANGAN (PKL)'
                : 'NILAI PRAKTIK KERJA LAPANGAN (PKL)',
            'date' => 'Garut, ' . now()->locale('id')->translatedFormat('d F Y'),
            'signature_title' => $activeTemplate === 'pembekalan' ? 'Kepala SMK Negeri 8 Garut' : 'Pembimbing DUDIKA',
            'signature_name' => $activeTemplate === 'pembekalan'
                ? ($sampleSekolah?->nama_kepala_sekolah ?? 'KEPALA SEKOLAH')
                : 'AHMAD RAMADANI',
            'signature_nip' => $activeTemplate === 'pembekalan'
                ? $this->formatNip($sampleSekolah?->nip_kepala_sekolah)
                : 'NIP. 242518421002',
        ];

        return view('sertifikat.setting', compact(
            'setting',
            'fonts',
            'jurusans',
            'sampleValues',
            'sampleSiswa',
            'activeTemplate',
            'frontLayout',
            'backLayout',
            'centeredFrontKeys',
            'backSampleValues'
            , 'numberPreviewReplacements'
        ));
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
            'template' => ['required', Rule::in(['pelaksanaan', 'pembekalan'])],
            'font_family' => ['required', 'string', 'max:120', Rule::in($fontOptions)],
            'background_front' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'background_front_pembekalan' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
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
            'back_design_layout' => ['nullable', 'json'],
        ], [
            'background_front.image' => 'Background depan harus berupa gambar.',
            'background_back.image' => 'Background belakang harus berupa gambar.',
            'background_front.max' => 'Ukuran background depan maksimal 5 MB.',
            'background_front_pembekalan.max' => 'Ukuran background depan pembekalan maksimal 5 MB.',
            'background_back.max' => 'Ukuran background belakang maksimal 5 MB.',
            'custom_font.max' => 'Ukuran font maksimal 2 MB.',
        ]);

        $setting = $current ?? new SertifikatSetting();
        $isPembekalan = $validated['template'] === 'pembekalan';
        $setting->font_family = $validated['font_family'];
        if (!empty($validated['design_layout'])) {
            $layout = json_decode($validated['design_layout'], true);
            $layout['_canvas']['offset_x'] = max(-200, min(200, (float) ($layout['_canvas']['offset_x'] ?? -75)));
            foreach ($layout as $key => &$item) {
                if (in_array($key, ['_canvas', '_back'], true)) {
                    continue;
                }
                $item['x'] = max(0, min(96, (float) ($item['x'] ?? 0)));
                $item['y'] = max(0, min(94, (float) ($item['y'] ?? 0)));
                $item['size'] = max(8, min(72, (float) ($item['size'] ?? 14)));
                $item['font'] = in_array($item['font'] ?? null, $fontOptions, true)
                    ? $item['font']
                    : $validated['font_family'];
                $item['weight'] = in_array((int) ($item['weight'] ?? 400), [300, 400, 500, 600, 700, 800], true)
                    ? (int) ($item['weight'] ?? 400)
                    : 400;
                $item['italic'] = filter_var($item['italic'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $item['color'] = preg_match('/^#[0-9A-Fa-f]{6}$/', (string) ($item['color'] ?? ''))
                    ? strtolower($item['color'])
                    : '#1f2937';
                $item['text_custom'] = filter_var($item['text_custom'] ?? false, FILTER_VALIDATE_BOOLEAN);
                if ($item['text_custom']) {
                    $item['text'] = mb_substr(trim(strip_tags((string) ($item['text'] ?? ''))), 0, 300);
                }
                if ($key === 'nomor_sertifikat') {
                    $item['text_custom'] = true;
                    $defaultNumber = $isPembekalan ? 'Nomor: SPB/{nis}/{tahun}' : 'Nomor: SPL/{nis}/{tahun}';
                    $item['text'] = mb_substr(trim(strip_tags((string) ($item['text'] ?? $defaultNumber))), 0, 150);
                    $item['text'] = $item['text'] !== '' ? $item['text'] : $defaultNumber;
                }
                if ($key === 'identitas') {
                    $item['text_custom'] = false;
                    unset($item['text']);
                }
            }
            unset($item);
            if (!empty($validated['back_design_layout'])) {
                $backLayout = json_decode($validated['back_design_layout'], true);
                $defaults = $isPembekalan ? $setting->pembekalanBackLayout() : $setting->backLayout();
                $allowedBackElements = ['title', 'legend', 'date', 'signature_title', 'signature_name', 'signature_nip'];
                $cleanBackLayout = [];
                foreach ($allowedBackElements as $key) {
                    $item = is_array($backLayout[$key] ?? null) ? $backLayout[$key] : [];
                    $default = $defaults[$key];
                    $cleanBackLayout[$key] = [
                        'x' => max(0, min(100, (float) ($item['x'] ?? $default['x']))),
                        'y' => max(0, min(96, (float) ($item['y'] ?? $default['y']))),
                        'size' => max(8, min(72, (float) ($item['size'] ?? $default['size']))),
                        'font' => in_array($item['font'] ?? null, $fontOptions, true) ? $item['font'] : $validated['font_family'],
                        'weight' => in_array((int) ($item['weight'] ?? 400), [300, 400, 500, 600, 700, 800], true) ? (int) ($item['weight'] ?? 400) : 400,
                        'italic' => filter_var($item['italic'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'color' => preg_match('/^#[0-9A-Fa-f]{6}$/', (string) ($item['color'] ?? '')) ? strtolower($item['color']) : '#1f2937',
                        'text_custom' => filter_var($item['text_custom'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'text' => mb_substr(trim(strip_tags((string) ($item['text'] ?? ''))), 0, 300),
                    ];
                }
                $rawTable = is_array($backLayout['_table'] ?? null) ? $backLayout['_table'] : [];
                $defaultTable = $defaults['_table'];
                $sanitizeTableText = function (string $key) use ($rawTable, $defaultTable, $fontOptions, $validated): array {
                    $item = is_array($rawTable[$key] ?? null) ? $rawTable[$key] : [];
                    $default = $defaultTable[$key];
                    $weight = (int) ($item['weight'] ?? $default['weight']);
                    return [
                        'size' => max(8, min(30, (float) ($item['size'] ?? $default['size']))),
                        'font' => in_array($item['font'] ?? null, $fontOptions, true) ? $item['font'] : $validated['font_family'],
                        'weight' => in_array($weight, [300, 400, 500, 600, 700, 800], true) ? $weight : $default['weight'],
                        'italic' => filter_var($item['italic'] ?? $default['italic'], FILTER_VALIDATE_BOOLEAN),
                        'color' => preg_match('/^#[0-9A-Fa-f]{6}$/', (string) ($item['color'] ?? '')) ? strtolower($item['color']) : $default['color'],
                    ];
                };
                $headerColor = $rawTable['header_color'] ?? '#e5e7eb';
                $cleanBackLayout['_table'] = [
                    'x' => max(0, min(90, (float) ($rawTable['x'] ?? $defaultTable['x']))),
                    'y' => max(0, min(90, (float) ($rawTable['y'] ?? $defaultTable['y']))),
                    'width' => max(10, min(98, (float) ($rawTable['width'] ?? $defaultTable['width']))),
                    'height' => max(10, min(90, (float) ($rawTable['height'] ?? $defaultTable['height']))),
                    'header_color' => preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $headerColor)
                        ? strtolower($headerColor)
                        : '#e5e7eb',
                    'header' => $sanitizeTableText('header'),
                    'body' => $sanitizeTableText('body'),
                ];
                if ($isPembekalan) {
                    $pembekalanBackLayout = $cleanBackLayout;
                } else {
                    $layout['_back'] = $cleanBackLayout;
                }
            }
            if ($isPembekalan) {
                $savedLayout = is_array($setting->design_layout) ? $setting->design_layout : [];
                $savedLayout['_pembekalan'] = $layout;
                if (isset($pembekalanBackLayout)) {
                    $savedLayout['_pembekalan_back'] = $pembekalanBackLayout;
                }
                $setting->design_layout = $savedLayout;
            } else {
                $currentLayout = is_array($setting->design_layout) ? $setting->design_layout : [];
                foreach (['_pembekalan', '_pembekalan_back'] as $key) {
                    if (isset($currentLayout[$key])) {
                        $layout[$key] = $currentLayout[$key];
                    }
                }
                $setting->design_layout = $layout;
            }
        }

        foreach (['background_front', 'background_front_pembekalan', 'background_back'] as $field) {
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
        $column = match ($side) {
            'pembekalan' => 'background_front_pembekalan_path',
            'back' => 'background_back_path',
            default => 'background_front_path',
        };
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

    private function titleCasePerson(string $value): string
    {
        return collect(preg_split('/(\s+)/u', $value, -1, PREG_SPLIT_DELIM_CAPTURE))
            ->map(fn (string $part) => substr_count($part, '.') >= 2
                ? $part
                : mb_convert_case($part, MB_CASE_TITLE, 'UTF-8'))
            ->implode('');
    }

    private function formatNip($value): string
    {
        $nip = trim((string) $value);
        $nip = trim((string) preg_replace('/^NIP\.?\s*/i', '', $nip));

        return $nip !== '' && $nip !== '-' ? 'NIP. ' . $nip : '';
    }
}
