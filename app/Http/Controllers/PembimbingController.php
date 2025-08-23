<?php

namespace App\Http\Controllers;

use App\Models\Pembimbing;
use Illuminate\Http\Request;
use Yajra\DataTables\Contracts\DataTable;
use Yajra\DataTables\Facades\DataTables;

class PembimbingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pembimbing = Pembimbing::all();
        return view('pembimbing.index', compact('pembimbing'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pembimbing.create');
    }

    public function data(Request $request)
    {
         $perusahaan = Pembimbing::all();

        return DataTables::of($perusahaan)
            ->addColumn('aksi', function ($perusahaan) {
                return '
                    <a href="javascript:void(0)" class="btn btn-sm btn-warning btnEdit" 
                    data-id="' . $perusahaan->id . '"
                    data-nama="' . $perusahaan->nama_pembimbing . '"
                      data-nip="' . $perusahaan->nip_pembimbing . '"
                      data-jabatan="' . $perusahaan->jabatan_pembimbing . '"
                      data-jenis="' . $perusahaan->jenis_kelamin . '"
                      data-nohp="' . $perusahaan->no_hp_pembimbing . '"                  
                    >Edit</a>
                    <a href="javascript:void(0)" class="btn btn-sm btn-danger btnHapus" data-id="' . $perusahaan->id . '">Hapus</a>
                ';
            })
            ->addIndexColumn()
            ->rawColumns(['aksi'])
            ->make(true);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_pembimbing' => 'required',
            'nip_pembimbing' => 'required',
            'jenis_kelamin' => 'required',
            'jabatan_pembimbing' => 'required',
            'no_hp_pembimbing' => 'required',
        ]);

        Pembimbing::create([
            'nama_pembimbing' => $request->nama_pembimbing,
            'nip_pembimbing' => $request->nip_pembimbing,
            'jenis_kelamin' => $request->jenis_kelamin,
            'jabatan_pembimbing' => $request->jabatan_pembimbing,
            'no_hp_pembimbing' => $request->no_hp_pembimbing,
           
        ]);

        return redirect()->route('pembimbing.index')->with('success', 'Data Pembimbing Berhasil Ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $pembimbing = Pembimbing::findOrFail($id);        
        return response()->json($pembimbing);
    }
   

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        $pembimbing = Pembimbing::findOrFail($id);
        return response()->json($pembimbing);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_pembimbing' => 'required',
            'nip_pembimbing' => 'required',
            'jenis_kelamin' => 'required',
            'jabatan_pembimbing' => 'required',
            'no_hp_pembimbing' => 'required',
        ]);
        $pembimbing = Pembimbing::findOrFail($id);
        $pembimbing->update([
            'nama_pembimbing' => $request->nama_pembimbing,
            'nip_pembimbing' => $request->nip_pembimbing,
            'jenis_kelamin' => $request->jenis_kelamin,
            'jabatan_pembimbing' => $request->jabatan_pembimbing,
            'no_hp_pembimbing' => $request->no_hp_pembimbing,
        ]);

        return response()->json(['success' => 'Data Pembimbing Berhasil Diupdate']);
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $pembimbing = Pembimbing::findOrFail($id);
        $pembimbing->delete();
        return response()->json(['success' => 'Data Pembimbing Berhasil Dihapus']);
    }
}

