<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;

class JurusanController extends Controller
{
    public function index()
    {
        return view('jurusan.index');
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {
            $data = Jurusan::all();
            return DataTables::of($data)
                ->addIndexColumn()
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
                ->rawColumns(['aksi'])
                ->make(true);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_jurusan' => 'required|string|max:255',
            'kode_jurusan' => 'required|string|max:50|unique:jurusan,kode_jurusan',
        ]);

        Jurusan::create([
            'nama_jurusan' => $request->nama_jurusan,
            'kode_jurusan' => $request->kode_jurusan,
        ]);

        return response()->json(['message' => 'Data berhasil disimpan']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_jurusan' => 'required|string|max:255',
            'kode_jurusan' => 'required|string|max:50|unique:jurusan,kode_jurusan,' . $id,
        ]);

        Jurusan::findOrFail($id)->update([
            'nama_jurusan' => $request->nama_jurusan,
            'kode_jurusan' => $request->kode_jurusan,
        ]);

        return response()->json(['message' => 'Data berhasil diupdate']);
    }

    public function edit($id)
    {
        $jurusan = Jurusan::findOrFail($id);
        return response()->json($jurusan);
    }

    public function destroy($id)
    {
        Jurusan::findOrFail($id)->delete();
        return response()->json(['message' => 'Data berhasil dihapus']);
    }

    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv'
            ]);

            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            array_shift($rows);

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($rows as $index => $row) {
                if (empty($row[0]) || empty($row[1])) {
                    $errorCount++;
                    $errors[] = "Baris " . ($index + 2) . ": Data tidak lengkap";
                    continue;
                }

                try {
                    // Check if kode_jurusan already exists
                    $existing = Jurusan::where('kode_jurusan', $row[1])->first();
                    if ($existing) {
                        // Update if exists
                        $existing->update([
                            'nama_jurusan' => $row[0],
                            'kode_jurusan' => $row[1]
                        ]);
                    } else {
                        // Create if not exists
                        Jurusan::create([
                            'nama_jurusan' => $row[0],
                            'kode_jurusan' => $row[1]
                        ]);
                    }
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Baris " . ($index + 2) . ": " . $e->getMessage();
                }
            }

            $message = "Import selesai. Berhasil: $successCount, Gagal: $errorCount";
            if (!empty($errors)) {
                return response()->json(['message' => $message, 'errors' => $errors, 'success' => false], 422);
            }

            return response()->json(['message' => $message, 'success' => true]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set header
        $sheet->setCellValue('A1', 'Nama Jurusan');
        $sheet->setCellValue('B1', 'Kode Jurusan');
        
        // Style header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ];
        $sheet->getStyle('A1:B1')->applyFromArray($headerStyle);
        
        // Add sample data
        $sheet->setCellValue('A2', 'Contoh: Teknik Informatika');
        $sheet->setCellValue('B2', 'TI');
        
        // Set column width
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(20);
        
        // Create writer and response
        $writer = new Xlsx($spreadsheet);
        $fileName = 'template_import_jurusan_' . date('d_m_Y_H_i_s') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}
