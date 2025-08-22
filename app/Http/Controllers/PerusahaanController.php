<?php

namespace App\Http\Controllers;

use App\Models\Perusahaan;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PerusahaanController extends Controller
{
    
    public function index()
    {
        $perusahaan = Perusahaan::all();
        return view('perusahaan.index', compact('perusahaan'));
    }

    public function data(Request $request)
    {
        $perusahaan = Perusahaan::all();

        return DataTables::of($perusahaan)
            ->addColumn('aksi', function ($perusahaan) {
                return '
                    <a href="javascript:void(0)" class="btn btn-sm btn-warning btnEdit" 
                    data-id="' . $perusahaan->id . '"
                    data-nama="' . $perusahaan->nama_perusahaan . '"
                    data-alamat="' . $perusahaan->alamat . '"                    
                    >Edit</a>
                    <a href="javascript:void(0)" class="btn btn-sm btn-danger btnHapus" data-id="' . $perusahaan->id . '">Hapus</a>
                ';
            })
            ->addIndexColumn()
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_perusahaan' => 'required',
            'alamat' => 'required',
        ]);

        Perusahaan::create([
            'nama_perusahaan' => $request->nama_perusahaan,
            'alamat' => $request->alamat,
        ]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_perusahaan' => 'required',
            'alamat' => 'required',
        ]);

        $perusahaan = Perusahaan::find($id);
        $perusahaan->update([
            'nama_perusahaan' => $request->nama_perusahaan,
            'alamat' => $request->alamat,
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, $id)
    {
        $perusahaan = Perusahaan::find($id);
        $perusahaan->delete();

        return response()->json(['success' => true]);
    }
}
