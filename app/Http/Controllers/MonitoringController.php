<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FormatsExcelSheets;
use App\Models\Monitoring;
use App\Models\Pembimbing;
use App\Models\Perusahaan;
use App\Models\Siswa;
use App\Models\TempatPkl;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Pdf as WriterPdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;

class MonitoringController extends Controller
{
    use FormatsExcelSheets;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            if (auth()->user()->role == 'panitia') {
                $grouped = TempatPkl::with(['siswa', 'perusahaan', 'pembimbing'])
                    ->get();
            } else {
                $grouped = TempatPkl::with(['siswa', 'perusahaan', 'pembimbing'])
                    ->whereHas('siswa.kelas.jurusan', function ($query) {
                        $query->where('id', auth()->user()->jurusan_id);
                    })
                    ->get();
            }

            return DataTables::of($grouped)
                ->addIndexColumn()
                ->addColumn('aksi', function ($row) {
                    return '
                        <button class="btn btn-sm btn-warning btn-edit"
                        data-id="' . $row->id . '"
                        data-perusahaan="' . $row->perusahaan_id . '"
                        data-siswa="' . $row->siswa_id . '"
                        data-pembimbing="' . ($row->pembimbing_id ?? '') . '"
                        >Edit
                        </button>
                        <button class="btn btn-sm btn-success btnUpdateKesediaan"
                        data-id="' . $row->id . '"
                        >Upload
                        </button>
                        <button class="btn btn-sm btn-danger btn-hapus" data-id="' . $row->id . '">
                            Hapus
                        </button>
                    ';
                })
                ->rawColumns(['aksi'])
                ->make(true);
        }

        $siswa = Siswa::orderBy('nama_siswa')->get();
        $perusahaan = Perusahaan::orderBy('nama_perusahaan')->get();
        $pembimbing = $this->availablePembimbing()->orderBy('nama_pembimbing')->get();

        return view('monitoring.index', compact('siswa', 'perusahaan', 'pembimbing'));
    }

    public function data(Request $request)
    {
        return $this->index($request);
    }


    /**
     * Display a listing of the resource in data table.
     */
    public function index_cetak(Request $request)
    {
        if ($request->ajax()) {
            // Grouping by perusahaan
            $grouped = TempatPkl::with(['siswa.kelas', 'perusahaan'])
                ->get()
                ->groupBy('perusahaan_id', 'pembimbing_id')
                ->map(function ($group) {
                    return [
                        'id' => $group->first()->perusahaan_id,
                        'perusahaan' => $group->first()->perusahaan->nama_perusahaan,
                        'siswa' => $group->pluck('siswa.nama_siswa')->implode(', '),
                        'pembimbing' => $group->first()->pembimbing->nama_pembimbing ?? '-'
                    ];
                })->values();

            return DataTables::of($grouped)
                ->addIndexColumn()
                ->addColumn('aksi', function ($row) {
                    return '
                        <button type="button" class="btn btn-sm btn-warning btn-edit-pembimbing" data-id="' . $row['id'] . '" data-pembimbing="' . $row['pembimbing'] . '">Edit Pembimbing</button>
                        <a href="' . route('monitoring.cetak-monitoring', $row['id']) . '" target="_blank" class="btn btn-sm btn-success btn-cetak" data-id="' . $row['id'] . '">Cetak</a>
                        <a href="' . route('monitoring.cetak-sppd', $row['id']) . '" target="_blank" class="btn btn-sm btn-info btn-cetak" data-id="' . $row['id'] . '">Cetak SPPD</a>';
                })
                ->rawColumns(['aksi'])
                ->make(true);
        }

        $siswa = Siswa::all();
        $perusahaan = Perusahaan::all();
        $pembimbing = $this->availablePembimbing()->orderBy('nama_pembimbing')->get();
        //   var_dump($siswa);
        return view('monitoring.index_cetak', compact('siswa', 'perusahaan', 'pembimbing'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('monitoring.create');
    }

    private function availablePembimbing()
    {
        $query = Pembimbing::query();

        if (auth()->user()->role === 'panitia' || !auth()->user()?->jurusan_id) {
            return $query;
        }

        return $query->where(function ($builder) {
            $builder->where('jenis_guru', 'adaptif_normatif')
                ->orWhere('jurusan_id', auth()->user()->jurusan_id);
        });
    }


    /**
     * cetak monitoring perusahaan.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function cetak($id)
    {
        $data = DB::table('tempat_pkl as tp')
            ->leftJoin('siswa as s', 'tp.siswa_id', '=', 's.id')
            ->leftJoin('perusahaan as p', 'tp.perusahaan_id', '=', 'p.id')
            ->leftJoin('pembimbings as pb', 'tp.pembimbing_id', '=', 'pb.id')
            ->select(
                's.id as siswa_id',
                's.nama_siswa',
                'p.id as perusahaan_id',
                'p.nama_perusahaan',
                'pb.nama_pembimbing as nama_pembimbing',
                'tp.tanggal_mulai',
                'tp.tanggal_selesai',
            )
            ->where('p.id', $id)
            ->get();

        $pdf = pdf::loadView('tempat_pkl.cetak', compact('data'));
        return $pdf->stream('surat-izin-pkl.pdf'); // atau ->download('namafile.pdf');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    public function setTanggal(Request $request)
    {
        Carbon::setLocale('id');

        $request->validate([
            'tanggal_surat' => 'required|date',
            'tanggal_berangkat' => 'required|date',
            'nama_kepala_sekolah' => 'required|string',
            'nip_kepala_sekolah' => 'required|string',
            'nama_file_ttd' => 'required|string',
            'nomor_surat' => 'required|string',
        ]);

        session([
            'tanggal_surat' => $request->tanggal_surat,
            'tanggal_berangkat' => $request->tanggal_berangkat,
            'nama_kepala_sekolah' => $request->nama_kepala_sekolah,
            'nip_kepala_sekolah' => $request->nip_kepala_sekolah,
            'nama_file_ttd' => $request->nama_file_ttd,
            'nomor_surat' => $request->nomor_surat,
        ]);

        return redirect()->back()->with('success', 'Tanggal berhasil diset!');
    }




    /**
     * Display the specified resource.
     */
    public function lihatdata(Request $request, $id)
    {

        $data = TempatPkl::with(['siswa', 'perusahaan', 'pembimbing'])->where('perusahaan_id', $id)->first();
        return response()->json($data);
    }


    public function cetakMonitoring($id)
    {
        Carbon::setLocale('id');

        $tanggalSurat = session('tanggal_surat')
            ?: Carbon::now()->translatedFormat('d F Y');

        $tanggalBerangkat = session('tanggal_berangkat')
            ?: Carbon::now()->translatedFormat('d F Y');

        $data = TempatPkl::with(['siswa.kelas.jurusan', 'perusahaan', 'pembimbing'])
            ->where('perusahaan_id', $id)
            ->get();


        $pdf = pdf::loadView('monitoring.cetak_st', compact('data') + [
            'tanggal_surat' => $tanggalSurat,
            'tanggal_berangkat' => $tanggalBerangkat,
            'nama_kepala_sekolah' => session('nama_kepala_sekolah'),
            'nip_kepala_sekolah' => session('nip_kepala_sekolah'),
            'nama_file_ttd' => session('nama_file_ttd'),
            'nomor_surat' => session('nomor_surat'),

        ]);
        $pdf->setOption(['enable_remote' => true, 'isHTML5ParserEnabled' => true]);
        return $pdf->stream('surat-izin-pkl.pdf');
    }

    public function exportExcel()
    {
        $data = TempatPkl::with(['siswa.kelas.jurusan', 'perusahaan', 'pembimbing'])
            ->orderBy('perusahaan_id')
            ->get();

        if ($data->isEmpty()) {
            return response()->json(['message' => 'Tidak ada data untuk diekspor'], 404);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $headers = [
            'Nama Siswa',
            'NISN',
            'Kelas',
            'Jurusan',
            'Perusahaan',
            'Alamat',
            'Nama Pemilik (PIC)',
            'No. Telpon Pemilik (No. Telp PIC)',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Pembimbing Monitoring',
            'Pembimbing Sekolah',
            'Keterangan Wilayah',
            'Jumlah Perusahaan per Pembimbing',
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        $row = 2;

        // Kelompokkan data berdasarkan pembimbing sekolah
        $grouped = $data->groupBy('pembimbing_id');

        foreach ($grouped as $pembimbingId => $items) {

            $firstRow = $row;
            $lastRow = $row + count($items) - 1;

            // Hitung jumlah perusahaan unik untuk pembimbing ini
            $companyCount = $items->pluck('perusahaan_id')->unique()->count();

            foreach ($items as $item) {
                // Data siswa
                $sheet->setCellValue('A' . $row, $item->siswa->nama_siswa ?? '-');

                $sheet->setCellValueExplicit(
                    'B' . $row,
                    $item->siswa->nis ?? '-',
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );

                $sheet->setCellValue('C' . $row, $item->siswa->kelas->nama_kelas ?? '-');
                $sheet->setCellValue('D' . $row, $item->siswa->kelas->jurusan->nama_jurusan ?? '-');

                // Isi kolom perusahaan per baris (tidak di-merge karena bisa berbeda per siswa)
                $sheet->setCellValue('E' . $row, $item->perusahaan->nama_perusahaan ?? '-');
                $sheet->setCellValue('F' . $row, $item->perusahaan->alamat ?? '-');
                $sheet->setCellValue('G' . $row, $item->perusahaan->nama_pemilik_perusahaan ?? '-');
                $sheet->setCellValueExplicit('H' . $row, $item->perusahaan->telepon_pemilik_perusahaan ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

                // Tanggal mulai & selesai tetap per siswa
                $sheet->setCellValue('I' . $row, $item->tanggal_mulai ?? '-');
                $sheet->setCellValue('J' . $row, $item->tanggal_selesai ?? '-');
                // Keterangan wilayah berdasarkan rekap wilayah untuk perusahaan ini
                $sheet->setCellValue('M' . $row, $this->computeWilayahForPerusahaan($item->perusahaan));

                $row++;
            }

            // Merge pembimbing-related columns (K, L, N) untuk setiap kelompok pembimbing
            foreach (['K', 'L', 'N'] as $column) {
                if ($firstRow < $lastRow) {
                    $sheet->mergeCells($column . $firstRow . ':' . $column . $lastRow);
                }
            }

            $firstItem = $items->first();

            // Isi merged cell pembimbing (tampil sekali saja)
            $sheet->setCellValue('K' . $firstRow, $firstItem->nama_pembimbing ?? '-');
            $sheet->setCellValue('L' . $firstRow, $firstItem->pembimbing->nama_pembimbing ?? '-');
            $sheet->setCellValue('N' . $firstRow, $companyCount);
        }

        $this->applyExcelTableFormatting($sheet, 'N', $row - 1);

        // Save file
        $fileName = 'monitoring_pkl_merge_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        $filePath = storage_path('app/public/' . $fileName);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    /**
     * Stream the Excel export directly to the user (no disk save).
     */
    public function downloadExport()
    {
        $data = TempatPkl::with(['siswa.kelas.jurusan', 'perusahaan', 'pembimbing'])
            ->orderBy('perusahaan_id')
            ->get();

        if ($data->isEmpty()) {
            return response()->json(['message' => 'Tidak ada data untuk diekspor'], 404);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $headers = [
            'Nama Siswa',
            'NISN',
            'Kelas',
            'Jurusan',
            'Perusahaan',
            'Alamat',
            'Nama Pemilik (PIC)',
            'No. Telpon Pemilik (No. Telp PIC)',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Pembimbing Monitoring',
            'Pembimbing Sekolah',
            'Keterangan Wilayah',
            'Jumlah Perusahaan per Pembimbing',
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        $row = 2;

        // Kelompokkan data berdasarkan pembimbing sekolah
        $grouped = $data->groupBy('pembimbing_id');

        foreach ($grouped as $pembimbingId => $items) {
            $firstRow = $row;
            $lastRow = $row + count($items) - 1;

            // Hitung jumlah perusahaan unik untuk pembimbing ini
            $companyCount = $items->pluck('perusahaan_id')->unique()->count();

            foreach ($items as $item) {
                $sheet->setCellValue('A' . $row, $item->siswa->nama_siswa ?? '-');
                $sheet->setCellValueExplicit('B' . $row, $item->siswa->nis ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('C' . $row, $item->siswa->kelas->nama_kelas ?? '-');
                $sheet->setCellValue('D' . $row, $item->siswa->kelas->jurusan->nama_jurusan ?? '-');

                $sheet->setCellValue('E' . $row, $item->perusahaan->nama_perusahaan ?? '-');
                $sheet->setCellValue('F' . $row, $item->perusahaan->alamat ?? '-');
                $sheet->setCellValue('G' . $row, $item->perusahaan->nama_pemilik_perusahaan ?? '-');
                $sheet->setCellValueExplicit('H' . $row, $item->perusahaan->telepon_pemilik_perusahaan ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

                $sheet->setCellValue('I' . $row, $item->tanggal_mulai ?? '-');
                $sheet->setCellValue('J' . $row, $item->tanggal_selesai ?? '-');
                // Keterangan wilayah per perusahaan
                $sheet->setCellValue('M' . $row, $this->computeWilayahForPerusahaan($item->perusahaan));

                $row++;
            }

            // Merge pembimbing-related columns (K, L, N) untuk setiap kelompok pembimbing
            foreach (['K', 'L', 'N'] as $column) {
                if ($firstRow < $lastRow) {
                    $sheet->mergeCells($column . $firstRow . ':' . $column . $lastRow);
                }
            }

            $firstItem = $items->first();
            $sheet->setCellValue('K' . $firstRow, $firstItem->nama_pembimbing ?? '-');
            $sheet->setCellValue('L' . $firstRow, $firstItem->pembimbing->nama_pembimbing ?? '-');
            $sheet->setCellValue('N' . $firstRow, $companyCount);
        }

        $this->applyExcelTableFormatting($sheet, 'N', $row - 1);

        $fileName = 'monitoring_pkl_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ]);
    }



    // public function cetakSppd($id)
    // {
    //     $data = TempatPkl::with(['siswa.kelas.jurusan', 'perusahaan', 'pembimbing'])
    //         ->where('perusahaan_id', $id)
    //         ->get();

    //     $pdf = pdf::loadView('monitoring.cetak_sppd', compact('data'));
    //     return $pdf->stream('surat-perjalanan-dinas.pdf'); // atau ->download('namafile.pdf');
    // }

    public function cetakSppd($id)
    {
        Carbon::setLocale('id');

        $tanggalSurat = session('tanggal_surat')
            ?: Carbon::now()->translatedFormat('d F Y');

        $tanggalBerangkat = session('tanggal_berangkat')
            ?: Carbon::now()->translatedFormat('d F Y');


        // Ambil SEMUA siswa terkait perusahaan (id=perusahaan_id)
        $data = TempatPkl::with(['siswa.kelas.jurusan', 'perusahaan', 'pembimbing'])
            ->where('perusahaan_id', $id)
            ->get();

        // tanggal berlaku untuk semua
        $pdf = PDF::loadView('monitoring.cetak_sppd', [
            'data' => $data,
            'tanggal_surat' => $tanggalSurat,
            'tanggal_berangkat' => $tanggalBerangkat,
            'nama_kepala_sekolah' => session('nama_kepala_sekolah'),
            'nip_kepala_sekolah' => session('nip_kepala_sekolah'),
            'nama_file_ttd' => session('nama_file_ttd'),
            'nomor_surat' => session('nomor_surat'),
        ]);
        $pdf->setOption(['enable_remote' => true, 'isHTML5ParserEnabled' => true]);
        return $pdf->stream('sppd.pdf');
    }





    /**
     * Show the form for editing the specified resource.
     */

    /**
     * Update the specified resource in storage.
     */
    public function edit($id)
    {
        $data = TempatPkl::with(['siswa', 'perusahaan', 'pembimbing'])->findOrFail($id);

        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'perusahaan_id' => 'required|exists:perusahaan,id',
            'pembimbing_id' => 'nullable|exists:pembimbings,id',
        ]);

        $data = TempatPkl::findOrFail($id);
        $data->update([
            'siswa_id' => $request->siswa_id,
            'perusahaan_id' => $request->perusahaan_id,
            'pembimbing_id' => $request->pembimbing_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data monitoring berhasil diupdate.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $data = TempatPkl::findOrFail($id);
        $data->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data monitoring berhasil dihapus.'
        ]);
    }

    /**
     * Compute wilayah label for a perusahaan based on rekap logic.
     */
    private function computeWilayahForPerusahaan($perusahaan): string
    {
        $kecamatanName = $this->resolveKecamatanName($perusahaan->kecamatan_id);

        $normalized = $kecamatanName ? Str::upper(trim($kecamatanName)) : null;
        $isSelaawiGroup = $normalized && in_array($normalized, ['SELAAWI', 'BALUBUR LIMBANGAN', 'BLUBUR LIMBANGAN', 'CIBUGEL', 'CIBIUK'], true);

        if ($isSelaawiGroup) {
            return 'Wilayah Selaawi';
        }

        $kabupatenKotaName = $this->resolveKabupatenKotaName($perusahaan->provinsi_id, $perusahaan->kabupaten_kota_id) ?? 'Kabupaten/Kota Tidak Diketahui';

        return 'Kabupaten/Kota ' . $kabupatenKotaName;
    }

    private function cachedWilayahJson(string $cacheKey, string $url): array
    {
        return Cache::remember($cacheKey, now()->addDay(), function () use ($url) {
            $response = Http::timeout(15)->retry(2, 150)->get($url);

            if (! $response->successful()) {
                return [];
            }

            return $response->json() ?? [];
        });
    }

    private function resolveKecamatanName($kecamatanId): ?string
    {
        if (empty($kecamatanId)) {
            return null;
        }

        $regencyId = substr((string) $kecamatanId, 0, 4);
        $districts = $this->cachedWilayahJson(
            "wilayah:districts:{$regencyId}",
            "https://www.emsifa.com/api-wilayah-indonesia/api/districts/{$regencyId}.json"
        );

        foreach ($districts as $district) {
            if ((string) ($district['id'] ?? '') === (string) $kecamatanId) {
                return $district['name'] ?? null;
            }
        }

        return null;
    }

    private function resolveKabupatenKotaName($provinceId, $kabupatenKotaId): ?string
    {
        if (empty($provinceId) || empty($kabupatenKotaId)) {
            return null;
        }

        $regencies = $this->cachedWilayahJson(
            "wilayah:regencies:{$provinceId}",
            "https://www.emsifa.com/api-wilayah-indonesia/api/regencies/{$provinceId}.json"
        );

        foreach ($regencies as $regency) {
            if ((string) ($regency['id'] ?? '') === (string) $kabupatenKotaId) {
                return $regency['name'] ?? null;
            }
        }

        return null;
    }
}
