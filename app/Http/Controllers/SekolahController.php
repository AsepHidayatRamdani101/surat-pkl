<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class SekolahController extends Controller
{
    public function index()
    {
        return view('sekolah.index');
    }

    public function data()
    {
        $sekolah = Sekolah::orderBy('created_at', 'desc')->get();

        return DataTables::of($sekolah)
            ->addColumn('checkbox', function ($item) {
                return '<input type="checkbox" class="form-check checkbox-sekolah" value="' . $item->id . '">';
            })
            ->addColumn('cap_sekolah', function ($item) {
                if ($item->cap_sekolah_path) {
                    return '<a href="' . asset('storage/' . $item->cap_sekolah_path) . '" target="_blank">Lihat Cap</a>';
                }
                return '-';
            })
            ->addColumn('ttd_kepala_sekolah', function ($item) {
                if ($item->ttd_kepala_sekolah_path) {
                    return '<a href="' . asset('storage/' . $item->ttd_kepala_sekolah_path) . '" target="_blank">Lihat TTD</a>';
                }
                return '-';
            })
            ->addColumn('aksi', function ($item) {
                return '
                    <a href="javascript:void(0)" class="btn btn-sm btn-warning btnEdit" 
                        data-id="' . $item->id . '"
                        data-nama_kepala_sekolah="' . htmlentities($item->nama_kepala_sekolah) . '"
                        data-nip_kepala_sekolah="' . htmlentities($item->nip_kepala_sekolah) . '"
                        data-tanggal_mulai_pkl="' . $item->tanggal_mulai_pkl . '"
                        data-tanggal_selesai_pkl="' . $item->tanggal_selesai_pkl . '"
                        data-cap_sekolah_path="' . $item->cap_sekolah_path . '"
                        data-ttd_kepala_sekolah_path="' . $item->ttd_kepala_sekolah_path . '">
                        Edit
                    </a>
                    <a href="javascript:void(0)" class="btn btn-sm btn-danger btnHapus" data-id="' . $item->id . '">Hapus</a>
                ';
            })
            ->addIndexColumn()
            ->rawColumns(['checkbox', 'cap_sekolah', 'ttd_kepala_sekolah', 'aksi'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kepala_sekolah' => 'required|string|max:255',
            'nip_kepala_sekolah' => 'required|string|max:50',
            'tanggal_mulai_pkl' => 'required|date',
            'tanggal_selesai_pkl' => 'required|date|after_or_equal:tanggal_mulai_pkl',
            'cap_sekolah' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'ttd_kepala_sekolah' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->only([
            'nama_kepala_sekolah',
            'nip_kepala_sekolah',
            'tanggal_mulai_pkl',
            'tanggal_selesai_pkl',
        ]);

        if ($request->hasFile('cap_sekolah')) {
            $file = $request->file('cap_sekolah');
            $filename = time() . '_cap_' . $file->getClientOriginalName();
            $data['cap_sekolah_path'] = $file->storeAs('school-assets', $filename, 'public');
        }

        if ($request->hasFile('ttd_kepala_sekolah')) {
            $file = $request->file('ttd_kepala_sekolah');
            $filename = time() . '_ttd_' . $file->getClientOriginalName();
            $data['ttd_kepala_sekolah_path'] = $file->storeAs('school-assets', $filename, 'public');
        }

        Sekolah::create($data);

        return response()->json(['success' => true]);
    }

    public function edit($id)
    {
        $item = Sekolah::findOrFail($id);
        return response()->json([
            'id' => $item->id,
            'nama_kepala_sekolah' => $item->nama_kepala_sekolah,
            'nip_kepala_sekolah' => $item->nip_kepala_sekolah,
            'tanggal_mulai_pkl' => $item->tanggal_mulai_pkl->format('Y-m-d'),
            'tanggal_selesai_pkl' => $item->tanggal_selesai_pkl->format('Y-m-d'),
            'cap_sekolah_path' => $item->cap_sekolah_path,
            'ttd_kepala_sekolah_path' => $item->ttd_kepala_sekolah_path,
            'cap_sekolah_url' => $item->cap_sekolah_url,
            'ttd_kepala_sekolah_url' => $item->ttd_kepala_sekolah_url,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kepala_sekolah' => 'required|string|max:255',
            'nip_kepala_sekolah' => 'required|string|max:50',
            'tanggal_mulai_pkl' => 'required|date',
            'tanggal_selesai_pkl' => 'required|date|after_or_equal:tanggal_mulai_pkl',
            'cap_sekolah' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'ttd_kepala_sekolah' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $item = Sekolah::findOrFail($id);
        $data = $request->only([
            'nama_kepala_sekolah',
            'nip_kepala_sekolah',
            'tanggal_mulai_pkl',
            'tanggal_selesai_pkl',
        ]);

        if ($request->hasFile('cap_sekolah')) {
            if ($item->cap_sekolah_path && Storage::disk('public')->exists($item->cap_sekolah_path)) {
                Storage::disk('public')->delete($item->cap_sekolah_path);
            }
            $file = $request->file('cap_sekolah');
            $filename = time() . '_cap_' . $file->getClientOriginalName();
            $data['cap_sekolah_path'] = $file->storeAs('school-assets', $filename, 'public');
        }

        if ($request->hasFile('ttd_kepala_sekolah')) {
            if ($item->ttd_kepala_sekolah_path && Storage::disk('public')->exists($item->ttd_kepala_sekolah_path)) {
                Storage::disk('public')->delete($item->ttd_kepala_sekolah_path);
            }
            $file = $request->file('ttd_kepala_sekolah');
            $filename = time() . '_ttd_' . $file->getClientOriginalName();
            $data['ttd_kepala_sekolah_path'] = $file->storeAs('school-assets', $filename, 'public');
        }

        $item->update($data);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $item = Sekolah::findOrFail($id);

        if ($item->cap_sekolah_path && Storage::disk('public')->exists($item->cap_sekolah_path)) {
            Storage::disk('public')->delete($item->cap_sekolah_path);
        }
        if ($item->ttd_kepala_sekolah_path && Storage::disk('public')->exists($item->ttd_kepala_sekolah_path)) {
            Storage::disk('public')->delete($item->ttd_kepala_sekolah_path);
        }

        $item->delete();

        return response()->json(['success' => true]);
    }

    public function destroyMultiple(Request $request)
    {
        $ids = $request->ids;
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['message' => 'Pilih minimal satu data'], 422);
        }

        $items = Sekolah::whereIn('id', $ids)->get();
        foreach ($items as $item) {
            if ($item->cap_sekolah_path && Storage::disk('public')->exists($item->cap_sekolah_path)) {
                Storage::disk('public')->delete($item->cap_sekolah_path);
            }
            if ($item->ttd_kepala_sekolah_path && Storage::disk('public')->exists($item->ttd_kepala_sekolah_path)) {
                Storage::disk('public')->delete($item->ttd_kepala_sekolah_path);
            }
        }

        Sekolah::whereIn('id', $ids)->delete();

        return response()->json(['message' => 'Data berhasil dihapus']);
    }
}
