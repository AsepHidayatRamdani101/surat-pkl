<?php

namespace App\Http\Controllers;

use App\Models\Materi;
use App\Models\TugasPembekalan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TugasPembekalanController extends Controller
{
    public function pageIndex(Request $request)
    {
        $filters = [
            'tanggal_awal' => $request->get('tanggal_awal'),
            'tanggal_akhir' => $request->get('tanggal_akhir'),
            'keyword' => $request->get('keyword'),
        ];

        $query = TugasPembekalan::with(['materi', 'jawabanSiswa.nilaiTugas'])
            ->latest('tanggal_tugas')
            ->latest('id');

        if (!empty($filters['tanggal_awal'])) {
            $query->whereDate('tanggal_tugas', '>=', $filters['tanggal_awal']);
        }

        if (!empty($filters['tanggal_akhir'])) {
            $query->whereDate('tanggal_tugas', '<=', $filters['tanggal_akhir']);
        }

        if (!empty($filters['keyword'])) {
            $keyword = trim((string) $filters['keyword']);
            $query->where(function ($q) use ($keyword) {
                $q->where('judul_tugas', 'like', '%' . $keyword . '%')
                    ->orWhere('deskripsi_tugas', 'like', '%' . $keyword . '%')
                    ->orWhere('soal_essay', 'like', '%' . $keyword . '%')
                    ->orWhereHas('materi', function ($materiQuery) use ($keyword) {
                        $materiQuery->where('topik', 'like', '%' . $keyword . '%');
                    });
            });
        }

        $tugas = $query->get();
        $materiOptions = Materi::orderByDesc('tanggal_materi')->orderByDesc('id')->get(['id', 'tanggal_materi', 'topik']);

        return view('pembekalan.tugas', compact('tugas', 'filters', 'materiOptions'));
    }

    public function pageStore(Request $request)
    {
        $validated = $request->validate([
            'materi_id' => ['required', 'exists:materis,id', 'unique:tugas_pembekalans,materi_id'],
            'tanggal_tugas' => ['required', 'date'],
            'judul_tugas' => ['required', 'string', 'max:255'],
            'soal_essay' => ['required', 'array', 'min:2'],
            'soal_essay.*' => ['required', 'string'],
            'deskripsi_tugas' => ['nullable', 'string'],
            'deadline' => ['nullable', 'date'],
        ]);

        $validated['soal_essay'] = collect($validated['soal_essay'])
            ->map(fn($soal) => trim((string) $soal))
            ->filter()
            ->values()
            ->all();

        TugasPembekalan::create($validated);

        return redirect()->route('pembekalan.tugas')->with('success', 'Tugas berhasil ditambahkan.');
    }

    public function pageUpdate(Request $request, TugasPembekalan $tugasPembekalan)
    {
        $validated = $request->validate([
            'materi_id' => ['required', 'exists:materis,id', Rule::unique('tugas_pembekalans', 'materi_id')->ignore($tugasPembekalan->id)],
            'tanggal_tugas' => ['required', 'date'],
            'judul_tugas' => ['required', 'string', 'max:255'],
            'soal_essay' => ['required', 'array', 'min:2'],
            'soal_essay.*' => ['required', 'string'],
            'deskripsi_tugas' => ['nullable', 'string'],
            'deadline' => ['nullable', 'date'],
        ]);

        $validated['soal_essay'] = collect($validated['soal_essay'])
            ->map(fn($soal) => trim((string) $soal))
            ->filter()
            ->values()
            ->all();

        $tugasPembekalan->update($validated);

        return redirect()->route('pembekalan.tugas')->with('success', 'Tugas berhasil diperbarui.');
    }

    public function pageDestroy(TugasPembekalan $tugasPembekalan)
    {
        $tugasPembekalan->delete();

        return redirect()->route('pembekalan.tugas')->with('success', 'Tugas berhasil dihapus.');
    }

    public function index(Request $request)
    {
        $query = TugasPembekalan::with(['materi'])->latest('tanggal_tugas');

        return response()->json($query->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'materi_id' => ['required', 'exists:materis,id', 'unique:tugas_pembekalans,materi_id'],
            'tanggal_tugas' => ['required', 'date'],
            'judul_tugas' => ['required', 'string', 'max:255'],
            'soal_essay' => ['required', 'array', 'min:2'],
            'soal_essay.*' => ['required', 'string'],
            'deskripsi_tugas' => ['nullable', 'string'],
            'deadline' => ['nullable', 'date'],
        ]);

        $validated['soal_essay'] = collect($validated['soal_essay'])
            ->map(fn($soal) => trim((string) $soal))
            ->filter()
            ->values()
            ->all();

        $tugas = TugasPembekalan::create($validated);

        return response()->json($tugas, 201);
    }

    public function show(TugasPembekalan $tugasPembekalan)
    {
        return response()->json($tugasPembekalan->load(['materi', 'jawabanSiswa.siswa', 'jawabanSiswa.nilaiTugas']));
    }

    public function update(Request $request, TugasPembekalan $tugasPembekalan)
    {
        $validated = $request->validate([
            'materi_id' => ['required', 'exists:materis,id', Rule::unique('tugas_pembekalans', 'materi_id')->ignore($tugasPembekalan->id)],
            'tanggal_tugas' => ['required', 'date'],
            'judul_tugas' => ['required', 'string', 'max:255'],
            'soal_essay' => ['required', 'array', 'min:2'],
            'soal_essay.*' => ['required', 'string'],
            'deskripsi_tugas' => ['nullable', 'string'],
            'deadline' => ['nullable', 'date'],
        ]);

        $validated['soal_essay'] = collect($validated['soal_essay'])
            ->map(fn($soal) => trim((string) $soal))
            ->filter()
            ->values()
            ->all();

        $tugasPembekalan->update($validated);

        return response()->json($tugasPembekalan);
    }

    public function destroy(TugasPembekalan $tugasPembekalan)
    {
        $tugasPembekalan->delete();

        return response()->json(['message' => 'Tugas deleted']);
    }
}
