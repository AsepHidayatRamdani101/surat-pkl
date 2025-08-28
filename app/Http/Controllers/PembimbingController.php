<?php

namespace App\Http\Controllers;

use App\Models\Pembimbing;
use Illuminate\Http\Request;
use Yajra\DataTables\Contracts\DataTable;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PembimbingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pembimbing = Pembimbing::all();
        return view('pembimbing.index', compact('pembimbing'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pembimbing.create');
    }

    public function data(Request $request)
    {
        $perusahaan = Pembimbing::all();

        return DataTables::of($perusahaan)
            ->addColumn('aksi', function ($perusahaan) {
                return '
                    <a href="javascript:void(0)" class="btn btn-sm btn-warning btnEdit" 
                    data-id="' . $perusahaan->id . '"
                    data-nama="' . $perusahaan->nama_pembimbing . '"
                      data-nip="' . $perusahaan->nip_pembimbing . '"
                      data-jabatan="' . $perusahaan->jabatan_pembimbing . '"
                      data-jenis="' . $perusahaan->jenis_kelamin . '"
                      data-nohp="' . $perusahaan->no_hp_pembimbing . '"                  
                    >Edit</a>
                    <a href="javascript:void(0)" class="btn btn-sm btn-danger btnHapus" data-id="' . $perusahaan->id . '">Hapus</a>
                ';
            })
            ->addIndexColumn()
            ->rawColumns(['aksi'])
            ->make(true);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_pembimbing' => 'required',
            'nip_pembimbing' => 'required',
            'jenis_kelamin' => 'required',
            'jabatan_pembimbing' => 'required',
            'no_hp_pembimbing' => 'required',
        ]);

        Pembimbing::create([
            'nama_pembimbing' => $request->nama_pembimbing,
            'nip_pembimbing' => $request->nip_pembimbing,
            'jenis_kelamin' => $request->jenis_kelamin,
            'jabatan_pembimbing' => $request->jabatan_pembimbing,
            'no_hp_pembimbing' => $request->no_hp_pembimbing,

        ]);

        return redirect()->route('pembimbing.index')->with('success', 'Data Pembimbing Berhasil Ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $pembimbing = Pembimbing::findOrFail($id);
        return response()->json($pembimbing);
    }


    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        $file = $request->file('file');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = array_map(function ($v) {
            return array_values($v);
        }, array_slice($sheet->toArray(), 1));

        foreach ($rows as $row) {
            if (!empty($row[0])) {
                $pembimbing = new Pembimbing([
                    'nama_pembimbing' => $row[0],
                    'nip_pembimbing' => $row[1],
                    'jenis_kelamin' => $row[2],
                    'jabatan_pembimbing' => $row[3],
                    'no_hp_pembimbing' => $row[4],
                ]);
                $pembimbing->save();
            }
        }

        return redirect()->back()->with('success', 'Data Pembimbing Berhasil Diimport');
    }

    public function exportExcel()
    {
        $pembimbing = Pembimbing::all();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Nama');
        $sheet->setCellValue('B1', 'NIP');
        $sheet->setCellValue('C1', 'Jenis Kelamin');
        $sheet->setCellValue('D1', 'Jabatan');
        $sheet->setCellValue('E1', 'No HP');
        $row = 2;
        foreach ($pembimbing as $p) {
            $sheet->setCellValue('A' . $row, $p->nama_pembimbing);
            $sheet->setCellValue('B' . $row, $p->nip_pembimbing);
            $sheet->setCellValue('C' . $row, $p->jenis_kelamin);
            $sheet->setCellValue('D' . $row, $p->jabatan_pembimbing);
            $sheet->setCellValue('E' . $row, $p->no_hp_pembimbing);
            $row++;
        }
        $writer = new Xlsx($spreadsheet);
        $filename = 'pembimbing.xlsx';
        $writer->save($filename);
        return response()->download('pembimbing.xlsx')->deleteFileAfterSend(true);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        $pembimbing = Pembimbing::findOrFail($id);
        return response()->json($pembimbing);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_pembimbing' => 'required',
            'nip_pembimbing' => 'required',
            'jenis_kelamin' => 'required',
            'jabatan_pembimbing' => 'required',
            'no_hp_pembimbing' => 'required',
        ]);
        $pembimbing = Pembimbing::findOrFail($id);
        $pembimbing->update([
            'nama_pembimbing' => $request->nama_pembimbing,
            'nip_pembimbing' => $request->nip_pembimbing,
            'jenis_kelamin' => $request->jenis_kelamin,
            'jabatan_pembimbing' => $request->jabatan_pembimbing,
            'no_hp_pembimbing' => $request->no_hp_pembimbing,
        ]);

        return response()->json(['success' => 'Data Pembimbing Berhasil Diupdate']);
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $pembimbing = Pembimbing::findOrFail($id);
        $pembimbing->delete();
        return response()->json(['success' => 'Data Pembimbing Berhasil Dihapus']);
    }
}
