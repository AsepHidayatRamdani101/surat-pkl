<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FormatsExcelSheets;
use App\Models\Kelas;
use App\Support\RekapNilaiSiswa;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class WaliKelasController extends Controller
{
    use FormatsExcelSheets;

    private const HEADERS = [
        'No', 'NIS', 'Nama Siswa', 'Kelas', 'Hadir', 'Izin', 'Sakit', 'Alpa',
        'Tugas Dinilai', 'Rata-rata Tugas', 'Sikap', 'Nilai Praktik PKL', 'Nilai Sidang', 'Predikat Sidang',
    ];

    public function index(Request $request, RekapNilaiSiswa $rekap)
    {
        $kelasList = $this->kelasDiampu($request);
        $kelas = $this->pilihKelas($request, $kelasList);
        $rows = $kelas ? $rekap->forKelas($kelas->id) : collect();

        return view('wali_kelas.index', [
            'kelasList' => $kelasList,
            'kelas' => $kelas,
            'rows' => $rows,
            'ringkasan' => [
                'siswa' => $rows->count(),
                'tugas' => $this->rata($rows, 'tugas_rata'),
                'sidang' => $this->rata($rows, 'sidang'),
                'sidangSelesai' => $rows->whereNotNull('sidang')->count(),
            ],
        ]);
    }

    public function export(Request $request, RekapNilaiSiswa $rekap)
    {
        $kelas = $this->pilihKelas($request, $this->kelasDiampu($request));
        abort_if(!$kelas, 404, 'Kelas tidak ditemukan.');

        $rows = $rekap->forKelas($kelas->id);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr(preg_replace('/[\\\\\/\?\*\[\]:]/', '-', $kelas->nama_kelas), 0, 31));

        foreach (self::HEADERS as $index => $header) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($index + 1) . '1', $header);
        }

        $line = 2;
        foreach ($rows as $index => $row) {
            $values = [
                $index + 1, $row['nis'], $row['nama'], $row['kelas'],
                $row['hadir'], $row['izin'], $row['sakit'], $row['alpa'],
                $row['tugas_dinilai'], $row['tugas_rata'] ?? '-', $row['sikap'] ?? '-',
                $row['praktik'] ?? '-', $row['sidang'] ?? '-', $row['predikat'] ?? '-',
            ];
            foreach ($values as $col => $value) {
                $sheet->setCellValueExplicit(
                    Coordinate::stringFromColumnIndex($col + 1) . $line,
                    $value,
                    is_int($value) || is_float($value) ? DataType::TYPE_NUMERIC : DataType::TYPE_STRING
                );
            }
            $line++;
        }

        $lastColumn = Coordinate::stringFromColumnIndex(count(self::HEADERS));
        $this->applyExcelTableFormatting($sheet, $lastColumn, max($line - 1, 1));

        $filename = 'nilai-siswa-kelas-' . preg_replace('/[^A-Za-z0-9]+/', '-', $kelas->nama_kelas) . '-' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function kelasDiampu(Request $request)
    {
        $ids = $request->attributes->get('wali_kelas_ids', []);

        return Kelas::query()->with('jurusan')->whereIn('id', $ids)->orderBy('nama_kelas')->get();
    }

    // Kelas yang dipilih harus termasuk kelas yang diampu; selain itu ditolak.
    private function pilihKelas(Request $request, $kelasList): ?Kelas
    {
        if ($request->filled('kelas_id')) {
            $kelas = $kelasList->firstWhere('id', (int) $request->kelas_id);
            abort_if(!$kelas, 403, 'Anda bukan wali kelas dari kelas tersebut.');

            return $kelas;
        }

        return $kelasList->first();
    }

    private function rata($rows, string $key): ?float
    {
        $nilai = $rows->pluck($key)->filter(fn ($v) => $v !== null);

        return $nilai->isEmpty() ? null : round((float) $nilai->avg(), 2);
    }
}
