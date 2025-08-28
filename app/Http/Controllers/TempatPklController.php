<?php

namespace App\Http\Controllers;

use App\Models\TempatPkl;
use App\Models\Siswa;
use App\Models\Perusahaan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;


class TempatPklController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Grouping by perusahaan
            if (auth()->user()->role == 'panitia') {
                $grouped = TempatPkl::with(['siswa', 'perusahaan'])
                    ->get();
            } else {
                $grouped = TempatPkl::with(['siswa', 'perusahaan'])
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

        $siswa = Siswa::whereHas('kelas.jurusan', function ($query) {
            $query->where('id', auth()->user()->jurusan_id);
            $query->where('status', '!=', 'belum_terdaftar');
        })->get();
        $perusahaan = Perusahaan::orderBy('nama_perusahaan')->get();
        //   var_dump($siswa);
        return view('tempat_pkl.index', compact('siswa', 'perusahaan'));
    }


    public function exportExcel()
    {
        $data = TempatPkl::with(['siswa.kelas', 'perusahaan'])
            ->distinct('nis')
            ->get();
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Tidak ada data untuk diekspor'], 404);
        }
        // Buat spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header
        $headers = ['Nama Siswa', 'NISN', 'Kelas', 'Perusahaan', 'Alamat', 'Tanggal Daftar'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        // Data
        $data = \App\Models\TempatPkl::with('siswa.kelas', 'perusahaan')->get();
        $row = 2;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item->siswa->nama_siswa ?? '-');

            // Format NISN sebagai teks agar 0 di depan tidak hilang
            $sheet->setCellValueExplicit('B' . $row, $item->siswa->nis ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            $sheet->setCellValue('C' . $row, $item->siswa->kelas->nama_kelas ?? '-');
            $sheet->setCellValue('D' . $row, $item->perusahaan->nama_perusahaan ?? '-');
            $sheet->setCellValue('E' . $row, $item->perusahaan->alamat ?? '-');
            $sheet->setCellValue('F' . $row, $item->created_at->format('Y-m-d') ?? '-');
            $row++;
        }

        // Autofilter
        $sheet->setAutoFilter('A1:F1');

        // Autosize kolom
        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Style header
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9D9D9']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ]
        ];
        $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

        // Border seluruh data
        $sheet->getStyle('A1:F' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Simpan dan download
        $fileName = 'data_tempat_pkl_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        $filePath = storage_path('app/public/' . $fileName);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function upload_kesediaan()
    {
        return view('tempat_pkl.upload_kesediaan');
    }

    public function update_kesediaan(Request $request, $id)
    {
        $request->validate([
            'nama_pembimbing' => 'required|string|max:255',
            'jabatan_pembimbing' => 'required|string|max:255',
            'no_hp_pembimbing' => 'required|string|max:15',
            'NIP_pembimbing' => 'nullable|string|max:20',
            'tugas_siswa' => 'nullable|string',
        ]);

        $filepath = null;
        if ($request->hasFile('file_upload_kesediaan')) {
            $file = $request->file('file_upload_kesediaan');
            $namaFile = time() . '_' . $file->getClientOriginalName();
            $filepath = $file->storeAs('surat-kesediaan', $namaFile, 'public');
            $request->merge(['surat_kesediaan_path' => $filepath]);
        }

        $data = TempatPkl::findOrFail($id);
        $data->update([
            'nama_pembimbing' => $request->nama_pembimbing,
            'jabatan_pembimbing' => $request->jabatan_pembimbing,
            'no_hp_pembimbing' => $request->no_hp_pembimbing,
            'NIP_pembimbing' => $request->NIP_pembimbing,
            'tugas_siswa' => $request->tugas_siswa,
            'surat_kesediaan_path' => $filepath,
            'status' => 'Sudah Menerima Balasan'
        ]);

        return response()->json(['message' => 'Kesediaan berhasil diupdate']);
    }



    public function index_cetak(Request $request)
    {
        if ($request->ajax()) {
            // Grouping by perusahaan
            $grouped = TempatPkl::with(['siswa.kelas', 'perusahaan'])
                ->get()
                ->groupBy('perusahaan_id')
                ->map(function ($group) {
                    return [
                        'id' => $group->first()->perusahaan_id,
                        'perusahaan' => $group->first()->perusahaan->nama_perusahaan,
                        'siswa' => $group->pluck('siswa.nama_siswa')->implode(', '),
                        'tanggal_mulai' => $group->first()->tanggal_mulai,
                        'tanggal_selesai' => $group->first()->tanggal_selesai
                    ];
                })->values();

            return DataTables::of($grouped)
                ->addIndexColumn()
                ->addColumn('aksi', function ($row) {
                    return '
                        <a href="' . route('tempat-pkl.cetak', $row['id']) . '" target="_blank" class="btn btn-sm btn-primary">Cetak</a>
                        <a href="' . route('tempat-pkl.cetak-amplop', $row['id']) . '" target="_blank" class="btn btn-sm btn-success">Cetak Amplop</a>
                        <a href="' . route('tempat-pkl.cetak-amplop-word', $row['id']) . '" target="_blank" class="btn btn-sm btn-info">Cetak Word</a>
                        ';
                })
                ->rawColumns(['aksi'])
                ->make(true);
        }

        $siswa = Siswa::all();
        $perusahaan = Perusahaan::all();
        return view('tempat_pkl.index_cetak', compact('siswa', 'perusahaan'));
    }

    public function store(Request $request)
    {
        //tampilkan seluruh isi request
        // $request->all();


        $request->validate([
            'perusahaan_id' => 'required',
            'siswa_id' => 'required|array|min:1',
            'siswa_id.*' => 'required|exists:siswa,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'created_by' => 'required',

        ]);

        $filePath = null;
        if ($request->hasFile('file_upload')) {
            $file = $request->file('file_upload');
            $namaFile = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('surat-izin', $namaFile, 'public');

            $request->merge(['surat_izin_path' => $filePath]);
        }


        if ($request->perusahaan_id == 0) {
            Perusahaan::create([
                'nama_perusahaan' => $request->nama_perusahaan,
                'alamat' => $request->alamat_perusahaan,
            ]);
            $request->merge(['perusahaan_id' => Perusahaan::latest()->first()->id]);
        }

        $siswaIds = $request->siswa_id;
        foreach ($siswaIds as $siswaId) {
            TempatPkl::create([
                'perusahaan_id' => $request->perusahaan_id,
                'siswa_id' => $siswaId,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'created_by' => $request->created_by,
                'surat_izin_path' => $filePath
            ]);
        }

        foreach ($siswaIds as $siswaId) {
            Siswa::where('id', $siswaId)->update([
                'status' => 'Mendaftar_perusahaan'
            ]);
        }

        return response()->json(['message' => 'Data tempat PKL berhasil disimpan']);
    }


    public function editPembimbing(Request $request, $id)
    {
        $data = TempatPkl::where('perusahaan_id', $id)->get();
        foreach ($data as $tempatPkl) {
            $tempatPkl->update([
                'pembimbing_id' => $request->pembimbing_id
            ]);
        }

        return response()->json($data);
    }

    public function edit($id)
    {
        $data = TempatPkl::findOrFail($id);
        return response()->json($data);
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'perusahaan_id' => 'required',
            'siswa_id' => 'required',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $siswaId = (int) $request->siswa_id[0];

        $data = TempatPkl::findOrFail($id);
        $data->update([
            'perusahaan_id' => $request->perusahaan_id,
            'siswa_id' => $siswaId,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
        ]);

        // echo $request->perusahaan_id;

        return response()->json(['message' => 'Data tempat PKL berhasil diupdate']);
    }

    public function destroy($id)
    {
        $suratIzinPath = TempatPkl::where('id', $id)->value('surat_izin_path');

        if ($suratIzinPath != null) {
            if (Storage::disk('public')->exists($suratIzinPath)) {
                Storage::disk('public')->delete($suratIzinPath);
            }
        }




        $data = TempatPkl::findOrFail($id);
        $data->delete();




        return response()->json(['success' => true]);
    }

    public function cetak($id)
    {
        $data = TempatPkl::with(['siswa.kelas.jurusan', 'perusahaan'])
            ->where('perusahaan_id', $id)
            ->get();

        $pdf = pdf::loadView('tempat_pkl.cetak', compact('data'));
        return $pdf->stream('surat-izin-pkl.pdf'); // atau ->download('namafile.pdf');
    }

    public function cetakAmplop($id)
    {
        $data = TempatPkl::with(['siswa.kelas.jurusan', 'perusahaan'])
            ->where('perusahaan_id', $id)
            ->get();

        $pdf = Pdf::loadView('tempat_pkl.cetak_amplop', compact('data'));
        $pdf->setPaper([0, 0, 650, 312], 'potrait'); // ukuran 23 x 11 cm

        return $pdf->stream('amplop.pdf');
    }

    public function cetakAmplopWord($id)
    {
        $data = TempatPkl::with('perusahaan')->findOrFail($id);

        // Pastikan ini benar
        $phpWord = new PhpWord();

        // Set ukuran halaman landscape 23cm x 11cm
        $section = $phpWord->addSection([
            'pageSizeW' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(23),
            'pageSizeH' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(11),
            'orientation' => 'landscape',
            'marginLeft' => 600,
            'marginRight' => 600,
            'marginTop' => 300,
            'marginBottom' => 300,
        ]);

        $section->addText("Kepada Yth:");
        $section->addText($data->perusahaan->nama_perusahaan);
        $section->addText($data->perusahaan->alamat);

        $fileName = 'amplop_' . time() . '.docx';
        $path = storage_path('app/public/' . $fileName);

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($path);

        return response()->download($path)->deleteFileAfterSend(true);
    }
}
