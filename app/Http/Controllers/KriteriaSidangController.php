<?php

namespace App\Http\Controllers;

use App\Models\KriteriaSidang;
use App\Models\RubrikKriteriaSidang;
use Illuminate\Http\Request;

class KriteriaSidangController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->role === 'panitia', 403);

        $totalBobot = (int) KriteriaSidang::sum('bobot');

        return view('sidang.kriteria.index', compact('totalBobot'));
    }

    public function data()
    {
        abort_unless(in_array(auth()->user()->role, ['panitia', 'pembimbing'], true), 403);

        $kriteria = KriteriaSidang::with('rubriks')
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $kriteria,
            'total_bobot' => (int) $kriteria->sum('bobot'),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->role === 'panitia', 403);

        $data = $this->validateKriteria($request);

        $kriteria = KriteriaSidang::create($data['kriteria']);
        $this->simpanRubriks($kriteria, $data['rubriks']);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, $id)
    {
        abort_unless(auth()->user()->role === 'panitia', 403);

        $kriteria = KriteriaSidang::findOrFail($id);
        $data = $this->validateKriteria($request);

        $kriteria->update($data['kriteria']);
        $kriteria->rubriks()->delete();
        $this->simpanRubriks($kriteria, $data['rubriks']);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->role === 'panitia', 403);

        KriteriaSidang::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }

    private function validateKriteria(Request $request): array
    {
        $validated = $request->validate([
            'nama_kriteria' => 'required|string|max:255',
            'bobot' => 'required|integer|min:1|max:100',
            'deskripsi' => 'nullable|string',
            'urutan' => 'nullable|integer|min:0',
            'rubriks' => 'nullable|array',
            'rubriks.*.label' => 'required|string|max:100',
            'rubriks.*.nilai_min' => 'required|integer|min:0|max:100',
            'rubriks.*.nilai_max' => 'required|integer|min:0|max:100|gte:rubriks.*.nilai_min',
            'rubriks.*.deskripsi' => 'nullable|string',
        ]);

        return [
            'kriteria' => [
                'nama_kriteria' => $validated['nama_kriteria'],
                'bobot' => $validated['bobot'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'urutan' => $validated['urutan'] ?? 0,
            ],
            'rubriks' => $validated['rubriks'] ?? [],
        ];
    }

    private function simpanRubriks(KriteriaSidang $kriteria, array $rubriks): void
    {
        foreach ($rubriks as $rubrik) {
            RubrikKriteriaSidang::create([
                'kriteria_sidang_id' => $kriteria->id,
                'label' => $rubrik['label'],
                'nilai_min' => $rubrik['nilai_min'],
                'nilai_max' => $rubrik['nilai_max'],
                'deskripsi' => $rubrik['deskripsi'] ?? null,
            ]);
        }
    }
}
