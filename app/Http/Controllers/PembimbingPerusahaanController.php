<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FormatsExcelSheets;
use App\Models\Pembimbing_perusahaan;
use App\Models\Perusahaan;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PembimbingPerusahaanController extends Controller
{
    use FormatsExcelSheets;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $perusahaan = Perusahaan::orderBy('nama_perusahaan', 'asc')->get();
        $pembimbing_perusahaan = Pembimbing_perusahaan::all();

        return view('pembimbing_perusahaan.index', compact('pembimbing_perusahaan', 'perusahaan'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function data(Request $request)
    {
        $rows = Perusahaan::leftJoin('pembimbing_perusahaans as pp', 'perusahaan.id', '=', 'pp.perusahaan_id')
            ->select(
                'perusahaan.id as perusahaan_id',
                'perusahaan.nama_perusahaan',
                'pp.id',
                'pp.nama_pembimbing',
                'pp.NIP',
                'pp.jabatan',
                'pp.jenis_kelamin',
                'pp.nohp'
            )
            ->orderBy('perusahaan.nama_perusahaan')
            ->orderBy('pp.id')
            ->get();

        return DataTables::of($rows)
            ->addIndexColumn()
            ->editColumn('nama_pembimbing', function ($row) {
                return $row->nama_pembimbing ?: '-';
            })
            ->editColumn('NIP', function ($row) {
                return $row->NIP ?: '-';
            })
            ->editColumn('jabatan', function ($row) {
                return $row->jabatan ?: '-';
            })
            ->editColumn('nohp', function ($row) {
                return $row->nohp ?: '-';
            })
            ->addColumn('aksi', function ($row) {
                $btnTambah = '<a href="#" class="btn btn-primary btn-sm btnTambahPerusahaan" 
                    data-perusahaan_id="' . $row->perusahaan_id . '"
                    data-nama_perusahaan="' . htmlentities($row->nama_perusahaan) . '">Tambah Pembimbing</a>';

                if (!$row->id) {
                    return $btnTambah;
                }

                return $btnTambah . ' <a href="#" class="btn btn-warning btn-sm btnEdit" 
                    data-id="' . $row->id . '"
                    data-nama="' . htmlentities($row->nama_pembimbing) . '"
                    data-nip="' . $row->NIP . '"
                    data-jabatan="' . htmlentities($row->jabatan) . '"
                    data-jenis="' . $row->jenis_kelamin . '"
                    data-nohp="' . $row->nohp . '"
                    data-perusahaan_id="' . $row->perusahaan_id . '"
                    data-nama_perusahaan="' . htmlentities($row->nama_perusahaan) . '">Edit</a>
                    <a href="#" class="btn btn-danger btn-sm btnHapus" data-id="' . $row->id . '">Hapus</a>';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }




    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'nama_pembimbing' => 'required',
            'NIP' => 'required',
            'perusahaan_id' => 'required',
            'jabatan' => 'required',
            'jenis_kelamin' => 'required',
            'nohp' => 'required',


        ]);

        Pembimbing_perusahaan::create([
            'nama_pembimbing' => $request->nama_pembimbing,
            'NIP' => $request->NIP,
            'perusahaan_id' => $request->perusahaan_id,
            'jabatan' => $request->jabatan,
            'jenis_kelamin' => $request->jenis_kelamin,
            'nohp' => $request->nohp,
        ]);

        return response()->json(['success' => 'Data Pembimbing Perusahaan Berhasil Ditambahkan']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $pembimbing_perusahaan = Pembimbing_perusahaan::where('perusahaan_id', $id)->get();
        return response()->json($pembimbing_perusahaan);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        $pembimbing_perusahaan = Pembimbing_perusahaan::findOrFail($id);
        return response()->json($pembimbing_perusahaan);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_pembimbing' => 'required',
            'NIP' => 'required',
            'perusahaan_id' => 'required',
            'jabatan' => 'required',
            'jenis_kelamin' => 'required',
            'nohp' => 'required',
        ]);

        $pembimbing_perusahaan = Pembimbing_perusahaan::findOrFail($id);
        $pembimbing_perusahaan->update([
            'nama_pembimbing' => $request->nama_pembimbing,
            'NIP' => $request->NIP,
            'perusahaan_id' => $request->perusahaan_id,
            'jabatan' => $request->jabatan,
            'jenis_kelamin' => $request->jenis_kelamin,
            'nohp' => $request->nohp,

        ]);

        return response()->json(['success' => 'Data Pembimbing Perusahaan Berhasil Diupdate']);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $pembimbing_perusahaan = Pembimbing_perusahaan::findOrFail($id);
        $pembimbing_perusahaan->delete();

        return response()->json(['success' => 'Data Pembimbing Perusahaan Berhasil Dihapus']);
    }


    public function exportExcel()
    {
        $pembimbing_perusahaan = Pembimbing_perusahaan::with('perusahaan')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Nama Perusahaan');
        $sheet->setCellValue('C1', 'Nama Pembimbing');
        $sheet->setCellValue('D1', 'NIP');
        $sheet->setCellValue('E1', 'Jabatan');
        $sheet->setCellValue('F1', 'Jenis Kelamin');
        $sheet->setCellValue('G1', 'No HP');

        $row = 2;
        foreach ($pembimbing_perusahaan as $pembimbing) {
            $sheet->setCellValue('A' . $row, $row - 1);
            $sheet->setCellValue('B' . $row, $pembimbing->perusahaan->nama_perusahaan);
            $sheet->setCellValue('C' . $row, $pembimbing->nama_pembimbing);
            $sheet->setCellValue('D' . $row, $pembimbing->NIP);
            $sheet->setCellValue('E' . $row, $pembimbing->jabatan);
            $sheet->setCellValue('F' . $row, $pembimbing->jenis_kelamin);
            $sheet->setCellValue('G' . $row, $pembimbing->nohp);
            $row++;
        }

        $this->applyExcelTableFormatting($sheet, 'G', $row - 1);

        $writer = new Xlsx($spreadsheet);
        $filename = 'pembimbing_perusahaan-' . date('Y-m-d') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $file = $request->file('file');
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        foreach ($rows as $row) {
            if ($row[1] != null) {
                Pembimbing_perusahaan::create([
                    'perusahaan_id' => $row[1],
                    'nama_pembimbing' => $row[2],
                    'NIP' => $row[3],
                    'jabatan' => $row[4],
                    'jenis_kelamin' => $row[5],
                    'nohp' => $row[6],
                ]);
            }
        }

        return response()->json(['success' => 'Data Pembimbing Perusahaan Berhasil Diimport']);
    }
}
