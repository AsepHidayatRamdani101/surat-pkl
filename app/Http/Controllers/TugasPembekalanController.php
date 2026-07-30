<?php

namespace App\Http\Controllers;

use App\Models\Materi;
use App\Models\TugasPembekalan;
use App\Support\WorksheetPromptExtractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
            'soal_essay' => ['nullable', 'array'],
            'soal_essay.*' => ['nullable', 'string'],
            'soal_files' => ['nullable', 'array'],
            'soal_files.*' => ['file', 'mimes:pdf,jpg,jpeg', 'max:10240'],
            'deskripsi_tugas' => ['nullable', 'string'],
            'deadline' => ['nullable', 'date'],
        ]);

        $normalizedEssay = $this->normalizeSoalEssay($validated['soal_essay'] ?? []);
        $uploadedFiles = $this->storeUploadedSoalFiles($request);
        $this->ensureSoalSourceAvailable($normalizedEssay, [], $uploadedFiles);
        $parsedPrompts = $this->buildParsedPrompts($uploadedFiles, $normalizedEssay);

        $validated['soal_essay'] = !empty($normalizedEssay) ? $normalizedEssay : null;
        $validated['soal_files'] = !empty($uploadedFiles) ? $uploadedFiles : null;
        $validated['soal_parsed_prompts'] = !empty($parsedPrompts) ? $parsedPrompts : null;
        $validated['soal_parsed_at'] = !empty($parsedPrompts) ? now() : null;

        TugasPembekalan::create($validated);

        return redirect()->route('pembekalan.tugas')->with('success', 'Tugas berhasil ditambahkan.');
    }

    public function pageUpdate(Request $request, TugasPembekalan $tugasPembekalan)
    {
        $validated = $request->validate([
            'materi_id' => ['required', 'exists:materis,id', Rule::unique('tugas_pembekalans', 'materi_id')->ignore($tugasPembekalan->id)],
            'tanggal_tugas' => ['required', 'date'],
            'judul_tugas' => ['required', 'string', 'max:255'],
            'soal_essay' => ['nullable', 'array'],
            'soal_essay.*' => ['nullable', 'string'],
            'soal_files' => ['nullable', 'array'],
            'soal_files.*' => ['file', 'mimes:pdf,jpg,jpeg', 'max:10240'],
            'remove_soal_files' => ['nullable', 'array'],
            'remove_soal_files.*' => ['string'],
            'deskripsi_tugas' => ['nullable', 'string'],
            'deadline' => ['nullable', 'date'],
        ]);

        $normalizedEssay = $this->normalizeSoalEssay($validated['soal_essay'] ?? []);

        $existingFiles = is_array($tugasPembekalan->soal_files) ? $tugasPembekalan->soal_files : [];
        $filesToRemove = collect($validated['remove_soal_files'] ?? [])->filter()->values()->all();
        $remainingExistingFiles = $this->removeSelectedSoalFiles($existingFiles, $filesToRemove);
        $newFiles = $this->storeUploadedSoalFiles($request);
        $mergedFiles = array_values(array_merge($remainingExistingFiles, $newFiles));
        $this->ensureSoalSourceAvailable($normalizedEssay, $remainingExistingFiles, $newFiles);
        $parsedPrompts = $this->buildParsedPrompts($mergedFiles, $normalizedEssay);

        $validated['soal_essay'] = !empty($normalizedEssay) ? $normalizedEssay : null;
        $validated['soal_files'] = !empty($mergedFiles) ? $mergedFiles : null;
        $validated['soal_parsed_prompts'] = !empty($parsedPrompts) ? $parsedPrompts : null;
        $validated['soal_parsed_at'] = !empty($parsedPrompts) ? now() : null;

        $tugasPembekalan->update($validated);

        return redirect()->route('pembekalan.tugas')->with('success', 'Tugas berhasil diperbarui.');
    }

    public function pageDestroy(TugasPembekalan $tugasPembekalan)
    {
        $this->deleteSoalFiles($tugasPembekalan->soal_files);
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
            'soal_essay' => ['nullable', 'array'],
            'soal_essay.*' => ['nullable', 'string'],
            'soal_files' => ['nullable', 'array'],
            'soal_files.*' => ['file', 'mimes:pdf,jpg,jpeg', 'max:10240'],
            'deskripsi_tugas' => ['nullable', 'string'],
            'deadline' => ['nullable', 'date'],
        ]);

        $normalizedEssay = $this->normalizeSoalEssay($validated['soal_essay'] ?? []);
        $uploadedFiles = $this->storeUploadedSoalFiles($request);
        $this->ensureSoalSourceAvailable($normalizedEssay, [], $uploadedFiles);
        $parsedPrompts = $this->buildParsedPrompts($uploadedFiles, $normalizedEssay);

        $validated['soal_essay'] = !empty($normalizedEssay) ? $normalizedEssay : null;
        $validated['soal_files'] = !empty($uploadedFiles) ? $uploadedFiles : null;
        $validated['soal_parsed_prompts'] = !empty($parsedPrompts) ? $parsedPrompts : null;
        $validated['soal_parsed_at'] = !empty($parsedPrompts) ? now() : null;

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
            'soal_essay' => ['nullable', 'array'],
            'soal_essay.*' => ['nullable', 'string'],
            'soal_files' => ['nullable', 'array'],
            'soal_files.*' => ['file', 'mimes:pdf,jpg,jpeg', 'max:10240'],
            'remove_soal_files' => ['nullable', 'array'],
            'remove_soal_files.*' => ['string'],
            'deskripsi_tugas' => ['nullable', 'string'],
            'deadline' => ['nullable', 'date'],
        ]);

        $normalizedEssay = $this->normalizeSoalEssay($validated['soal_essay'] ?? []);

        $existingFiles = is_array($tugasPembekalan->soal_files) ? $tugasPembekalan->soal_files : [];
        $filesToRemove = collect($validated['remove_soal_files'] ?? [])->filter()->values()->all();
        $remainingExistingFiles = $this->removeSelectedSoalFiles($existingFiles, $filesToRemove);
        $newFiles = $this->storeUploadedSoalFiles($request);
        $mergedFiles = array_values(array_merge($remainingExistingFiles, $newFiles));
        $this->ensureSoalSourceAvailable($normalizedEssay, $remainingExistingFiles, $newFiles);
        $parsedPrompts = $this->buildParsedPrompts($mergedFiles, $normalizedEssay);

        $validated['soal_essay'] = !empty($normalizedEssay) ? $normalizedEssay : null;
        $validated['soal_files'] = !empty($mergedFiles) ? $mergedFiles : null;
        $validated['soal_parsed_prompts'] = !empty($parsedPrompts) ? $parsedPrompts : null;
        $validated['soal_parsed_at'] = !empty($parsedPrompts) ? now() : null;

        $tugasPembekalan->update($validated);

        return response()->json($tugasPembekalan);
    }

    public function destroy(TugasPembekalan $tugasPembekalan)
    {
        $this->deleteSoalFiles($tugasPembekalan->soal_files);
        $tugasPembekalan->delete();

        return response()->json(['message' => 'Tugas deleted']);
    }

    private function normalizeSoalEssay(array $soalEssay): array
    {
        return collect($soalEssay)
            ->map(fn($soal) => trim((string) $soal))
            ->filter()
            ->values()
            ->all();
    }

    private function storeUploadedSoalFiles(Request $request): array
    {
        if (!$request->hasFile('soal_files')) {
            return [];
        }

        $storedPaths = [];
        foreach ($request->file('soal_files', []) as $file) {
            if ($file) {
                $storedPaths[] = $file->store('tugas-soal', 'public');
            }
        }

        return $storedPaths;
    }

    private function deleteSoalFiles($soalFiles): void
    {
        if (!is_array($soalFiles) || empty($soalFiles)) {
            return;
        }

        foreach ($soalFiles as $path) {
            if (!empty($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    private function removeSelectedSoalFiles(array $existingFiles, array $filesToRemove): array
    {
        if (empty($existingFiles) || empty($filesToRemove)) {
            return $existingFiles;
        }

        $removeLookup = collect($filesToRemove)
            ->map(fn($path) => (string) $path)
            ->filter()
            ->values()
            ->all();

        $remaining = [];
        foreach ($existingFiles as $path) {
            if (in_array($path, $removeLookup, true)) {
                Storage::disk('public')->delete($path);
                continue;
            }

            $remaining[] = $path;
        }

        return $remaining;
    }

    private function ensureSoalSourceAvailable(array $essay, array $existingFiles, array $newFiles): void
    {
        $hasEssay = !empty($essay);
        $hasFiles = !empty($existingFiles) || !empty($newFiles);

        if ($hasEssay || $hasFiles) {
            return;
        }

        throw ValidationException::withMessages([
            'soal_essay' => 'Soal essay boleh kosong, tetapi Anda harus mengupload file soal (PDF/JPG) jika tidak mengisi soal essay.',
        ]);
    }

    private function buildParsedPrompts(array $soalFiles, array $fallbackEssay): array
    {
        return app(WorksheetPromptExtractor::class)->extractFromTaskSources($soalFiles, $fallbackEssay);
    }
}
