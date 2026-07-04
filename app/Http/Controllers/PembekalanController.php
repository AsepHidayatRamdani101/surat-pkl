<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Bimbingan;
use App\Models\Kelas;
use App\Models\Pembimbing;
use App\Models\Sekolah;
use App\Models\Siswa;
use App\Models\TugasPembekalan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PembekalanController extends Controller
{
    public function materi(Request $request)
    {
        $filters = [
            'pembimbing_id' => $request->get('pembimbing_id'),
            'kelas_id' => $request->get('kelas_id'),
            'tanggal_awal' => $request->get('tanggal_awal'),
            'tanggal_akhir' => $request->get('tanggal_akhir'),
            'keyword' => $request->get('keyword'),
        ];

        $query = Bimbingan::with(['siswa.kelas', 'pembimbing'])
            ->whereNotNull('topik_pembekalan')
            ->where('topik_pembekalan', '!=', '')
            ->orderByDesc('tanggal_bimbingan')
            ->orderByDesc('id');

        if (!empty($filters['pembimbing_id'])) {
            $query->where('pembimbing_id', $filters['pembimbing_id']);
        }

        if (!empty($filters['kelas_id'])) {
            $query->whereHas('siswa', function ($q) use ($filters) {
                $q->where('kelas_id', $filters['kelas_id']);
            });
        }

        if (!empty($filters['tanggal_awal'])) {
            $query->whereDate('tanggal_bimbingan', '>=', $filters['tanggal_awal']);
        }

        if (!empty($filters['tanggal_akhir'])) {
            $query->whereDate('tanggal_bimbingan', '<=', $filters['tanggal_akhir']);
        }

        if (!empty($filters['keyword'])) {
            $keyword = trim($filters['keyword']);
            $query->where('topik_pembekalan', 'like', '%' . $keyword . '%');
        }

        $materi = $query->get();
        $editMateri = null;

        if ($request->filled('edit')) {
            $editMateri = Bimbingan::query()
                ->whereNotNull('topik_pembekalan')
                ->where('topik_pembekalan', '!=', '')
                ->find($request->get('edit'));
        }

        $summary = [
            'total_materi' => $materi->count(),
            'topik_unik' => $materi->pluck('topik_pembekalan')->filter()->unique()->count(),
            'total_siswa' => $materi->pluck('siswa_id')->filter()->unique()->count(),
            'total_pembimbing' => $materi->pluck('pembimbing_id')->filter()->unique()->count(),
        ];

        $pembimbingOptions = Pembimbing::orderBy('nama_pembimbing')->get(['id', 'nama_pembimbing']);
        $kelasOptions = Kelas::orderBy('nama_kelas')->get(['id', 'nama_kelas']);
        $siswaOptions = Siswa::with('kelas')->orderBy('nama_siswa')->get(['id', 'nama_siswa', 'kelas_id']);

        return view('pembekalan.materi', compact(
            'materi',
            'summary',
            'filters',
            'pembimbingOptions',
            'kelasOptions',
            'siswaOptions',
            'editMateri'
        ));
    }

    public function storeMateri(Request $request)
    {
        $validated = $request->validate([
            'pembimbing_id' => ['required'],
            'siswa_id' => ['required'],
            'tanggal_bimbingan' => ['required', 'date'],
            'topik_pembekalan' => ['required', 'string', 'max:255'],
            'catatan' => ['nullable', 'string'],
            'status_absensi' => ['required', 'in:hadir,izin,alpa'],
            'materi_tipe' => ['required', 'in:text,pdf,video'],
            'materi_isi' => ['nullable', 'string'],
            'materi_video_url' => ['nullable', 'url', 'max:255'],
            'materi_file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        if ($validated['materi_tipe'] === 'text' && empty(trim((string) ($validated['materi_isi'] ?? '')))) {
            return back()->withErrors(['materi_isi' => 'Isi materi wajib diisi untuk tipe text.'])->withInput();
        }

        if ($validated['materi_tipe'] === 'video' && empty($validated['materi_video_url'])) {
            return back()->withErrors(['materi_video_url' => 'URL video wajib diisi untuk tipe video.'])->withInput();
        }

        if ($validated['materi_tipe'] === 'pdf' && !$request->hasFile('materi_file')) {
            return back()->withErrors(['materi_file' => 'File PDF wajib diunggah untuk tipe pdf.'])->withInput();
        }

        $selectedPembimbing = (string) $validated['pembimbing_id'];
        $selectedSiswa = (string) $validated['siswa_id'];

        if ($selectedPembimbing !== 'all' && !Pembimbing::whereKey($selectedPembimbing)->exists()) {
            return back()->withErrors(['pembimbing_id' => 'Pembimbing tidak valid.'])->withInput();
        }

        if ($selectedSiswa !== 'all' && !Siswa::whereKey($selectedSiswa)->exists()) {
            return back()->withErrors(['siswa_id' => 'Siswa tidak valid.'])->withInput();
        }

        $targets = $this->resolveMateriTargets($selectedPembimbing, $selectedSiswa);

        if (empty($targets)) {
            return back()->withErrors([
                'siswa_id' => 'Target siswa/pembimbing tidak ditemukan. Pastikan kelompok bimbingan sudah disusun.',
            ])->withInput();
        }

        $createdCount = 0;
        $updatedCount = 0;
        $uploadedFilePath = null;

        if ($validated['materi_tipe'] === 'pdf' && $request->hasFile('materi_file')) {
            $uploadedFilePath = $request->file('materi_file')->store('materi-pembekalan', 'public');
        }

        $materiPayload = [
            'materi_tipe' => $validated['materi_tipe'],
            'materi_isi' => $validated['materi_tipe'] === 'text' ? $validated['materi_isi'] : null,
            'materi_file_path' => $validated['materi_tipe'] === 'pdf' ? $uploadedFilePath : null,
            'materi_video_url' => $validated['materi_tipe'] === 'video' ? $validated['materi_video_url'] : null,
        ];

        foreach ($targets as $target) {
            $record = Bimbingan::updateOrCreate(
                [
                    'pembimbing_id' => $target['pembimbing_id'],
                    'siswa_id' => $target['siswa_id'],
                    'tanggal_bimbingan' => $validated['tanggal_bimbingan'],
                    'topik_pembekalan' => $validated['topik_pembekalan'],
                    'materi_tipe' => $validated['materi_tipe'],
                ],
                [
                    'catatan' => $validated['catatan'] ?? null,
                    'status_absensi' => $validated['status_absensi'],
                    'materi_isi' => $materiPayload['materi_isi'],
                    'materi_file_path' => $materiPayload['materi_file_path'],
                    'materi_video_url' => $materiPayload['materi_video_url'],
                ]
            );

            if (!$record->wasRecentlyCreated && $validated['materi_tipe'] === 'pdf' && !empty($record->getOriginal('materi_file_path')) && $record->getOriginal('materi_file_path') !== $uploadedFilePath) {
                $this->deleteMateriFileIfUnused($record->getOriginal('materi_file_path'));
            }

            if ($record->wasRecentlyCreated) {
                $createdCount++;
            } else {
                $updatedCount++;
            }
        }

        return redirect()->route('pembekalan.materi')->with(
            'success',
            "Materi pembekalan berhasil diproses. {$createdCount} data ditambahkan, {$updatedCount} data diperbarui."
        );
    }

    public function updateMateri(Request $request, Bimbingan $bimbingan)
    {
        $validated = $request->validate([
            'pembimbing_id' => ['required', 'exists:pembimbings,id'],
            'siswa_id' => ['required', 'exists:siswa,id'],
            'tanggal_bimbingan' => ['required', 'date'],
            'topik_pembekalan' => ['required', 'string', 'max:255'],
            'catatan' => ['nullable', 'string'],
            'status_absensi' => ['required', 'in:hadir,izin,alpa'],
            'materi_tipe' => ['required', 'in:text,pdf,video'],
            'materi_isi' => ['nullable', 'string'],
            'materi_video_url' => ['nullable', 'url', 'max:255'],
            'materi_file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        if ($validated['materi_tipe'] === 'text' && empty(trim((string) ($validated['materi_isi'] ?? '')))) {
            return back()->withErrors(['materi_isi' => 'Isi materi wajib diisi untuk tipe text.'])->withInput();
        }

        if ($validated['materi_tipe'] === 'video' && empty($validated['materi_video_url'])) {
            return back()->withErrors(['materi_video_url' => 'URL video wajib diisi untuk tipe video.'])->withInput();
        }

        if ($validated['materi_tipe'] === 'pdf' && !$request->hasFile('materi_file') && empty($bimbingan->materi_file_path)) {
            return back()->withErrors(['materi_file' => 'File PDF wajib diunggah untuk tipe pdf.'])->withInput();
        }

        $newFilePath = $bimbingan->materi_file_path;
        if ($validated['materi_tipe'] === 'pdf' && $request->hasFile('materi_file')) {
            $newFilePath = $request->file('materi_file')->store('materi-pembekalan', 'public');
        }

        if ($validated['materi_tipe'] !== 'pdf' && !empty($bimbingan->materi_file_path)) {
            $this->deleteMateriFileIfUnused($bimbingan->materi_file_path, $bimbingan->id);
            $newFilePath = null;
        }

        $bimbingan->update([
            'pembimbing_id' => (int) $validated['pembimbing_id'],
            'siswa_id' => (int) $validated['siswa_id'],
            'tanggal_bimbingan' => $validated['tanggal_bimbingan'],
            'topik_pembekalan' => $validated['topik_pembekalan'],
            'materi_tipe' => $validated['materi_tipe'],
            'materi_isi' => $validated['materi_tipe'] === 'text' ? $validated['materi_isi'] : null,
            'materi_file_path' => $validated['materi_tipe'] === 'pdf' ? $newFilePath : null,
            'materi_video_url' => $validated['materi_tipe'] === 'video' ? $validated['materi_video_url'] : null,
            'catatan' => $validated['catatan'] ?? null,
            'status_absensi' => $validated['status_absensi'],
        ]);

        if ($validated['materi_tipe'] === 'pdf' && $request->hasFile('materi_file') && !empty($bimbingan->getOriginal('materi_file_path')) && $bimbingan->getOriginal('materi_file_path') !== $newFilePath) {
            $this->deleteMateriFileIfUnused($bimbingan->getOriginal('materi_file_path'), $bimbingan->id);
        }

        return redirect()->route('pembekalan.materi')->with('success', 'Materi pembekalan berhasil diperbarui.');
    }

    public function destroyMateri(Bimbingan $bimbingan)
    {
        if (!empty($bimbingan->materi_file_path)) {
            $this->deleteMateriFileIfUnused($bimbingan->materi_file_path, $bimbingan->id);
        }

        $bimbingan->delete();

        return redirect()->route('pembekalan.materi')->with('success', 'Materi pembekalan berhasil dihapus.');
    }

    public function index(Request $request)
    {
        $filters = [
            'pembimbing_id' => $request->get('pembimbing_id'),
            'kelas_id' => $request->get('kelas_id'),
            'tanggal_awal' => $request->get('tanggal_awal'),
            'tanggal_akhir' => $request->get('tanggal_akhir'),
        ];

        $query = Bimbingan::with(['siswa.kelas', 'pembimbing'])
            ->orderByDesc('tanggal_bimbingan')
            ->orderByDesc('id');

        if (!empty($filters['pembimbing_id'])) {
            $query->where('pembimbing_id', $filters['pembimbing_id']);
        }

        if (!empty($filters['kelas_id'])) {
            $query->whereHas('siswa', function ($q) use ($filters) {
                $q->where('kelas_id', $filters['kelas_id']);
            });
        }

        if (!empty($filters['tanggal_awal'])) {
            $query->whereDate('tanggal_bimbingan', '>=', $filters['tanggal_awal']);
        }

        if (!empty($filters['tanggal_akhir'])) {
            $query->whereDate('tanggal_bimbingan', '<=', $filters['tanggal_akhir']);
        }

        $bimbingan = $query->get();

        $materi = $bimbingan->filter(fn($item) => !empty($item->topik_pembekalan))->values();

        $tugasLegacy = $bimbingan->filter(fn($item) => !empty($item->tugas))->map(function ($item) {
            return (object) [
                'tanggal_bimbingan' => $item->tanggal_bimbingan,
                'siswa' => $item->siswa,
                'tugas' => $item->tugas,
                'tugas_siswa' => $item->tugas_siswa,
                'nilai_tugas' => $item->nilai_tugas,
            ];
        })->values();

        $tugasQuery = TugasPembekalan::with([
            'materi',
            'jawabanSiswa.siswa.kelas',
            'jawabanSiswa.nilaiTugas',
        ])->orderByDesc('tanggal_tugas')->orderByDesc('id');

        if (!empty($filters['kelas_id'])) {
            $tugasQuery->whereHas('jawabanSiswa.siswa', function ($q) use ($filters) {
                $q->where('kelas_id', $filters['kelas_id']);
            });
        }

        if (!empty($filters['tanggal_awal'])) {
            $tugasQuery->whereDate('tanggal_tugas', '>=', $filters['tanggal_awal']);
        }

        if (!empty($filters['tanggal_akhir'])) {
            $tugasQuery->whereDate('tanggal_tugas', '<=', $filters['tanggal_akhir']);
        }

        $tugasModern = $tugasQuery->get()->flatMap(function ($item) {
            $jawabanList = $item->jawabanSiswa->sortByDesc('submitted_at');

            if ($jawabanList->isEmpty()) {
                return collect([(object) [
                    'tanggal_bimbingan' => $item->tanggal_tugas,
                    'siswa' => null,
                    'tugas' => $item->judul_tugas,
                    'tugas_siswa' => null,
                    'nilai_tugas' => null,
                ]]);
            }

            return $jawabanList->map(function ($jawaban) use ($item) {
                return (object) [
                    'tanggal_bimbingan' => $item->tanggal_tugas,
                    'siswa' => $jawaban->siswa,
                    'tugas' => $item->judul_tugas,
                    'tugas_siswa' => $jawaban->jawaban_text,
                    'nilai_tugas' => $jawaban->nilaiTugas?->nilai,
                ];
            });
        })->values();

        $tugas = $tugasModern
            ->concat($tugasLegacy)
            ->sortByDesc(function ($item) {
                return $item->tanggal_bimbingan ?? '1970-01-01';
            })
            ->values();

        $absensi = $bimbingan->filter(fn($item) => !empty($item->status_absensi))->values();
        $sikap = $bimbingan->filter(fn($item) => !empty($item->penilaian_sikap) || !empty($item->catatan))->values();

        $summary = [
            'total_sesi' => $bimbingan->count(),
            'materi' => $materi->count(),
            'tugas' => $tugas->count(),
            'hadir' => $absensi->where('status_absensi', 'hadir')->count(),
            'sikap' => $sikap->count(),
        ];

        $pembimbingOptions = Pembimbing::orderBy('nama_pembimbing')->get(['id', 'nama_pembimbing']);
        $kelasOptions = Kelas::orderBy('nama_kelas')->get(['id', 'nama_kelas']);

        return view('pembekalan.index', compact(
            'materi',
            'tugas',
            'absensi',
            'sikap',
            'summary',
            'filters',
            'pembimbingOptions',
            'kelasOptions'
        ));
    }

    public function laporan(Request $request)
    {
        $filters = [
            'pembimbing_id' => $request->get('pembimbing_id'),
            'tanggal_awal' => $request->get('tanggal_awal'),
            'tanggal_akhir' => $request->get('tanggal_akhir'),
        ];

        [$data, $summary, $rekapPembimbing, $topSiswa] = $this->buildLaporanData($filters);

        $pembimbingOptions = Pembimbing::orderBy('nama_pembimbing')->get(['id', 'nama_pembimbing']);

        return view('pembekalan.laporan', compact(
            'data',
            'summary',
            'rekapPembimbing',
            'topSiswa',
            'filters',
            'pembimbingOptions'
        ));
    }

    public function exportExcel(Request $request)
    {
        $filters = [
            'pembimbing_id' => $request->get('pembimbing_id'),
            'tanggal_awal' => $request->get('tanggal_awal'),
            'tanggal_akhir' => $request->get('tanggal_akhir'),
        ];

        [$data, $summary] = $this->buildLaporanData($filters);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Pembekalan');

        $sheet->setCellValue('A1', 'Laporan Pembekalan');
        $sheet->setCellValue('A3', 'Total Sesi');
        $sheet->setCellValue('B3', $summary['total_sesi']);
        $sheet->setCellValue('A4', 'Total Siswa Aktif');
        $sheet->setCellValue('B4', $summary['total_siswa']);
        $sheet->setCellValue('A5', 'Total Pembimbing');
        $sheet->setCellValue('B5', $summary['total_pembimbing']);
        $sheet->setCellValue('A6', 'Total Hadir');
        $sheet->setCellValue('B6', $summary['hadir']);
        $sheet->setCellValue('A7', 'Rata-rata Nilai');
        $sheet->setCellValue('B7', $summary['rata_nilai']);

        $startRow = 9;
        $headers = ['No', 'Tanggal', 'Pembimbing', 'Siswa', 'Kelas', 'Materi', 'Tugas', 'Absensi', 'Nilai', 'Sikap'];

        foreach ($headers as $index => $header) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column . $startRow, $header);
        }

        $row = $startRow + 1;
        foreach ($data as $index => $item) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $item->tanggal_bimbingan ? date('d-m-Y', strtotime($item->tanggal_bimbingan)) : '-');
            $sheet->setCellValue('C' . $row, $item->pembimbing->nama_pembimbing ?? '-');
            $sheet->setCellValue('D' . $row, $item->siswa->nama_siswa ?? '-');
            $sheet->setCellValue('E' . $row, $item->siswa->kelas->nama_kelas ?? '-');
            $sheet->setCellValue('F' . $row, $item->topik_pembekalan ?? '-');
            $sheet->setCellValue('G' . $row, $item->tugas ?? '-');
            $sheet->setCellValue('H' . $row, $item->status_absensi ?? '-');
            $sheet->setCellValue('I' . $row, $item->nilai_tugas ?? '-');
            $sheet->setCellValue('J' . $row, $item->penilaian_sikap ? ucwords(str_replace('_', ' ', $item->penilaian_sikap)) : '-');
            $row++;
        }

        foreach (range('A', 'J') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'laporan-pembekalan-' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function exportPdf(Request $request)
    {
        $filters = [
            'pembimbing_id' => $request->get('pembimbing_id'),
            'tanggal_awal' => $request->get('tanggal_awal'),
            'tanggal_akhir' => $request->get('tanggal_akhir'),
        ];

        [$data, $summary, $rekapPembimbing, $topSiswa] = $this->buildLaporanData($filters);

        $sekolah = Sekolah::first();
        $logoPath = public_path('LogoJabar.png');
        if (!file_exists($logoPath)) {
            $logoPath = public_path('vendor/adminlte/dist/img/AdminLTELogo.png');
        }
        $logoBase64 = file_exists($logoPath) ? $this->toBase64Image($logoPath) : null;

        $capSekolahBase64 = null;
        if ($sekolah && !empty($sekolah->cap_sekolah_path)) {
            $capPath = storage_path('app/public/' . $sekolah->cap_sekolah_path);
            if (file_exists($capPath)) {
                $capSekolahBase64 = $this->toBase64Image($capPath);
            }
        }

        $ttdKepalaSekolahBase64 = null;
        if ($sekolah && !empty($sekolah->ttd_kepala_sekolah_path)) {
            $ttdPath = storage_path('app/public/' . $sekolah->ttd_kepala_sekolah_path);
            if (file_exists($ttdPath)) {
                $ttdKepalaSekolahBase64 = $this->toBase64Image($ttdPath);
            }
        }

        $nomorDokumen = sprintf('LAP-PBK/%s/%s/%03d', now()->format('Y'), now()->format('m'), random_int(1, 999));

        $pdf = Pdf::loadView('pembekalan.laporan_pdf', compact(
            'data',
            'summary',
            'rekapPembimbing',
            'topSiswa',
            'filters',
            'sekolah',
            'logoBase64',
            'capSekolahBase64',
            'ttdKepalaSekolahBase64',
            'nomorDokumen'
        ))
            ->setPaper('A4', 'landscape');

        $filename = 'laporan-pembekalan-' . now()->format('Ymd_His') . '.pdf';

        if ($request->boolean('stream')) {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }

    private function toBase64Image(string $path): ?string
    {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $content = @file_get_contents($path);

        if ($content === false) {
            return null;
        }

        return 'data:image/' . strtolower($type) . ';base64,' . base64_encode($content);
    }

    private function buildLaporanData(array $filters): array
    {

        $query = Bimbingan::with(['siswa.kelas', 'pembimbing'])
            ->orderByDesc('tanggal_bimbingan')
            ->orderByDesc('id');

        if (!empty($filters['pembimbing_id'])) {
            $query->where('pembimbing_id', $filters['pembimbing_id']);
        }

        if (!empty($filters['tanggal_awal'])) {
            $query->whereDate('tanggal_bimbingan', '>=', $filters['tanggal_awal']);
        }

        if (!empty($filters['tanggal_akhir'])) {
            $query->whereDate('tanggal_bimbingan', '<=', $filters['tanggal_akhir']);
        }

        $data = $query->get();

        $summary = [
            'total_sesi' => $data->count(),
            'total_siswa' => $data->pluck('siswa_id')->filter()->unique()->count(),
            'total_pembimbing' => $data->pluck('pembimbing_id')->filter()->unique()->count(),
            'hadir' => $data->where('status_absensi', 'hadir')->count(),
            'izin' => $data->where('status_absensi', 'izin')->count(),
            'alpa' => $data->where('status_absensi', 'alpa')->count(),
            'rata_nilai' => round((float) ($data->whereNotNull('nilai_tugas')->avg('nilai_tugas') ?? 0), 2),
        ];

        $rekapPembimbing = Bimbingan::query()
            ->leftJoin('pembimbings', 'pembimbings.id', '=', 'bimbingans.pembimbing_id')
            ->select('bimbingans.pembimbing_id', 'pembimbings.nama_pembimbing')
            ->selectRaw('COUNT(*) as total_sesi')
            ->selectRaw("SUM(CASE WHEN bimbingans.status_absensi = 'hadir' THEN 1 ELSE 0 END) as total_hadir")
            ->selectRaw('COALESCE(ROUND(AVG(bimbingans.nilai_tugas),2),0) as rata_nilai')
            ->when(!empty($filters['pembimbing_id']), function ($q) use ($filters) {
                $q->where('bimbingans.pembimbing_id', $filters['pembimbing_id']);
            })
            ->when(!empty($filters['tanggal_awal']), function ($q) use ($filters) {
                $q->whereDate('bimbingans.tanggal_bimbingan', '>=', $filters['tanggal_awal']);
            })
            ->when(!empty($filters['tanggal_akhir']), function ($q) use ($filters) {
                $q->whereDate('bimbingans.tanggal_bimbingan', '<=', $filters['tanggal_akhir']);
            })
            ->groupBy('bimbingans.pembimbing_id', 'pembimbings.nama_pembimbing')
            ->orderByDesc('total_sesi')
            ->get();

        $topSiswa = Bimbingan::query()
            ->leftJoin('siswa', 'siswa.id', '=', 'bimbingans.siswa_id')
            ->leftJoin('kelas', 'kelas.id', '=', 'siswa.kelas_id')
            ->select('bimbingans.siswa_id', 'siswa.nama_siswa', 'siswa.nis', 'kelas.nama_kelas')
            ->selectRaw('COUNT(*) as total_sesi')
            ->selectRaw('COALESCE(ROUND(AVG(bimbingans.nilai_tugas),2),0) as rata_nilai')
            ->selectRaw("SUM(CASE WHEN bimbingans.status_absensi = 'hadir' THEN 1 ELSE 0 END) as total_hadir")
            ->when(!empty($filters['pembimbing_id']), function ($q) use ($filters) {
                $q->where('bimbingans.pembimbing_id', $filters['pembimbing_id']);
            })
            ->when(!empty($filters['tanggal_awal']), function ($q) use ($filters) {
                $q->whereDate('bimbingans.tanggal_bimbingan', '>=', $filters['tanggal_awal']);
            })
            ->when(!empty($filters['tanggal_akhir']), function ($q) use ($filters) {
                $q->whereDate('bimbingans.tanggal_bimbingan', '<=', $filters['tanggal_akhir']);
            })
            ->groupBy('bimbingans.siswa_id', 'siswa.nama_siswa', 'siswa.nis', 'kelas.nama_kelas')
            ->orderByDesc('rata_nilai')
            ->orderByDesc('total_hadir')
            ->limit(10)
            ->get();

        return [$data, $summary, $rekapPembimbing, $topSiswa];
    }

    private function resolveMateriTargets(string $pembimbingInput, string $siswaInput): array
    {
        $pairsQuery = DB::table('kelompok_bimbingan_siswa as kbs')
            ->join('kelompok_bimbingan as kb', 'kb.id', '=', 'kbs.kelompok_bimbingan_id')
            ->select('kb.pembimbing_id', 'kbs.siswa_id')
            ->distinct();

        if ($pembimbingInput === 'all' && $siswaInput === 'all') {
            $pairs = (clone $pairsQuery)->get();

            if ($pairs->isEmpty()) {
                $pairs = DB::table('bimbingans')
                    ->select('pembimbing_id', 'siswa_id')
                    ->whereNotNull('pembimbing_id')
                    ->whereNotNull('siswa_id')
                    ->distinct()
                    ->get();
            }

            return $pairs
                ->map(fn($row) => ['pembimbing_id' => (int) $row->pembimbing_id, 'siswa_id' => (int) $row->siswa_id])
                ->all();
        }

        if ($pembimbingInput !== 'all' && $siswaInput !== 'all') {
            return [[
                'pembimbing_id' => (int) $pembimbingInput,
                'siswa_id' => (int) $siswaInput,
            ]];
        }

        if ($pembimbingInput === 'all') {
            $pairs = (clone $pairsQuery)->where('kbs.siswa_id', (int) $siswaInput)->get();

            if ($pairs->isEmpty()) {
                return Pembimbing::query()
                    ->pluck('id')
                    ->map(fn($id) => ['pembimbing_id' => (int) $id, 'siswa_id' => (int) $siswaInput])
                    ->all();
            }

            return $pairs
                ->map(fn($row) => ['pembimbing_id' => (int) $row->pembimbing_id, 'siswa_id' => (int) $siswaInput])
                ->all();
        }

        $pairs = (clone $pairsQuery)->where('kb.pembimbing_id', (int) $pembimbingInput)->get();

        if ($pairs->isEmpty()) {
            return Siswa::query()
                ->pluck('id')
                ->map(fn($id) => ['pembimbing_id' => (int) $pembimbingInput, 'siswa_id' => (int) $id])
                ->all();
        }

        return $pairs
            ->map(fn($row) => ['pembimbing_id' => (int) $pembimbingInput, 'siswa_id' => (int) $row->siswa_id])
            ->all();
    }

    private function deleteMateriFileIfUnused(?string $path, ?int $excludeId = null): void
    {
        if (empty($path)) {
            return;
        }

        $query = Bimbingan::query()->where('materi_file_path', $path);
        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        if (!$query->exists()) {
            Storage::disk('public')->delete($path);
        }
    }
}
