<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FormatsExcelSheets;
use App\Models\Kelas;
use App\Models\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;

class KelasController extends Controller
{
    use FormatsExcelSheets;

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
            'tingkat' => 'required|integer|between:11,13',
            'jumlah_rombel' => 'required|integer|min:0',
        ]);

        Kelas::create([
            'nama_kelas' => $request->nama_kelas,
            'jurusan_id' => $request->jurusan_id,
            'tingkat' => $request->tingkat,
            'jumlah_rombel' => $request->jumlah_rombel,
        ]);

        return response()->json(['message' => 'Data berhasil disimpan']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kelas' => 'required|string|max:255',
            'jurusan_id' => 'required|exists:jurusan,id',
            'tingkat' => 'required|integer|between:11,13',
            'jumlah_rombel' => 'required|integer|min:0',
        ]);

        Kelas::findOrFail($id)->update([
            'nama_kelas' => $request->nama_kelas,
            'jurusan_id' => $request->jurusan_id,
            'tingkat' => $request->tingkat,
            'jumlah_rombel' => $request->jumlah_rombel,
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

    public function switchXiToXii()
    {
        return $this->switchTingkatKelas(11, 12, 'XI', 'XII');
    }

    public function switchXiiToXi()
    {
        return $this->switchTingkatKelas(12, 11, 'XII', 'XI');
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
                if (empty($row[0]) || empty($row[1]) || empty($row[2]) || ($row[3] ?? '') === '') {
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
                            'tingkat' => (int)$row[2],
                            'jumlah_rombel' => (int)$row[3],
                        ]);
                    } else {
                        // Create if not exists
                        Kelas::create([
                            'nama_kelas' => $row[0],
                            'jurusan_id' => $jurusan->id,
                            'tingkat' => (int)$row[2],
                            'jumlah_rombel' => (int)$row[3],
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

        $sheet->setCellValue('A1', 'Nama Kelas');
        $sheet->setCellValue('B1', 'Nama Jurusan');
        $sheet->setCellValue('C1', 'Tingkat');
        $sheet->setCellValue('D1', 'Jumlah Rombel');

        $sheet->setCellValue('A2', 'Contoh: X-TI-1');
        $sheet->setCellValue('B2', 'Teknik Informatika');
        $sheet->setCellValue('C2', '1');
        $sheet->setCellValue('D2', '3');

        $this->applyExcelTableFormatting($sheet, 'D', 2);

        $writer = new Xlsx($spreadsheet);
        $fileName = 'template_import_kelas_' . date('d_m_Y_H_i_s') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    private function switchTingkatKelas(int $fromTingkat, int $toTingkat, string $fromLabel, string $toLabel)
    {
        $kelasList = Kelas::where('tingkat', $fromTingkat)->get();

        if ($kelasList->isEmpty()) {
            return response()->json([
                'message' => "Tidak ada data kelas tingkat {$fromTingkat} yang bisa diubah."
            ], 422);
        }

        DB::transaction(function () use ($kelasList, $fromTingkat, $toTingkat, $fromLabel, $toLabel) {
            foreach ($kelasList as $kelas) {
                $namaKelasBaru = preg_replace('/\\b' . preg_quote($fromLabel, '/') . '\\b/u', $toLabel, $kelas->nama_kelas);

                if ($namaKelasBaru === $kelas->nama_kelas) {
                    $namaKelasBaru = preg_replace('/\\b' . $fromTingkat . '\\b/u', (string) $toTingkat, $kelas->nama_kelas);
                }

                $kelas->update([
                    'nama_kelas' => $namaKelasBaru,
                    'tingkat' => $toTingkat,
                ]);
            }
        });

        return response()->json([
            'message' => "Data kelas {$fromLabel} berhasil diubah menjadi kelas {$toLabel}.",
            'jumlah' => $kelasList->count(),
        ]);
    }
}
