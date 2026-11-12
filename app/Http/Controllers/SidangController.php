<?php

namespace App\Http\Controllers;

use App\Models\JadwalSidang;
use App\Models\KriteriaSidang;
use App\Models\LaporanSidang;
use App\Models\NilaiSidang;
use App\Models\Pembimbing;
use App\Models\Sekolah;
use App\Models\SertifikatSidang;
use App\Models\SertifikatSetting;
use App\Models\SertifikatFont;
use App\Models\SertifikatAspek;
use App\Models\SertifikatPenilaian;
use App\Models\Siswa;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SidangController extends Controller
{
    // ===== Laporan PKL untuk sidang (siswa upload PDF/PPT) =====

    public function laporan()
    {
        abort_unless(auth()->user()->role === 'siswa', 403);

        $siswa = $this->currentSiswa();
        $laporan = LaporanSidang::where('siswa_id', $siswa->id)->first();

        return view('sidang.laporan.index', compact('siswa', 'laporan'));
    }

    public function laporanStore(Request $request)
    {
        abort_unless(auth()->user()->role === 'siswa', 403);

        $data = $request->validate([
            'judul' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'file' => 'required|file|mimes:pdf,ppt,pptx|max:20480',
        ]);

        $siswa = $this->currentSiswa();
        $lama = LaporanSidang::where('siswa_id', $siswa->id)->first();

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $path = $file->store('laporan-sidang', 'public');

        if ($lama) {
            Storage::disk('public')->delete($lama->file_path);
            $lama->update([
                'judul' => $data['judul'],
                'keterangan' => $data['keterangan'] ?? null,
                'file_path' => $path,
                'tipe_file' => $ext === 'pdf' ? 'pdf' : 'ppt',
            ]);
        } else {
            LaporanSidang::create([
                'siswa_id' => $siswa->id,
                'judul' => $data['judul'],
                'keterangan' => $data['keterangan'] ?? null,
                'file_path' => $path,
                'tipe_file' => $ext === 'pdf' ? 'pdf' : 'ppt',
            ]);
        }

        return redirect()->route('sidang.laporan')->with('success', 'Laporan PKL berhasil diupload.');
    }

    // ===== Penilaian sidang (pembimbing & panitia) =====

    public function penilaian()
    {
        abort_unless(in_array(auth()->user()->role, ['panitia', 'pembimbing'], true), 403);

        $kriteria = KriteriaSidang::with('rubriks')->orderBy('urutan')->orderBy('id')->get();

        return view('sidang.penilaian.index', [
            'kriteriaList' => $kriteria,
            'role' => auth()->user()->role,
        ]);
    }

    public function penilaianData()
    {
        abort_unless(in_array(auth()->user()->role, ['panitia', 'pembimbing'], true), 403);

        $siswaQuery = Siswa::with('kelas')->orderBy('nama_siswa');

        if (auth()->user()->role === 'pembimbing') {
            $pembimbingId = $this->pembimbingId();
            $siswaQuery->where(function ($query) use ($pembimbingId) {
                $query->whereHas('kelompokBimbingan', function ($builder) use ($pembimbingId) {
                    $builder->where(function ($kelompokQuery) use ($pembimbingId) {
                        $kelompokQuery->where('kelompok_bimbingan.pembimbing_id', $pembimbingId)
                            ->orWhereHas('pembimbings', fn ($q) => $q->where('pembimbings.id', $pembimbingId));
                    });
                })->orWhereHas('jadwalSidangPembimbing', fn ($q) => $q->where('pembimbing_id', $pembimbingId));
            });
        }

        $siswaList = $siswaQuery->get();
        $siswaIds = $siswaList->pluck('id');

        $nilai = NilaiSidang::whereIn('siswa_id', $siswaIds)->get()->groupBy('siswa_id');
        $laporan = LaporanSidang::whereIn('siswa_id', $siswaIds)->get()->keyBy('siswa_id');
        $sertifikat = SertifikatSidang::whereIn('siswa_id', $siswaIds)->get()->keyBy('siswa_id');
        $totalKriteria = KriteriaSidang::count();

        $rows = $siswaList->map(function ($siswa) use ($nilai, $laporan, $sertifikat, $totalKriteria) {
            $nilaiSiswa = $nilai->get($siswa->id, collect());
            $laporanSiswa = $laporan->get($siswa->id);

            return [
                'id' => $siswa->id,
                'nama_siswa' => $siswa->nama_siswa,
                'kelas' => optional($siswa->kelas)->nama_kelas,
                'laporan' => $laporanSiswa ? [
                    'judul' => $laporanSiswa->judul,
                    'tipe_file' => $laporanSiswa->tipe_file,
                    'url' => asset('storage/' . $laporanSiswa->file_path),
                ] : null,
                'kriteria_dinilai' => $nilaiSiswa->count(),
                'total_kriteria' => $totalKriteria,
                'nilai' => $nilaiSiswa->mapWithKeys(fn ($item) => [
                    $item->kriteria_sidang_id => ['nilai' => (float) $item->nilai, 'catatan' => $item->catatan],
                ]),
                'nilai_akhir' => $this->hitungNilaiAkhir($nilaiSiswa),
                'sertifikat' => $sertifikat->has($siswa->id),
            ];
        });

        return response()->json(['data' => $rows->values()]);
    }

    public function penilaianStore(Request $request)
    {
        abort_unless(in_array(auth()->user()->role, ['panitia', 'pembimbing'], true), 403);

        $data = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'nilai' => 'required|array|min:1',
            'nilai.*.kriteria_sidang_id' => 'required|exists:kriteria_sidangs,id',
            'nilai.*.nilai' => 'required|numeric|min:0|max:100',
            'nilai.*.catatan' => 'nullable|string',
        ]);

        $pembimbingId = auth()->user()->role === 'pembimbing' ? $this->pembimbingId() : null;

        foreach ($data['nilai'] as $item) {
            NilaiSidang::updateOrCreate(
                [
                    'siswa_id' => $data['siswa_id'],
                    'kriteria_sidang_id' => $item['kriteria_sidang_id'],
                ],
                [
                    'pembimbing_id' => $pembimbingId,
                    'nilai' => $item['nilai'],
                    'catatan' => $item['catatan'] ?? null,
                ]
            );
        }

        return response()->json(['success' => true]);
    }

    // ===== Nilai sidang (siswa) =====

    public function nilaiSiswa()
    {
        abort_unless(auth()->user()->role === 'siswa', 403);

        $siswa = $this->currentSiswa();
        $kriteria = KriteriaSidang::with('rubriks')->orderBy('urutan')->orderBy('id')->get();
        $nilai = NilaiSidang::where('siswa_id', $siswa->id)->get()->keyBy('kriteria_sidang_id');
        $sertifikat = SertifikatSidang::where('siswa_id', $siswa->id)->first();
        $nilaiAkhir = $this->hitungNilaiAkhir($nilai->values());

        return view('sidang.nilai.index', [
            'siswa' => $siswa,
            'kriteriaList' => $kriteria,
            'nilai' => $nilai,
            'nilaiAkhir' => $nilaiAkhir,
            'predikat' => $nilaiAkhir !== null ? $this->predikat($nilaiAkhir) : null,
            'sertifikat' => $sertifikat,
        ]);
    }

    // ===== Sertifikat sidang =====

    public function generateSertifikat(Request $request)
    {
        abort_unless(auth()->user()->role === 'panitia', 403);

        $data = $request->validate([
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'exists:siswa,id',
        ]);

        $totalKriteria = KriteriaSidang::count();
        if ($totalKriteria === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada kriteria sidang. Input kriteria terlebih dahulu.',
            ], 422);
        }

        $berhasil = 0;
        $dilewati = [];

        foreach ($data['siswa_ids'] as $siswaId) {
            $nilai = NilaiSidang::where('siswa_id', $siswaId)->get();

            if ($nilai->count() < $totalKriteria) {
                $dilewati[] = Siswa::find($siswaId)->nama_siswa
                    . ' (' . $nilai->count() . '/' . $totalKriteria . ' kriteria dinilai)';
                continue;
            }

            $nilaiAkhir = $this->hitungNilaiAkhir($nilai);

            SertifikatSidang::updateOrCreate(
                ['siswa_id' => $siswaId],
                [
                    'nomor_sertifikat' => 'SSD/' . Siswa::find($siswaId)->nis . '/' . now()->format('Y'),
                    'nilai_akhir' => $nilaiAkhir,
                    'predikat' => $this->predikat($nilaiAkhir),
                    'generated_at' => now(),
                    'generated_by' => auth()->id(),
                ]
            );
            $berhasil++;
        }

        $pesan = "Berhasil generate {$berhasil} sertifikat.";
        if ($dilewati) {
            $pesan .= ' Dilewati (nilai belum lengkap): ' . implode('; ', $dilewati);
        }

        return response()->json(['success' => $berhasil > 0, 'message' => $pesan], $berhasil > 0 ? 200 : 422);
    }

    public function cetakSertifikat($siswaId = null)
    {
        $data = $this->buildSertifikatData($siswaId);

        return view('sidang.sertifikat.cetak', $data);
    }

    public function downloadSertifikat($siswaId = null)
    {
        $data = $this->buildSertifikatData($siswaId);

        $pdf = Pdf::loadView('sidang.sertifikat.pdf', $data)->setPaper('A4', 'landscape');

        return $pdf->download('sertifikat-sidang-' . $data['siswa']->nis . '.pdf');
    }

    private function buildSertifikatData($siswaId): array
    {
        $user = auth()->user();

        if ($user->role === 'siswa') {
            $siswa = $this->currentSiswa();
        } else {
            abort_unless(in_array($user->role, ['panitia', 'pembimbing'], true), 403);
            abort_if(!$siswaId, 404);
            $siswa = Siswa::findOrFail($siswaId);
        }

        $siswa->load('kelas.jurusan');
        $sertifikat = SertifikatSidang::where('siswa_id', $siswa->id)->firstOrFail();

        $kriteria = KriteriaSidang::orderBy('urutan')->orderBy('id')->get();
        $nilai = NilaiSidang::where('siswa_id', $siswa->id)->get()->keyBy('kriteria_sidang_id');

        $rincian = $kriteria->map(fn ($item) => [
            'nama' => $item->nama_kriteria,
            'bobot' => $item->bobot,
            'nilai' => $nilai->has($item->id) ? (float) $nilai[$item->id]->nilai : null,
        ]);

        $sekolah = Sekolah::latest('id')->first();
        $sertifikatSetting = SertifikatSetting::current();
        $customFont = SertifikatFont::where('name', $sertifikatSetting->font_family)->latest('id')->first();
        $aspeksSertifikat = SertifikatAspek::where('jurusan_id', $siswa->kelas?->jurusan_id)->orderBy('urutan')->orderBy('id')->get();
        $nilaiSertifikat = SertifikatPenilaian::where('siswa_id', $siswa->id)->get()->keyBy('sertifikat_aspek_id');
        $relatifJikaAda = fn (?string $path) => $path && is_file(public_path($path)) ? $path : null;

        return [
            'siswa' => $siswa,
            'sertifikat' => $sertifikat,
            'rincian' => $rincian,
            'tanggalCetak' => now()->locale('id'),
            'kepalaSekolah' => optional($sekolah)->nama_kepala_sekolah ?? 'MOH. ROFIK ZEN, S.Pd., M.M.Pd.',
            'nipKepalaSekolah' => optional($sekolah)->nip_kepala_sekolah ?? '196906131994121002',
            'ttdPath' => $relatifJikaAda($sekolah?->ttd_kepala_sekolah_path ? 'storage/' . $sekolah->ttd_kepala_sekolah_path : null),
            'capPath' => $relatifJikaAda($sekolah?->cap_sekolah_path ? 'storage/' . $sekolah->cap_sekolah_path : null),
            'logoPath' => $relatifJikaAda('logo.png'),
            'fontFamily' => $sertifikatSetting->font_family,
            'backgroundDataUri' => $sertifikatSetting->backgroundDataUri(),
            'backgroundFrontDataUri' => $sertifikatSetting->backgroundFrontDataUri(),
            'backgroundBackDataUri' => $sertifikatSetting->backgroundBackDataUri(),
            'customFontDataUri' => $customFont?->dataUri() ?? $sertifikatSetting->customFontDataUri(),
            'customFontName' => $customFont?->name ?? $sertifikatSetting->custom_font_name,
            'customFontFaces' => SertifikatFont::orderBy('id')->get()->map(fn ($font) => ['name' => $font->name, 'src' => $font->dataUri()])->filter(fn ($font) => !empty($font['src']))->values(),
            'designLayout' => $sertifikatSetting->layout(),
            'assessmentRows' => $aspeksSertifikat->map(fn ($aspek) => ['aspek' => $aspek->nama_aspek, 'nilai' => $nilaiSertifikat->get($aspek->id)?->nilai, 'predikat' => $nilaiSertifikat->get($aspek->id)?->predikat]),
        ];
    }

    // ===== Helpers =====

    private function hitungNilaiAkhir($nilaiCollection): ?float
    {
        if ($nilaiCollection->isEmpty()) {
            return null;
        }

        $kriteria = KriteriaSidang::whereIn('id', $nilaiCollection->pluck('kriteria_sidang_id'))
            ->get()
            ->keyBy('id');

        $totalBobot = 0;
        $jumlah = 0;
        foreach ($nilaiCollection as $item) {
            $bobot = (int) optional($kriteria->get($item->kriteria_sidang_id))->bobot ?: 0;
            if ($bobot <= 0) {
                continue;
            }
            $jumlah += (float) $item->nilai * $bobot;
            $totalBobot += $bobot;
        }

        if ($totalBobot === 0) {
            return round((float) $nilaiCollection->avg('nilai'), 2);
        }

        return round($jumlah / $totalBobot, 2);
    }

    private function predikat(float $nilai): string
    {
        return match (true) {
            $nilai >= 90 => 'Sangat Baik (A)',
            $nilai >= 80 => 'Baik (B)',
            $nilai >= 70 => 'Cukup (C)',
            default => 'Perlu Bimbingan (D)',
        };
    }

    private function currentSiswa(): Siswa
    {
        $siswa = Siswa::where('nis', (string) auth()->user()->username)->first();
        abort_if(!$siswa, 403, 'Data siswa untuk akun ini tidak ditemukan.');

        return $siswa;
    }

    private function pembimbingId(): int
    {
        $id = Pembimbing::where('nip_pembimbing', (string) auth()->user()->username)->value('id');
        abort_if(!$id, 403, 'Data pembimbing untuk akun ini tidak ditemukan.');

        return (int) $id;
    }
}
