<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use Exception;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;

class SiswaController extends Controller
{


    public function index()
    {
        $kelas = Kelas::all();
        $jurusan = Jurusan::all();
        $statusFilter = request('status');
        $jumlahBelumMendaftar = Siswa::where('status', 'belum_terdaftar')->count();

        return view('siswa.index', compact('kelas', 'jurusan', 'statusFilter', 'jumlahBelumMendaftar'));
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {
            $query = Siswa::with('kelas.jurusan');

            if ($request->status === 'belum_terdaftar') {
                $query->where('status', 'belum_terdaftar');
            }

            $data = $query->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="form-check checkbox-siswa" value="' . $row->id . '">';
                })
                ->addColumn('aksi', function ($row) {
                    return '
                        <button class="btn btn-sm btn-warning btnEdit" data-id="' .
                        $row->id .
                        '">Edit</button>
                        <button class="btn btn-sm btn-danger btnHapus" data-id="' .
                        $row->id .
                        '">Hapus</button>
                    ';
                })
                ->rawColumns(['checkbox', 'aksi'])
                ->make(true);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_siswa' => 'required|string|max:255',
            'nis' => 'required|numeric|unique:siswa,nis,',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        Siswa::create([
            'nama_siswa' => $request->nama_siswa,
            'nis' => $request->nis,
            'kelas_id' => $request->kelas_id,
        ]);




        return response()->json(['message' => 'Data berhasil disimpan']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_siswa' => 'required|string|max:255',
            'nis' => 'required|numeric',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        Siswa::findOrFail($id)->update([
            'nama_siswa' => $request->nama_siswa,
            'nis' => $request->nis,
            'kelas_id' => $request->kelas_id,
        ]);

        return response()->json(['message' => 'Data berhasil diupdate']);
    }

    public function edit($id)
    {
        $siswa = Siswa::with('kelas.jurusan')->findOrFail($id);
        return response()->json($siswa);
    }

    public function destroy($id)
    {
        Siswa::findOrFail($id)->delete();
        return response()->json(['message' => 'Data berhasil dihapus']);
    }

    public function destroyMultiple(Request $request)
    {
        try {
            $ids = $request->ids;
            
            if (!is_array($ids) || empty($ids)) {
                return response()->json(['message' => 'Pilih minimal satu data'], 422);
            }

            Siswa::whereIn('id', $ids)->delete();
            
            return response()->json(['message' => 'Data berhasil dihapus (' . count($ids) . ' data)']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function import(Request $request)
    {
        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        array_shift($rows);

        foreach ($rows as $row) {
            Siswa::create([
                'nama_siswa' => $row[0],
                'nis' => $row[1],
                'kelas_id' => $row[2]
            ]);
        }
        return response()->json(['message' => 'Data berhasil diimport']);
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set header
        $sheet->setCellValue('A1', 'Nama Siswa');
        $sheet->setCellValue('B1', 'NIS');
        $sheet->setCellValue('C1', 'Kelas ID');
        
        // Style header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ];
        $sheet->getStyle('A1:C1')->applyFromArray($headerStyle);
        
        // Add sample data
        $sheet->setCellValue('A2', 'Contoh: Budi Santoso');
        $sheet->setCellValue('B2', '2024001');
        $sheet->setCellValue('C2', '1');
        
        // Set column width
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(15);
        
        // Create writer and response
        $writer = new Xlsx($spreadsheet);
        $fileName = 'template_import_siswa_' . date('d_m_Y_H_i_s') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}
