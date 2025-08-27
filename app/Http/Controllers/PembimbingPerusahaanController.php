<?php

namespace App\Http\Controllers;

use App\Models\Pembimbing_perusahaan;
use App\Models\Perusahaan;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class PembimbingPerusahaanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $perusahaan = Perusahaan::orderBy('nama_perusahaan', 'asc')->get();
        $pembimbing_perusahaan = Pembimbing_perusahaan::all();

        return view('pembimbing_perusahaan.index', compact('pembimbing_perusahaan', 'perusahaan'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function data(Request $request)
    {
        $pembimbing_perusahaan = Pembimbing_perusahaan::with('perusahaan')->get();
        return DataTables::of($pembimbing_perusahaan)
            ->addIndexColumn()
            ->addColumn('aksi', function ($pembimbing_perusahaan) {
                return '<a href="#" class="btn btn-warning btn-sm btnEdit" 
                data-id="' . $pembimbing_perusahaan->id . '"
                data-nama="' . $pembimbing_perusahaan->nama_pembimbing . '"
                data-nip="' . $pembimbing_perusahaan->NIP . '"
                data-jabatan="' . $pembimbing_perusahaan->jabatan . '"
                data-jenis="' . $pembimbing_perusahaan->jenis_kelamin . '"
                data-nohp="' . $pembimbing_perusahaan->nohp . '"
                data-perusahaan_id="' . $pembimbing_perusahaan->perusahaan_id . '"

                >Edit</a>
                        <a href="#" class="btn btn-danger btn-sm btnHapus" data-id="' . $pembimbing_perusahaan->id . '">Hapus</a>';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }




    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'nama_pembimbing' => 'required',
            'NIP' => 'required',
            'perusahaan_id' => 'required',
            'jabatan' => 'required',
            'jenis_kelamin' => 'required',
            'nohp' => 'required',


        ]);

        Pembimbing_perusahaan::create([
            'nama_pembimbing' => $request->nama_pembimbing,
            'NIP' => $request->NIP,
            'perusahaan_id' => $request->perusahaan_id,
            'jabatan' => $request->jabatan,
            'jenis_kelamin' => $request->jenis_kelamin,
            'nohp' => $request->nohp,
        ]);

        return response()->json(['success' => 'Data Pembimbing Perusahaan Berhasil Ditambahkan']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $pembimbing_perusahaan = Pembimbing_perusahaan::where('id_perusahaan', $id)->get();
        return response()->json($pembimbing_perusahaan);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        $pembimbing_perusahaan = Pembimbing_perusahaan::findOrFail($id);
        return response()->json($pembimbing_perusahaan);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_pembimbing' => 'required',
            'NIP' => 'required',
            'perusahaan_id' => 'required',
            'jabatan' => 'required',
            'jenis_kelamin' => 'required',
            'nohp' => 'required',
        ]);

        $pembimbing_perusahaan = Pembimbing_perusahaan::findOrFail($id);
        $pembimbing_perusahaan->update([
            'nama_pembimbing' => $request->nama_pembimbing,
            'NIP' => $request->NIP,
            'perusahaan_id' => $request->perusahaan_id,
            'jabatan' => $request->jabatan,
            'jenis_kelamin' => $request->jenis_kelamin,
            'nohp' => $request->nohp,

        ]);

        return response()->json(['success' => 'Data Pembimbing Perusahaan Berhasil Diupdate']);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $pembimbing_perusahaan = Pembimbing_perusahaan::findOrFail($id);
        $pembimbing_perusahaan->delete();

        return response()->json(['success' => 'Data Pembimbing Perusahaan Berhasil Dihapus']);
    }
}
