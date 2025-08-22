<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use Exception;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Yajra\DataTables\Facades\DataTables;

class SiswaController extends Controller
{


    public function index()
    {
        $kelas = Kelas::all();
        $jurusan = Jurusan::all();
        return view('siswa.index', compact('kelas', 'jurusan'));
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {
            $data = Siswa::with('kelas.jurusan')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('aksi', function ($row) {
                    return '
                        <button class="btn btn-warning btnEdit" data-id="' .
                        $row->id .
                        '">Edit</button>
                        <button class="btn btn-danger btnHapus" data-id="' .
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
}
