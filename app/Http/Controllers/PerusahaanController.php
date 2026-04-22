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
            ->addColumn('checkbox', function ($perusahaan) {
                return '<input type="checkbox" class="form-check checkbox-perusahaan" value="' . $perusahaan->id . '">';
            })
            ->addColumn('aksi', function ($perusahaan) {
                return '
                    <a href="javascript:void(0)" class="btn btn-sm btn-warning btnEdit" 
                    data-id="' . $perusahaan->id . '"
                    data-nama="' . $perusahaan->nama_perusahaan . '"
                    data-alamat="' . $perusahaan->alamat . '"
                    data-nama-pemilik="' . $perusahaan->nama_pemilik_perusahaan . '"
                    data-telepon-pemilik="' . $perusahaan->telepon_pemilik_perusahaan . '"
                    >Edit</a>
                    <a href="javascript:void(0)" class="btn btn-sm btn-danger btnHapus" data-id="' . $perusahaan->id . '">Hapus</a>
                ';
            })
            ->addIndexColumn()
            ->rawColumns(['checkbox', 'aksi'])
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
            'nama_pemilik_perusahaan' => $request->nama_pemilik_perusahaan,
            'telepon_pemilik_perusahaan' => $request->telepon_pemilik_perusahaan,
            'alamat' => $request->alamat,
        ]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_perusahaan' => 'required',
            'nama_pemilik_perusahaan' => 'required',
            'telepon_pemilik_perusahaan' => 'required',
            'alamat' => 'required',
        ]);

        $perusahaan = Perusahaan::find($id);
        $perusahaan->update([
            'nama_perusahaan' => $request->nama_perusahaan,
            'nama_pemilik_perusahaan' => $request->nama_pemilik_perusahaan,
            'telepon_pemilik_perusahaan' => $request->telepon_pemilik_perusahaan,
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

    public function destroyMultiple(Request $request)
    {
        try {
            $ids = $request->ids;
            
            if (!is_array($ids) || empty($ids)) {
                return response()->json(['message' => 'Pilih minimal satu data'], 422);
            }

            Perusahaan::whereIn('id', $ids)->delete();
            
            return response()->json(['message' => 'Data berhasil dihapus (' . count($ids) . ' data)']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
