<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Jurusan;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;

class KelasController extends Controller
{
    public function index()
    {
        $jurusan = Jurusan::all();
        return view('kelas.index', compact('jurusan'));
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {
            $data = Kelas::with('jurusan')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('jurusan_nama', function ($row) {
                    return $row->jurusan->nama_jurusan ?? '-';
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
                ->rawColumns(['aksi'])
                ->make(true);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kelas' => 'required|string|max:255',
            'jurusan_id' => 'required|exists:jurusan,id',
            'tingkat' => 'required|integer|between:1,4',
        ]);

        Kelas::create([
            'nama_kelas' => $request->nama_kelas,
            'jurusan_id' => $request->jurusan_id,
            'tingkat' => $request->tingkat,
        ]);

        return response()->json(['message' => 'Data berhasil disimpan']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kelas' => 'required|string|max:255',
            'jurusan_id' => 'required|exists:jurusan,id',
            'tingkat' => 'required|integer|between:1,4',
        ]);

        Kelas::findOrFail($id)->update([
            'nama_kelas' => $request->nama_kelas,
            'jurusan_id' => $request->jurusan_id,
            'tingkat' => $request->tingkat,
        ]);

        return response()->json(['message' => 'Data berhasil diupdate']);
    }

    public function edit($id)
    {
        $kelas = Kelas::with('jurusan')->findOrFail($id);
        return response()->json($kelas);
    }

    public function destroy($id)
    {
        Kelas::findOrFail($id)->delete();
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
                if (empty($row[0]) || empty($row[1]) || empty($row[2])) {
                    $errorCount++;
                    $errors[] = "Baris " . ($index + 2) . ": Data tidak lengkap";
                    continue;
                }

                try {
                    $jurusan = Jurusan::where('nama_jurusan', $row[1])->first();
                    if (!$jurusan) {
                        $errorCount++;
                        $errors[] = "Baris " . ($index + 2) . ": Jurusan '{$row[1]}' tidak ditemukan";
                        continue;
                    }

                    $existing = Kelas::where('nama_kelas', $row[0])->where('jurusan_id', $jurusan->id)->first();
                    if ($existing) {
                        // Update if exists
                        $existing->update([
                            'nama_kelas' => $row[0],
                            'jurusan_id' => $jurusan->id,
                            'tingkat' => (int)$row[2]
                        ]);
                    } else {
                        // Create if not exists
                        Kelas::create([
                            'nama_kelas' => $row[0],
                            'jurusan_id' => $jurusan->id,
                            'tingkat' => (int)$row[2]
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
        $sheet->setCellValue('A1', 'Nama Kelas');
        $sheet->setCellValue('B1', 'Nama Jurusan');
        $sheet->setCellValue('C1', 'Tingkat');
        
        // Style header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ];
        $sheet->getStyle('A1:C1')->applyFromArray($headerStyle);
        
        // Add sample data
        $sheet->setCellValue('A2', 'Contoh: X-TI-1');
        $sheet->setCellValue('B2', 'Teknik Informatika');
        $sheet->setCellValue('C2', '1');
        
        // Set column width
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(15);
        
        // Create writer and response
        $writer = new Xlsx($spreadsheet);
        $fileName = 'template_import_kelas_' . date('d_m_Y_H_i_s') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}
