<?php

namespace App\Http\Controllers;

use App\Models\RuanganSidang;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class RuanganSidangController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->role === 'panitia', 403);

        return view('sidang.ruangan.index');
    }

    public function data()
    {
        abort_unless(auth()->user()->role === 'panitia', 403);

        $query = RuanganSidang::query()->orderBy('nama_ruangan');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('aksi', function ($item) {
                return '
                    <div class="btn-group btn-aksi">
                        <button type="button" class="btn btn-outline-primary btn-sm btnEdit" title="Edit"
                            data-id="' . $item->id . '"
                            data-nama_ruangan="' . htmlentities($item->nama_ruangan) . '"
                            data-lokasi="' . htmlentities($item->lokasi ?? '') . '"
                            data-kapasitas="' . ($item->kapasitas ?? '') . '"
                            data-keterangan="' . htmlentities($item->keterangan ?? '') . '">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm btnHapus" title="Hapus"
                            data-id="' . $item->id . '">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->role === 'panitia', 403);

        $data = $request->validate([
            'nama_ruangan' => 'required|string|max:255',
            'lokasi' => 'nullable|string|max:255',
            'kapasitas' => 'nullable|integer|min:1',
            'keterangan' => 'nullable|string',
        ]);

        RuanganSidang::create($data);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, $id)
    {
        abort_unless(auth()->user()->role === 'panitia', 403);

        $data = $request->validate([
            'nama_ruangan' => 'required|string|max:255',
            'lokasi' => 'nullable|string|max:255',
            'kapasitas' => 'nullable|integer|min:1',
            'keterangan' => 'nullable|string',
        ]);

        RuanganSidang::findOrFail($id)->update($data);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->role === 'panitia', 403);

        RuanganSidang::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }
}
