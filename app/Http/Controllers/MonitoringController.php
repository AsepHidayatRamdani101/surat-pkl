<?php

namespace App\Http\Controllers;

use App\Models\Monitoring;
use App\Models\Pembimbing;
use App\Models\Perusahaan;
use App\Models\Siswa;
use App\Models\TempatPkl;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Pdf as WriterPdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;

class MonitoringController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Grouping by perusahaan
            if (auth()->user()->role == 'panitia') {
                $grouped = TempatPkl::with(['siswa', 'perusahaan', 'pembimbing'])
                    ->get();
            } else {
                $grouped = TempatPkl::with(['siswa', 'perusahaan', 'pembimbing'])
                    ->whereHas('siswa.kelas.jurusan', function ($query) {
                        $query->where('id', auth()->user()->jurusan_id);
                    });
            }

            return DataTables::of($grouped)
                ->addIndexColumn()
                ->addColumn('aksi', function ($row) {
                    return '
                        <button class="btn btn-sm btn-warning btn-edit" 
                        data-id="' . $row->id . '"
                        data-perusahaan="' . $row->perusahaan_id . '"
                        data-siswa="' . $row->siswa_id . '"
                        data-tanggal-mulai="' . $row->tanggal_mulai . '"
                        data-tanggal-selesai="' . $row->tanggal_selesai . '"
                        >Edit
                        </button>
                        <button class="btn btn-sm btn-success btnUpdateKesediaan" 
                        data-id="' . $row->id . '"
                        >Upload
                        </button>
                        <button class="btn btn-sm btn-danger btn-hapus" data-id="' . $row['id'] . '">
                            Hapus
                        </button>
                        
                    ';
                })
                ->rawColumns(['aksi'])
                ->make(true);
        }

        $siswa = Siswa::orderBy('nama_siswa')->get();
        $perusahaan = Perusahaan::orderBy('nama_perusahaan')->get();
        $pembimbing = Pembimbing::orderBy('nama_pembimbing')->get();
        //   var_dump($siswa);
        return view('monitoring.index', compact('siswa', 'perusahaan', 'pembimbing'));
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
        $pembimbing = Pembimbing::all();
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
        return $pdf->stream('surat-izin-pkl.pdf'); // atau ->download('namafile.pdf');

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
            'Pembimbing',
            'No HP Pembimbing',
            'Alamat Perusahaan',
            'Tanggal Mulai',
            'Tanggal Selesai'
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        $row = 2;

        // Kelompokkan data berdasarkan perusahaan
        $grouped = $data->groupBy('perusahaan_id');

        foreach ($grouped as $perusahaanId => $items) {

            $firstRow = $row;
            $lastRow = $row + count($items) - 1;

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

                // Kolom group perusahaan → isi nanti setelah merge
                $row++;
            }

            // Merge kolom E sampai H untuk perusahaan yang sama
            foreach (['E', 'F', 'G', 'H'] as $column) {
                $sheet->mergeCells($column . $firstRow . ':' . $column . $lastRow);
            }

            $firstItem = $items->first();

            // Isi merged cell (tampil sekali saja)
            $sheet->setCellValue('E' . $firstRow, $firstItem->perusahaan->nama_perusahaan ?? '-');
            $sheet->setCellValue('F' . $firstRow, $firstItem->pembimbing->nama_pembimbing ?? '-');
            $sheet->setCellValue('G' . $firstRow, $firstItem->pembimbing->no_hp ?? '-');
            $sheet->setCellValue('H' . $firstRow, $firstItem->perusahaan->alamat ?? '-');

            // Isi tanggal mulai & selesai tetap per siswa
            $tempRow = $firstRow;
            foreach ($items as $item) {
                $sheet->setCellValue('I' . $tempRow, $item->tanggal_mulai ?? '-');
                $sheet->setCellValue('J' . $tempRow, $item->tanggal_selesai ?? '-');
                $tempRow++;
            }
        }

        // Styling
        $sheet->setAutoFilter('A1:J1');

        foreach (range('A', 'J') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Header style
        $styleHeader = [
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9D9D9']
            ]
        ];

        $sheet->getStyle('A1:J1')->applyFromArray($styleHeader);

        // Border all
        $sheet->getStyle('A1:J' . ($row - 1))
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Save file
        $fileName = 'monitoring_pkl_merge_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        $filePath = storage_path('app/public/' . $fileName);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend(true);
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

        return $pdf->stream('sppd.pdf');
    }





    /**
     * Show the form for editing the specified resource.
     */

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Monitoring $monitoring)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Monitoring $monitoring)
    {
        //
    }
}
