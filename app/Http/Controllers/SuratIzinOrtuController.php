<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\SuratIzinOrtu;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;

class SuratIzinOrtuController extends Controller
{
    public function index()
    {
        $siswa = Siswa::whereHas('kelas.jurusan', function ($query) {
            $query->where('id', auth()->user()->jurusan_id);
        })->get();
        return view('surat_izin_ortu.index', compact('siswa'));
    }

    public function data()
    {
        $data = SuratIzinOrtu::with('siswa')->latest();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('aksi', function ($row) {
                return '
                    <button class="btn btn-sm btn-warning btn-edit"
                        data-id="' . $row->id . '"
                        data-siswa="' . $row->siswa_id . '"
                        data-ortu="' . $row->nama_ortu . '"
                        data-alamat="' . $row->alamat_ortu . '"
                        data-nohp_ortu="' . $row->siswa->no_hp_ortu . '"
                        data-nohp_siswa="' . $row->siswa->no_hp_siswa . '">
                        Edit
                    </button>
                     <a href="' . route('izin-ortu.cetak', $row->id) . '" class="btn btn-sm btn-primary" target="_blank">Cetak</a>
                    <button class="btn btn-sm btn-danger btn-hapus" 
                    data-id="' . $row->id . '"
                    data-siswa="' . $row->siswa_id . '"
                    >Hapus</button>
                ';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'nama_ortu' => 'required|string',
            'alamat_ortu' => 'required|string',
            'no_hp_ortu' => 'required|string',
            'no_hp_siswa' => 'required|string',
        ]);



        SuratIzinOrtu::create($request->all());

        Siswa::where('id', $request->siswa_id)->update([
            'nama_ortu' => $request->nama_ortu,
            'alamat_ortu' => $request->alamat_ortu,
            'no_hp_ortu' => $request->no_hp_ortu,
            'no_hp_siswa' => $request->no_hp_siswa,
            'status' => 'cetak_surat_izin_ortu',
        ]);

        return response()->json(['message' => 'Surat Izin ditambahkan']);
    }

    public function update(Request $request, $id)
    {
        $izin = SuratIzinOrtu::findOrFail($id);

        $izin->update($request->all());

        Siswa::where('id', $request->siswa_id)->update([
            'nama_ortu' => $request->nama_ortu,
            'alamat_ortu' => $request->alamat_ortu,
            'no_hp_ortu' => $request->no_hp_ortu,
            'no_hp_siswa' => $request->no_hp_siswa,
            'status' => 'cetak_surat_izin_ortu',
        ]);

        return response()->json(['message' => 'Surat Izin diperbarui']);
    }

    public function destroy(Request $request, $id)
    {
        SuratIzinOrtu::findOrFail($id)->delete();
        Siswa::where('id', $request->siswa_id)->update(['status' => 'belum_terdaftar']);
        return response()->json(
            [
                'message' => 'Data dihapus',
            ],
        );

        // return response()->json(['message' => 'Data dihapus']);
    }

    public function cetak($id)
    {
        $izin = SuratIzinOrtu::with('siswa.kelas.jurusan')->findOrFail($id);
        $pdf = Pdf::loadView('surat_izin_ortu.cetak', compact('izin'))->setPaper('A4', 'portrait');
        return $pdf->stream('Surat_Izin_Orangtua_' . $izin->siswa->nama_siswa . '.pdf');
    }
}
