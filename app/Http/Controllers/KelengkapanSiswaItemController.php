<?php

namespace App\Http\Controllers;

use App\Models\KelengkapanSiswaItem;
use Illuminate\Http\Request;

class KelengkapanSiswaItemController extends Controller
{
    public function index()
    {
        $items = KelengkapanSiswaItem::query()
            ->orderBy('urutan')
            ->orderBy('nama_item')
            ->get();

        return view('pembekalan.kelengkapan_master', [
            'items' => $items,
        ]);
    }

    public function store(Request $request)
    {
        KelengkapanSiswaItem::create($this->validatePayload($request));

        return redirect()->route('pembekalan.kelengkapan.master')->with('success', 'Daftar kelengkapan berhasil ditambahkan.');
    }

    public function update(Request $request, KelengkapanSiswaItem $kelengkapanSiswaItem)
    {
        $kelengkapanSiswaItem->update($this->validatePayload($request));

        return redirect()->route('pembekalan.kelengkapan.master')->with('success', 'Daftar kelengkapan berhasil diperbarui.');
    }

    public function destroy(KelengkapanSiswaItem $kelengkapanSiswaItem)
    {
        $kelengkapanSiswaItem->delete();

        return redirect()->route('pembekalan.kelengkapan.master')->with('success', 'Daftar kelengkapan berhasil dihapus.');
    }

    private function validatePayload(Request $request): array
    {
        $validated = $request->validate([
            'nama_item' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'urutan' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['urutan'] = (int) ($validated['urutan'] ?? 0);

        return $validated;
    }
}