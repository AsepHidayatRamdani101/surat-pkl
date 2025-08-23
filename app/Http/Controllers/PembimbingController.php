<?php

namespace App\Http\Controllers;

use App\Models\Pembimbing;
use Illuminate\Http\Request;

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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_pemimbing' => 'required',
            'nip_pembimbing' => 'required',
            'jenis_kelamin' => 'required',
            'jabatan_pembimbing' => 'required',
            'no_hp_pembimbing' => 'required',
        ]);

        Pembimbing::create([
            'nama_pemimbing' => $request->nama_pemimbing,
            'nip_pembimbing' => $request->nip,
            'jenis_kelamin' => $request->jenis_kelamin,
            'jabatan_pembimbing' => $request->jabatan_pembimbing,
            'no_hp_pembimbing' => $request->no_hp_pembimbing,
           
        ]);

        return redirect()->route('pembimbing.index')->with('success', 'Data Pembimbing Berhasil Ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Pembimbing $pembimbing)
    {
        return view('pembimbing.show', compact('pembimbing'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pembimbing $pembimbing)
    {
        return view('pembimbing.edit', compact('pembimbing'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pembimbing $pembimbing)
    {
        $request->validate([
            'nama_pemimbing' => 'required',
            'nip' => 'required',
            'jenis_kelamin' => 'required',
            'alamat' => 'required',
            'no_hp' => 'required',
        ]);

        $pembimbing->update([
            'nama_pemimbing' => $request->nama_pemimbing,
            'nip' => $request->nip,
            'jenis_kelamin' => $request->jenis_kelamin,
            'alamat' => $request->alamat,
            'no_hp' => $request->no_hp,
        ]);

        return redirect()->route('pembimbing.index')->with('success', 'Data Pembimbing Berhasil Diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pembimbing $pembimbing)
    {
        $pembimbing->delete();
        return redirect()->route('pembimbing.index')->with('success', 'Data Pembimbing Berhasil Dihapus');
    }
}

