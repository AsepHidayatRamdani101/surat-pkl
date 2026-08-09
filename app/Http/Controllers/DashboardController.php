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
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DashboardController extends Controller
{
    public function index(Request $request)
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
                'avg_nilai_tugas' => null,
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

                    $jawabanTugasPembimbing = JawabanTugasSiswa::with(['nilaiTugas'])
                        ->whereIn('siswa_id', $siswaBimbinganIds)
                        ->get();

                    $tugasSiswa = $jawabanTugasPembimbing
                        ->filter(fn($item) => !empty($item->submitted_at))
                        ->values();

                    $hadirLengkapSiswaPerHari = $absensiPembekalanPembimbing
                        ->groupBy(function ($item) {
                            return $item->siswa_id . '|' . $item->tanggal_absensi;
                        })
                        ->filter(function ($group) {
                            $statusDatang = optional($group->firstWhere('sesi_absensi', 'datang'))->status;
                            $statusPulang = optional($group->firstWhere('sesi_absensi', 'pulang'))->status;

                            return $statusDatang === 'hadir' && $statusPulang === 'hadir';
                        })
                        ->count();

                    $avgNilaiTugasPembimbing = $jawabanTugasPembimbing
                        ->map(fn($jawaban) => $jawaban->nilaiTugas?->nilai)
                        ->filter(fn($nilai) => $nilai !== null)
                        ->avg();

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
                        'tugas_terkumpul' => $tugasSiswa->count(),
                        'belum_dinilai' => $tugasSiswa->filter(fn($item) => $item->nilaiTugas === null)->count(),
                        'hadir' => $hadirLengkapSiswaPerHari,
                        'avg_nilai_tugas' => $avgNilaiTugasPembimbing !== null ? round((float) $avgNilaiTugasPembimbing, 2) : null,
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

        $topGuru = collect();
        $guruWeights = [
            'absensi' => 25,
            'sikap' => 25,
            'kelengkapan' => 25,
            'nilai' => 25,
        ];
        $guruTotalBobot = 100;
        $guruTanggalAwal = null;
        $guruTanggalAkhir = null;
        $guruRankingQueryParams = [];

        if ($user->role === 'panitia') {
            $ranking = $this->buildTopGuruPanitiaRanking($request);
            $topGuru = $ranking['topGuru'];
            $guruWeights = $ranking['weights'];
            $guruTotalBobot = $ranking['totalWeight'];
            $guruTanggalAwal = $ranking['tanggal_awal'];
            $guruTanggalAkhir = $ranking['tanggal_akhir'];
            $guruRankingQueryParams = [
                'guru_tanggal_awal' => $guruTanggalAwal,
                'guru_tanggal_akhir' => $guruTanggalAkhir,
                'bobot_absensi' => $guruWeights['absensi'],
                'bobot_sikap' => $guruWeights['sikap'],
                'bobot_kelengkapan' => $guruWeights['kelengkapan'],
                'bobot_nilai' => $guruWeights['nilai'],
            ];
        }

        return view('dashboard', compact(
            'topGuru',
            'guruWeights',
            'guruTotalBobot',
            'guruTanggalAwal',
            'guruTanggalAkhir',
            'guruRankingQueryParams'
        ));
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

            $dailyAbsensi = $absensiPembekalan
                ->groupBy(fn($entry) => (string) $entry->tanggal_absensi)
                ->sortKeys();

            $totalSesi = $dailyAbsensi->count();
            $hadir = $dailyAbsensi->filter(function ($entries) {
                $statusDatang = optional($entries->firstWhere('sesi_absensi', 'datang'))->status;
                $statusPulang = optional($entries->firstWhere('sesi_absensi', 'pulang'))->status;

                return $statusDatang === 'hadir' && $statusPulang === 'hadir';
            })->count();

            $izin = $dailyAbsensi->filter(function ($entries) {
                $statuses = $entries->pluck('status')->filter()->all();

                return in_array('izin', $statuses, true);
            })->count();

            $alpa = $dailyAbsensi->filter(function ($entries) {
                $statuses = $entries->pluck('status')->filter()->all();

                return in_array('alpa', $statuses, true);
            })->count();
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

            $timeline = $dailyAbsensi;
            $runningTotal = 0;
            $runningHadir = 0;

            foreach ($timeline as $entry) {
                $runningTotal++;
                $statusDatang = optional($entry->firstWhere('sesi_absensi', 'datang'))->status;
                $statusPulang = optional($entry->firstWhere('sesi_absensi', 'pulang'))->status;

                if ($statusDatang === 'hadir' && $statusPulang === 'hadir') {
                    $runningHadir++;
                }

                $firstEntry = $entry->first();

                $chartLabels[] = $firstEntry?->tanggal_absensi
                    ? \Carbon\Carbon::parse($firstEntry->tanggal_absensi)->format('d M')
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

    public function exportTopGuruPanitiaExcel(Request $request)
    {
        $this->ensurePanitiaRole();

        $ranking = $this->buildTopGuruPanitiaRanking($request);
        $topGuru = $ranking['topGuru'];
        $weights = $ranking['weights'];
        $totalWeight = $ranking['totalWeight'];
        $tanggalAwal = $ranking['tanggal_awal'];
        $tanggalAkhir = $ranking['tanggal_akhir'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Top Guru Panitia');

        $sheet->setCellValue('A1', 'Top 10 Guru Terbaik - Dashboard Panitia');
        $sheet->setCellValue('A2', 'Periode: ' . ($tanggalAwal ?: '-') . ' s/d ' . ($tanggalAkhir ?: '-'));
        $sheet->setCellValue(
            'A3',
            'Bobot (Total ' . round($totalWeight, 2) . '): Absensi=' . $weights['absensi'] . ', Sikap=' . $weights['sikap'] . ', Kelengkapan=' . $weights['kelengkapan'] . ', Nilai=' . $weights['nilai'],
        );

        $headers = [
            'No',
            'Nama Guru',
            'NIP',
            'Skor Akhir',
            'Skor Absensi',
            'Skor Sikap',
            'Skor Kelengkapan',
            'Skor Nilai',
            'Siswa Bimbingan',
            'Absensi Lengkap',
            'Sikap Siswa-Hari',
            'Kelengkapan Siswa-Hari',
            'Nilai Terisi',
        ];

        $startRow = 5;
        $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M'];
        foreach ($headers as $index => $header) {
            $sheet->setCellValue($columns[$index] . $startRow, $header);
        }

        $rowNumber = $startRow + 1;
        foreach ($topGuru as $index => $guru) {
            $sheet->setCellValue('A' . $rowNumber, $index + 1);
            $sheet->setCellValue('B' . $rowNumber, $guru->nama_pembimbing);
            $sheet->setCellValue('C' . $rowNumber, $guru->nip_pembimbing ?? '-');
            $sheet->setCellValue('D' . $rowNumber, $guru->skor_akhir);
            $sheet->setCellValue('E' . $rowNumber, $guru->score_absensi);
            $sheet->setCellValue('F' . $rowNumber, $guru->score_sikap);
            $sheet->setCellValue('G' . $rowNumber, $guru->score_kelengkapan);
            $sheet->setCellValue('H' . $rowNumber, $guru->score_nilai);
            $sheet->setCellValue('I' . $rowNumber, $guru->total_siswa_bimbingan);
            $sheet->setCellValue('J' . $rowNumber, $guru->absensi_hari_lengkap . '/' . $guru->absensi_hari_total);
            $sheet->setCellValue('K' . $rowNumber, $guru->sikap_siswa_hari_tercatat . '/' . $guru->sikap_siswa_hari_target);
            $sheet->setCellValue('L' . $rowNumber, $guru->kelengkapan_siswa_hari_tercatat . '/' . $guru->kelengkapan_siswa_hari_target);
            $sheet->setCellValue('M' . $rowNumber, $guru->nilai_tugas_terisi . '/' . $guru->nilai_tugas_submitted);
            $rowNumber++;
        }

        foreach (range('A', 'M') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'top-10-guru-panitia-' . now()->format('Ymd-His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function exportTopGuruPanitiaPdf(Request $request)
    {
        $this->ensurePanitiaRole();

        $ranking = $this->buildTopGuruPanitiaRanking($request);
        $filename = 'top-10-guru-panitia-' . now()->format('Ymd-His') . '.pdf';

        $pdf = Pdf::loadView('exports.top_guru_panitia_pdf', [
            'topGuru' => $ranking['topGuru'],
            'weights' => $ranking['weights'],
            'totalWeight' => $ranking['totalWeight'],
            'tanggalAwal' => $ranking['tanggal_awal'],
            'tanggalAkhir' => $ranking['tanggal_akhir'],
            'generatedAt' => now(),
        ])->setPaper('A4', 'landscape');

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

    private function ensurePanitiaRole(): void
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'panitia') {
            abort(403);
        }
    }

    private function buildTopGuruPanitiaRanking(Request $request): array
    {
        $validated = $this->validateTopGuruFilterInput($request);
        $tanggalAwal = $validated['guru_tanggal_awal'];
        $tanggalAkhir = $validated['guru_tanggal_akhir'];
        $weights = $validated['weights'];

        $totalWeight = array_sum($weights);
        if ($totalWeight <= 0) {
            $weights = [
                'absensi' => 25,
                'sikap' => 25,
                'kelengkapan' => 25,
                'nilai' => 25,
            ];
            $totalWeight = array_sum($weights);
        }

        $hasKelompokPembimbingPivot = Schema::hasTable('kelompok_bimbingan_pembimbing');

        $buildAssignedPairsQuery = function () use ($hasKelompokPembimbingPivot) {
            $baseQuery = DB::table('kelompok_bimbingan_siswa as kbs')
                ->join('kelompok_bimbingan as kb', 'kb.id', '=', 'kbs.kelompok_bimbingan_id')
                ->whereNotNull('kb.pembimbing_id')
                ->selectRaw('kb.pembimbing_id as pembimbing_id, kbs.siswa_id');

            if ($hasKelompokPembimbingPivot) {
                $pivotQuery = DB::table('kelompok_bimbingan_siswa as kbs')
                    ->join('kelompok_bimbingan_pembimbing as kbp', 'kbp.kelompok_bimbingan_id', '=', 'kbs.kelompok_bimbingan_id')
                    ->selectRaw('kbp.pembimbing_id as pembimbing_id, kbs.siswa_id');

                $baseQuery->union($pivotQuery);
            }

            return $baseQuery;
        };

        $assignedSiswaRows = DB::query()
            ->fromSub($buildAssignedPairsQuery(), 'assigned_pairs')
            ->select('pembimbing_id', 'siswa_id')
            ->distinct()
            ->get();

        $assignedSiswaCountByPembimbing = $assignedSiswaRows
            ->groupBy('pembimbing_id')
            ->map(fn($rows) => $rows->pluck('siswa_id')->unique()->count());

        $absensiCompletenessRows = DB::query()
            ->fromSub(
                DB::table('absensi_pembekalans')
                    ->selectRaw("pembimbing_id, siswa_id, tanggal_absensi,
                        MAX(CASE WHEN sesi_absensi = 'datang' THEN 1 ELSE 0 END) as has_datang,
                        MAX(CASE WHEN sesi_absensi = 'pulang' THEN 1 ELSE 0 END) as has_pulang")
                    ->when(!empty($tanggalAwal), function ($query) use ($tanggalAwal) {
                        $query->whereDate('tanggal_absensi', '>=', $tanggalAwal);
                    })
                    ->when(!empty($tanggalAkhir), function ($query) use ($tanggalAkhir) {
                        $query->whereDate('tanggal_absensi', '<=', $tanggalAkhir);
                    })
                    ->groupBy('pembimbing_id', 'siswa_id', 'tanggal_absensi'),
                'absensi_harian'
            )
            ->selectRaw('pembimbing_id, COUNT(*) as total_hari, SUM(CASE WHEN has_datang = 1 AND has_pulang = 1 THEN 1 ELSE 0 END) as hari_lengkap')
            ->groupBy('pembimbing_id')
            ->get()
            ->keyBy('pembimbing_id');

        $sikapSiswaPerHariByPembimbing = DB::query()
            ->fromSub(
                DB::table('nilai_sikap_pembekalans')
                    ->when(!empty($tanggalAwal), function ($query) use ($tanggalAwal) {
                        $query->whereDate('tanggal_penilaian', '>=', $tanggalAwal);
                    })
                    ->when(!empty($tanggalAkhir), function ($query) use ($tanggalAkhir) {
                        $query->whereDate('tanggal_penilaian', '<=', $tanggalAkhir);
                    })
                    ->selectRaw('pembimbing_id, tanggal_penilaian, COUNT(DISTINCT siswa_id) as jumlah_siswa_harian')
                    ->groupBy('pembimbing_id', 'tanggal_penilaian'),
                'sikap_harian'
            )
            ->selectRaw('pembimbing_id, COUNT(*) as total_hari, SUM(jumlah_siswa_harian) as total_siswa_hari')
            ->groupBy('pembimbing_id')
            ->get()
            ->keyBy('pembimbing_id');

        $kelengkapanSiswaPerHariByPembimbing = DB::query()
            ->fromSub(
                DB::table('cek_kelengkapan_siswas')
                    ->when(!empty($tanggalAwal), function ($query) use ($tanggalAwal) {
                        $query->whereDate('tanggal_cek', '>=', $tanggalAwal);
                    })
                    ->when(!empty($tanggalAkhir), function ($query) use ($tanggalAkhir) {
                        $query->whereDate('tanggal_cek', '<=', $tanggalAkhir);
                    })
                    ->selectRaw('pembimbing_id, tanggal_cek, COUNT(DISTINCT siswa_id) as jumlah_siswa_harian')
                    ->groupBy('pembimbing_id', 'tanggal_cek'),
                'kelengkapan_harian'
            )
            ->selectRaw('pembimbing_id, COUNT(*) as total_hari, SUM(jumlah_siswa_harian) as total_siswa_hari')
            ->groupBy('pembimbing_id')
            ->get()
            ->keyBy('pembimbing_id');

        $submittedTugasByPembimbing = DB::query()
            ->fromSub($buildAssignedPairsQuery(), 'assigned_pairs')
            ->join('jawaban_tugas_siswas as jts', function ($join) {
                $join->on('jts.siswa_id', '=', 'assigned_pairs.siswa_id')
                    ->whereNotNull('jts.submitted_at');
            })
            ->when(!empty($tanggalAwal), function ($query) use ($tanggalAwal) {
                $query->whereDate('jts.submitted_at', '>=', $tanggalAwal);
            })
            ->when(!empty($tanggalAkhir), function ($query) use ($tanggalAkhir) {
                $query->whereDate('jts.submitted_at', '<=', $tanggalAkhir);
            })
            ->selectRaw('assigned_pairs.pembimbing_id, COUNT(DISTINCT jts.id) as total_submitted')
            ->groupBy('assigned_pairs.pembimbing_id')
            ->get()
            ->keyBy('pembimbing_id');

        $gradedTugasByPembimbing = DB::query()
            ->fromSub($buildAssignedPairsQuery(), 'assigned_pairs')
            ->join('jawaban_tugas_siswas as jts', function ($join) {
                $join->on('jts.siswa_id', '=', 'assigned_pairs.siswa_id')
                    ->whereNotNull('jts.submitted_at');
            })
            ->join('nilai_tugas_pembekalans as ntp', function ($join) {
                $join->on('ntp.jawaban_tugas_siswa_id', '=', 'jts.id')
                    ->on('ntp.pembimbing_id', '=', 'assigned_pairs.pembimbing_id');
            })
            ->when(!empty($tanggalAwal), function ($query) use ($tanggalAwal) {
                $query->whereDate('jts.submitted_at', '>=', $tanggalAwal)
                    ->whereRaw('DATE(COALESCE(ntp.dinilai_at, ntp.created_at)) >= ?', [$tanggalAwal]);
            })
            ->when(!empty($tanggalAkhir), function ($query) use ($tanggalAkhir) {
                $query->whereDate('jts.submitted_at', '<=', $tanggalAkhir)
                    ->whereRaw('DATE(COALESCE(ntp.dinilai_at, ntp.created_at)) <= ?', [$tanggalAkhir]);
            })
            ->selectRaw('assigned_pairs.pembimbing_id, COUNT(DISTINCT ntp.jawaban_tugas_siswa_id) as total_graded')
            ->groupBy('assigned_pairs.pembimbing_id')
            ->get()
            ->keyBy('pembimbing_id');

        $topGuru = Pembimbing::query()
            ->orderBy('nama_pembimbing')
            ->get(['id', 'nama_pembimbing', 'nip_pembimbing'])
            ->map(function ($pembimbing) use (
                $assignedSiswaCountByPembimbing,
                $absensiCompletenessRows,
                $sikapSiswaPerHariByPembimbing,
                $kelengkapanSiswaPerHariByPembimbing,
                $submittedTugasByPembimbing,
                $gradedTugasByPembimbing,
                $weights,
                $totalWeight
            ) {
                $pembimbingId = (int) $pembimbing->id;
                $totalSiswaBimbingan = (int) ($assignedSiswaCountByPembimbing[$pembimbingId] ?? 0);

                $absensiRow = $absensiCompletenessRows->get($pembimbingId);
                $totalHariAbsensi = (int) ($absensiRow->total_hari ?? 0);
                $hariAbsensiLengkap = (int) ($absensiRow->hari_lengkap ?? 0);
                $absensiScore = $totalHariAbsensi > 0 ? ($hariAbsensiLengkap / $totalHariAbsensi) * 100 : 0;

                $sikapRow = $sikapSiswaPerHariByPembimbing->get($pembimbingId);
                $sikapTotalHari = (int) ($sikapRow->total_hari ?? 0);
                $sikapSiswaHariTercatat = (int) ($sikapRow->total_siswa_hari ?? 0);
                $sikapSiswaHariTarget = $totalSiswaBimbingan > 0 ? $totalSiswaBimbingan * $sikapTotalHari : 0;
                $sikapScore = $sikapSiswaHariTarget > 0 ? ($sikapSiswaHariTercatat / $sikapSiswaHariTarget) * 100 : 0;

                $kelengkapanRow = $kelengkapanSiswaPerHariByPembimbing->get($pembimbingId);
                $kelengkapanTotalHari = (int) ($kelengkapanRow->total_hari ?? 0);
                $kelengkapanSiswaHariTercatat = (int) ($kelengkapanRow->total_siswa_hari ?? 0);
                $kelengkapanSiswaHariTarget = $totalSiswaBimbingan > 0 ? $totalSiswaBimbingan * $kelengkapanTotalHari : 0;
                $kelengkapanScore = $kelengkapanSiswaHariTarget > 0 ? ($kelengkapanSiswaHariTercatat / $kelengkapanSiswaHariTarget) * 100 : 0;

                $totalSubmitted = (int) optional($submittedTugasByPembimbing->get($pembimbingId))->total_submitted;
                $totalGraded = (int) optional($gradedTugasByPembimbing->get($pembimbingId))->total_graded;
                $nilaiScore = $totalSubmitted > 0 ? ($totalGraded / $totalSubmitted) * 100 : 0;

                $skorAkhir = round((
                    ($absensiScore * $weights['absensi'])
                    + ($sikapScore * $weights['sikap'])
                    + ($kelengkapanScore * $weights['kelengkapan'])
                    + ($nilaiScore * $weights['nilai'])
                ) / $totalWeight, 2);

                return (object) [
                    'id' => $pembimbingId,
                    'nama_pembimbing' => $pembimbing->nama_pembimbing,
                    'nip_pembimbing' => $pembimbing->nip_pembimbing,
                    'total_siswa_bimbingan' => $totalSiswaBimbingan,
                    'absensi_hari_lengkap' => $hariAbsensiLengkap,
                    'absensi_hari_total' => $totalHariAbsensi,
                    'sikap_hari_total' => $sikapTotalHari,
                    'sikap_siswa_hari_tercatat' => $sikapSiswaHariTercatat,
                    'sikap_siswa_hari_target' => $sikapSiswaHariTarget,
                    'kelengkapan_hari_total' => $kelengkapanTotalHari,
                    'kelengkapan_siswa_hari_tercatat' => $kelengkapanSiswaHariTercatat,
                    'kelengkapan_siswa_hari_target' => $kelengkapanSiswaHariTarget,
                    'nilai_tugas_terisi' => $totalGraded,
                    'nilai_tugas_submitted' => $totalSubmitted,
                    'score_absensi' => round($absensiScore, 2),
                    'score_sikap' => round($sikapScore, 2),
                    'score_kelengkapan' => round($kelengkapanScore, 2),
                    'score_nilai' => round($nilaiScore, 2),
                    'skor_akhir' => $skorAkhir,
                ];
            })
            ->filter(fn($row) => $row->total_siswa_bimbingan > 0)
            ->sortByDesc(fn($row) => sprintf('%010.2f-%08d', $row->skor_akhir, $row->total_siswa_bimbingan))
            ->take(10)
            ->values();

        return [
            'topGuru' => $topGuru,
            'weights' => $weights,
            'totalWeight' => $totalWeight,
            'tanggal_awal' => $tanggalAwal,
            'tanggal_akhir' => $tanggalAkhir,
        ];
    }

    private function validateTopGuruFilterInput(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'guru_tanggal_awal' => ['nullable', 'date'],
            'guru_tanggal_akhir' => ['nullable', 'date', 'after_or_equal:guru_tanggal_awal'],
            'bobot_absensi' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'bobot_sikap' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'bobot_kelengkapan' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'bobot_nilai' => ['nullable', 'numeric', 'min:0', 'max:1000'],
        ]);

        $validated = $validator->validate();

        $weightsDefault = [
            'absensi' => 25,
            'sikap' => 25,
            'kelengkapan' => 25,
            'nilai' => 25,
        ];

        $weights = [
            'absensi' => max(0, (float) ($validated['bobot_absensi'] ?? $weightsDefault['absensi'])),
            'sikap' => max(0, (float) ($validated['bobot_sikap'] ?? $weightsDefault['sikap'])),
            'kelengkapan' => max(0, (float) ($validated['bobot_kelengkapan'] ?? $weightsDefault['kelengkapan'])),
            'nilai' => max(0, (float) ($validated['bobot_nilai'] ?? $weightsDefault['nilai'])),
        ];

        return [
            'guru_tanggal_awal' => $validated['guru_tanggal_awal'] ?? null,
            'guru_tanggal_akhir' => $validated['guru_tanggal_akhir'] ?? null,
            'weights' => $weights,
        ];
    }
}
