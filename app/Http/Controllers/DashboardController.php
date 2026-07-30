<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AbsensiPembekalan;
use App\Models\Bimbingan;
use App\Models\CekKelengkapanSiswa;
use App\Models\JawabanTugasSiswa;
use App\Models\KelompokBimbingan;
use App\Models\Materi;
use App\Models\NilaiSikapPembekalan;
use App\Models\Pembimbing;
use App\Models\TugasPembekalan;
use App\Models\Siswa;
use App\Models\SuratIzinOrtu;
use App\Models\TempatPkl;
use App\Support\WorksheetPromptExtractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->role === 'siswa') {
            return $this->renderSiswaDashboard('overview');
        }

        if ($user->role === 'pembimbing') {
            $pembimbing = Pembimbing::with('jurusan')
                ->where('nip_pembimbing', (string) $user->username)
                ->first();

            $jumlahKelompok = 0;
            $jumlahSiswaBimbingan = 0;
            $kelompok = collect();
            $bimbinganPembimbing = collect();
            $tugasSiswa = collect();
            $summaryPembimbing = [
                'total_sesi' => 0,
                'tugas_terkumpul' => 0,
                'belum_dinilai' => 0,
                'hadir' => 0,
                'total_cek_kelengkapan' => 0,
                'lengkap' => 0,
                'belum_lengkap' => 0,
            ];
            $kelengkapanTerbaru = collect();

            if ($pembimbing) {
                $jumlahKelompok = KelompokBimbingan::where('pembimbing_id', $pembimbing->id)->count();

                $siswaBimbinganIds = DB::table('kelompok_bimbingan_siswa as kbs')
                    ->join('kelompok_bimbingan as kb', 'kb.id', '=', 'kbs.kelompok_bimbingan_id')
                    ->where('kb.pembimbing_id', $pembimbing->id)
                    ->distinct()
                    ->pluck('kbs.siswa_id');

                $jumlahSiswaBimbingan = $siswaBimbinganIds->count();

                $kelompok = KelompokBimbingan::with('siswa.kelas', 'siswa.suratIzin.perusahaan')
                    ->withCount('siswa')
                    ->where('pembimbing_id', $pembimbing->id)
                    ->orderBy('nama_kelompok')
                    ->get();

                if ($siswaBimbinganIds->isNotEmpty()) {
                    $bimbinganPembimbing = Bimbingan::with('siswa.kelas')
                        ->whereIn('siswa_id', $siswaBimbinganIds)
                        ->orderByDesc('tanggal_bimbingan')
                        ->orderByDesc('id')
                        ->get();

                    $absensiPembekalanPembimbing = AbsensiPembekalan::query()
                        ->where('pembimbing_id', $pembimbing->id)
                        ->whereIn('siswa_id', $siswaBimbinganIds)
                        ->get();

                    $cekKelengkapanPembimbing = CekKelengkapanSiswa::with(['siswa.kelas'])
                        ->where('pembimbing_id', $pembimbing->id)
                        ->whereIn('siswa_id', $siswaBimbinganIds)
                        ->orderByDesc('tanggal_cek')
                        ->orderByDesc('id')
                        ->get();

                    $tugasSiswa = $bimbinganPembimbing
                        ->filter(fn($item) => !empty($item->tugas))
                        ->values();

                    $kelengkapanTerbaru = $cekKelengkapanPembimbing
                        ->groupBy('siswa_id')
                        ->map(function ($records) {
                            $latest = $records->sortByDesc(fn($item) => optional($item->tanggal_cek)?->format('Y-m-d') . '-' . $item->id)->first();
                            $missingItems = collect($latest?->item_checks ?? [])->filter(fn($check) => empty($check['is_checked']))->values();

                            return (object) [
                                'record' => $latest,
                                'missing_count' => $missingItems->count(),
                                'missing_names' => $missingItems->pluck('nama_item')->take(3)->join(', '),
                            ];
                        })
                        ->sortByDesc('missing_count')
                        ->values()
                        ->take(6);

                    $summaryPembimbing = [
                        'total_sesi' => $bimbinganPembimbing->count(),
                        'tugas_terkumpul' => $tugasSiswa->filter(fn($item) => !empty($item->tugas_siswa))->count(),
                        'belum_dinilai' => $tugasSiswa->whereNull('nilai_tugas')->count(),
                        'hadir' => $absensiPembekalanPembimbing->where('status', 'hadir')->count(),
                        'total_cek_kelengkapan' => $cekKelengkapanPembimbing->count(),
                        'lengkap' => $cekKelengkapanPembimbing->where('is_lengkap', true)->count(),
                        'belum_lengkap' => $cekKelengkapanPembimbing->where('is_lengkap', false)->count(),
                    ];
                }
            }

            return view('dashboard_pembimbing', compact(
                'pembimbing',
                'jumlahKelompok',
                'jumlahSiswaBimbingan',
                'kelompok',
                'bimbinganPembimbing',
                'tugasSiswa',
                'summaryPembimbing',
                'kelengkapanTerbaru'
            ));
        }

        return view('dashboard');
    }

    public function siswaAbsensi()
    {
        return $this->renderSiswaDashboard('absensi');
    }

    public function siswaMateri(Request $request)
    {
        $request->session()->put('siswa_materi_seen', true);

        return $this->renderSiswaDashboard('materi');
    }

    public function siswaMateriDetail(Request $request, Materi $materi)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'siswa') {
            abort(403);
        }

        $request->session()->put('siswa_materi_seen', true);

        return view('siswa.materi_detail', compact('materi'));
    }

    public function siswaTugas()
    {
        if (!session('siswa_materi_seen', false)) {
            return redirect()->route('dashboard.siswa.materi')
                ->with('error', 'Silakan lihat materi pembekalan terlebih dahulu sebelum mengerjakan tugas.');
        }

        return $this->renderSiswaDashboard('tugas');
    }

    public function siswaNilai()
    {
        return $this->renderSiswaDashboard('nilai');
    }

    public function siswaSikap()
    {
        return $this->renderSiswaDashboard('sikap');
    }

    private function renderSiswaDashboard(string $activeSection)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'siswa') {
            abort(403);
        }

        $siswa = Siswa::with('kelas.jurusan')
            ->where('nis', (string) $user->username)
            ->first();

        $hasSuratIzin = false;
        $hasTempatPkl = false;
        $suratIzin = null;
        $tempatPkl = null;
        $pembimbing = null;
        $pembimbingPerusahaan = null;
        $absensiPembekalan = collect();
        $bimbingan = collect();
        $nilaiSikapPembekalan = collect();
        $summary = [
            'total_sesi' => 0,
            'hadir' => 0,
            'izin' => 0,
            'alpa' => 0,
            'total_tugas' => 0,
            'tugas_selesai' => 0,
            'avg_nilai' => null,
            'latest_sikap' => null,
            'progres' => 0,
        ];
        $chartLabels = [];
        $chartProgres = [];
        $materi = Materi::with([
            'tugasPembekalan',
            'tugasPembekalan.jawabanSiswa' => fn($q) => $q->where('siswa_id', (int) ($siswa?->id ?? 0)),
            'tugasPembekalan.jawabanSiswa.nilaiTugas',
        ])
            ->latest('tanggal_materi')->latest('id')->get();

        $siswaId = $siswa?->id;
        $tugasPembekalan = TugasPembekalan::with([
            'materi',
            'jawabanSiswa' => fn($q) => $q->where('siswa_id', (int) ($siswaId ?? 0)),
            'jawabanSiswa.nilaiTugas',
        ])->latest('tanggal_tugas')->get();

        if ($siswa) {
            $suratIzin = SuratIzinOrtu::where('siswa_id', $siswa->id)
                ->latest('id')
                ->first();
            $hasSuratIzin = $suratIzin !== null;
            $tempatPkl = TempatPkl::with(['perusahaan', 'pembimbing', 'pembimbingPerusahaan'])
                ->where('siswa_id', $siswa->id)
                ->latest('id')
                ->first();
            $hasTempatPkl = $tempatPkl !== null;
            if ($tempatPkl) {
                $pembimbing = $tempatPkl->pembimbing;
                $pembimbingPerusahaan = $tempatPkl->pembimbingPerusahaan;
            }

            if (!$pembimbing) {
                $kelompokBimbingan = KelompokBimbingan::with(['pembimbing', 'pembimbings'])
                    ->whereHas('siswa', fn($query) => $query->where('siswa.id', $siswa->id))
                    ->latest('id')
                    ->first();

                if ($kelompokBimbingan) {
                    $pembimbing = $kelompokBimbingan->pembimbing
                        ?? $kelompokBimbingan->pembimbings->sortBy('nama_pembimbing')->first();
                }
            }

            if (!$pembimbing) {
                $pembimbing = $siswa->pembimbingBimbingan()
                    ->orderBy('nama_pembimbing')
                    ->first();
            }

            $absensiPembekalan = AbsensiPembekalan::with('pembimbing')
                ->where('siswa_id', $siswa->id)
                ->orderByDesc('tanggal_absensi')
                ->orderByDesc('id')
                ->get();

            $bimbingan = Bimbingan::with('pembimbing')
                ->where('siswa_id', $siswa->id)
                ->orderByDesc('tanggal_bimbingan')
                ->orderByDesc('id')
                ->get();

            $nilaiSikapPembekalan = NilaiSikapPembekalan::with([
                'pembimbing',
                'materi.tugasPembekalan',
                'materi.tugasPembekalan.jawabanSiswa' => fn($q) => $q->where('siswa_id', $siswa->id),
            ])
                ->where('siswa_id', $siswa->id)
                ->orderByDesc('tanggal_penilaian')
                ->orderByDesc('id')
                ->get();

            $totalSesi = $absensiPembekalan->count();
            $hadir = $absensiPembekalan->where('status', 'hadir')->count();
            $izin = $absensiPembekalan->where('status', 'izin')->count();
            $alpa = $absensiPembekalan->where('status', 'alpa')->count();
            $totalTugas = $tugasPembekalan->count();
            $tugasSelesai = $tugasPembekalan->filter(function ($item) {
                $jawaban = $item->jawabanSiswa->first();

                return !empty($jawaban?->submitted_at);
            })->count();
            $avgNilai = $tugasPembekalan
                ->map(fn($item) => $item->jawabanSiswa->first()?->nilaiTugas?->nilai)
                ->filter(fn($nilai) => $nilai !== null)
                ->avg();
            $latestSikap = $nilaiSikapPembekalan->first()?->nilai_sikap;
            $progres = $totalSesi > 0 ? (int) round(($hadir / $totalSesi) * 100) : 0;

            $summary = [
                'total_sesi' => $totalSesi,
                'hadir' => $hadir,
                'izin' => $izin,
                'alpa' => $alpa,
                'total_tugas' => $totalTugas,
                'tugas_selesai' => $tugasSelesai,
                'avg_nilai' => $avgNilai !== null ? round((float) $avgNilai, 2) : null,
                'latest_sikap' => $latestSikap,
                'progres' => $progres,
            ];

            $timeline = $absensiPembekalan->sortBy('tanggal_absensi')->values();
            $runningTotal = 0;
            $runningHadir = 0;

            foreach ($timeline as $entry) {
                $runningTotal++;
                if ($entry->status === 'hadir') {
                    $runningHadir++;
                }

                $chartLabels[] = $entry->tanggal_absensi
                    ? \Carbon\Carbon::parse($entry->tanggal_absensi)->format('d M')
                    : 'Sesi ' . $runningTotal;
                $chartProgres[] = (int) round(($runningHadir / $runningTotal) * 100);
            }
        }

        return view('dashboard_siswa', compact(
            'siswa',
            'hasSuratIzin',
            'hasTempatPkl',
            'suratIzin',
            'tempatPkl',
            'pembimbing',
            'pembimbingPerusahaan',
            'absensiPembekalan',
            'bimbingan',
            'nilaiSikapPembekalan',
            'materi',
            'tugasPembekalan',
            'summary',
            'chartLabels',
            'chartProgres',
            'activeSection'
        ));
    }

    public function siswaKerjakanTugas(Request $request)
    {
        if (!session('siswa_materi_seen', false)) {
            return redirect()->route('dashboard.siswa.materi')
                ->with('error', 'Silakan lihat materi pembekalan terlebih dahulu sebelum mengerjakan tugas.');
        }

        $user = auth()->user();
        if (!$user || $user->role !== 'siswa') {
            abort(403);
        }

        $siswa = Siswa::with('kelas.jurusan')
            ->where('nis', (string) $user->username)
            ->first();

        if (!$siswa) {
            return redirect()->route('dashboard')->with('error', 'Data siswa tidak ditemukan.');
        }

        $tugasList = TugasPembekalan::with([
            'materi',
            'jawabanSiswa' => fn($q) => $q->where('siswa_id', $siswa->id),
            'jawabanSiswa.nilaiTugas',
        ])->latest('tanggal_tugas')->get();

        $tugasList->each(function (TugasPembekalan $tugas) {
            $storedPrompts = is_array($tugas->soal_parsed_prompts) ? $tugas->soal_parsed_prompts : [];
            $worksheetPrompts = !empty($storedPrompts)
                ? $storedPrompts
                : $this->buildWorksheetPromptsFromTask($tugas);
            $tugas->setAttribute('worksheet_prompts', $worksheetPrompts);
            $tugas->setAttribute('worksheet_source', !empty($storedPrompts) ? 'stored' : 'fallback');
        });

        return view('siswa.kerjakan_tugas', compact('siswa', 'tugasList'));
    }

    public function siswaKerjakanTugasStore(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'siswa') {
            abort(403);
        }

        $siswa = Siswa::where('nis', (string) $user->username)->first();
        if (!$siswa) {
            return redirect()->route('dashboard')->with('error', 'Data siswa tidak ditemukan.');
        }

        $answers = $request->input('jawaban', []);
        $savedCount = 0;
        $skippedCount = 0;

        foreach ($answers as $tugasId => $jawabanText) {
            $sanitizedJawaban = $this->sanitizeJawabanHtml((string) $jawabanText);
            if ($this->isJawabanEmpty($sanitizedJawaban)) {
                continue;
            }

            $tugas = TugasPembekalan::find((int) $tugasId);
            if (!$tugas) {
                continue;
            }

            // Check if deadline has passed
            if ($tugas->deadline && \Carbon\Carbon::parse($tugas->deadline)->isPast()) {
                $skippedCount++;
                continue;
            }

            JawabanTugasSiswa::updateOrCreate(
                ['tugas_pembekalan_id' => $tugas->id, 'siswa_id' => $siswa->id],
                ['jawaban_text' => $sanitizedJawaban, 'submitted_at' => now()]
            );
            $savedCount++;
        }

        $message = $savedCount > 0 ? "$savedCount jawaban berhasil disimpan." : 'Tidak ada jawaban yang disimpan.';
        if ($skippedCount > 0) {
            $message .= " $skippedCount tugas melewati deadline dan tidak disimpan.";
        }

        return redirect()->route('dashboard.siswa.kerjakan-tugas')
            ->with('success', $message);
    }

    public function submitTugas(Request $request, $id)
    {
        $request->validate([
            'tugas_siswa' => ['required', 'string'],
        ]);

        $user = auth()->user();
        if (!$user || $user->role !== 'siswa') {
            abort(403);
        }

        $siswa = Siswa::where('nis', (string) $user->username)->first();
        if (!$siswa) {
            return redirect()->route('dashboard')->with('error', 'Data siswa tidak ditemukan.');
        }

        $bimbingan = Bimbingan::where('id', $id)
            ->where('siswa_id', $siswa->id)
            ->firstOrFail();

        $bimbingan->update([
            'tugas_siswa' => $request->tugas_siswa,
        ]);

        return redirect()->route('dashboard.siswa.kerjakan-tugas')->with('success', 'Jawaban berhasil disimpan.');
    }

    public function cetakSertifikatPembekalan()
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'siswa') {
            abort(403);
        }

        $siswa = Siswa::with('kelas.jurusan')
            ->where('nis', (string) $user->username)
            ->first();

        if (!$siswa) {
            return redirect()->route('dashboard')->with('error', 'Data siswa tidak ditemukan.');
        }

        $bimbingan = Bimbingan::where('siswa_id', $siswa->id)->get();
        $totalSesi = $bimbingan->count();
        $hadir = $bimbingan->where('status_absensi', 'hadir')->count();
        $progres = $totalSesi > 0 ? (int) round(($hadir / $totalSesi) * 100) : 0;

        $data = [
            'siswa' => $siswa,
            'totalSesi' => $totalSesi,
            'hadir' => $hadir,
            'progres' => $progres,
            'tanggalCetak' => now(),
        ];

        return view('siswa.sertifikat_pembekalan', $data);
    }

    public function downloadSertifikatPembekalan()
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'siswa') {
            abort(403);
        }

        $siswa = Siswa::with('kelas.jurusan')
            ->where('nis', (string) $user->username)
            ->first();

        if (!$siswa) {
            return redirect()->route('dashboard')->with('error', 'Data siswa tidak ditemukan.');
        }

        $bimbingan = Bimbingan::where('siswa_id', $siswa->id)->get();
        $totalSesi = $bimbingan->count();
        $hadir = $bimbingan->where('status_absensi', 'hadir')->count();
        $progres = $totalSesi > 0 ? (int) round(($hadir / $totalSesi) * 100) : 0;

        $data = [
            'siswa' => $siswa,
            'totalSesi' => $totalSesi,
            'hadir' => $hadir,
            'progres' => $progres,
            'tanggalCetak' => now(),
        ];

        $pdf = Pdf::loadView('siswa.sertifikat_pembekalan_pdf', $data)->setPaper('A4', 'landscape');

        $filename = 'sertifikat-pembekalan-' . ($siswa->nis ?? 'siswa') . '.pdf';

        return $pdf->download($filename);
    }

    public function updateNilaiTugasPembimbing(Request $request, $id)
    {
        $request->validate([
            'nilai_tugas' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $pembimbing = $this->getPembimbingFromAuth();
        if (!$pembimbing) {
            abort(403);
        }

        $bimbingan = Bimbingan::findOrFail($id);
        $this->authorizePembimbingToUpdateBimbingan($pembimbing, $bimbingan);

        $bimbingan->update([
            'nilai_tugas' => (float) $request->nilai_tugas,
        ]);

        return redirect()->to(route('dashboard') . '#tugas-siswa-pembimbing')
            ->with('success', 'Nilai tugas siswa berhasil disimpan.');
    }

    public function updateEvaluasiSiswaPembimbing(Request $request, $id)
    {
        $request->validate([
            'status_absensi' => ['required', 'in:hadir,izin,alpa'],
            'penilaian_sikap' => ['nullable', 'in:sangat_baik,baik,cukup,kurang'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $pembimbing = $this->getPembimbingFromAuth();
        if (!$pembimbing) {
            abort(403);
        }

        $bimbingan = Bimbingan::findOrFail($id);
        $this->authorizePembimbingToUpdateBimbingan($pembimbing, $bimbingan);

        $bimbingan->update([
            'status_absensi' => $request->status_absensi,
            'penilaian_sikap' => $request->penilaian_sikap,
            'catatan' => $request->catatan,
            'pembimbing_id' => $bimbingan->pembimbing_id ?: $pembimbing->id,
        ]);

        return redirect()->to(route('dashboard') . '#evaluasi-siswa-pembimbing')
            ->with('success', 'Absensi dan catatan sikap siswa berhasil disimpan.');
    }

    private function sanitizeJawabanHtml(string $html): string
    {
        // Drop script/style blocks and event-handler attributes, keep structural tags like table.
        $cleaned = preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/is', '', $html) ?? '';
        $cleaned = preg_replace('/\s+on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $cleaned) ?? '';
        $cleaned = preg_replace('/\s+style\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $cleaned) ?? '';

        $allowedTags = '<p><br><b><strong><i><em><u><ul><ol><li><table><thead><tbody><tfoot><tr><th><td><div><span>';
        return trim(strip_tags($cleaned, $allowedTags));
    }

    private function buildWorksheetPromptsFromTask(TugasPembekalan $tugas): array
    {
        return app(WorksheetPromptExtractor::class)->extractFromTaskSources(
            (array) ($tugas->soal_files ?? []),
            (array) ($tugas->soal_essay ?? [])
        );
    }

    private function isJawabanEmpty(string $html): bool
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim($text) === '';
    }

    private function getPembimbingFromAuth(): ?Pembimbing
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'pembimbing') {
            return null;
        }

        return Pembimbing::where('nip_pembimbing', (string) $user->username)->first();
    }

    private function authorizePembimbingToUpdateBimbingan(Pembimbing $pembimbing, Bimbingan $bimbingan): void
    {
        $isOwnSession = (int) $bimbingan->pembimbing_id === (int) $pembimbing->id;

        $isSiswaInKelompok = DB::table('kelompok_bimbingan_siswa as kbs')
            ->join('kelompok_bimbingan as kb', 'kb.id', '=', 'kbs.kelompok_bimbingan_id')
            ->where('kb.pembimbing_id', $pembimbing->id)
            ->where('kbs.siswa_id', $bimbingan->siswa_id)
            ->exists();

        if (!$isOwnSession && !$isSiswaInKelompok) {
            abort(403);
        }
    }
}
