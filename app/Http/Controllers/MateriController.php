<?php

namespace App\Http\Controllers;

use App\Models\Materi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MateriController extends Controller
{
    private function resolveLegacyType(?string $text, ?string $pdfPath, ?string $videoUrl): string
    {
        if (!empty(trim((string) $text))) {
            return 'text';
        }

        if (!empty($pdfPath)) {
            return 'pdf';
        }

        return 'video';
    }

    public function index(Request $request)
    {
        $filters = [
            'tanggal_awal' => $request->get('tanggal_awal'),
            'tanggal_akhir' => $request->get('tanggal_akhir'),
            'keyword' => $request->get('keyword'),
        ];

        $query = Materi::query()->latest('tanggal_materi')->latest('id');

        if (!empty($filters['tanggal_awal'])) {
            $query->whereDate('tanggal_materi', '>=', $filters['tanggal_awal']);
        }

        if (!empty($filters['tanggal_akhir'])) {
            $query->whereDate('tanggal_materi', '<=', $filters['tanggal_akhir']);
        }

        if (!empty($filters['keyword'])) {
            $query->where('topik', 'like', '%' . trim((string) $filters['keyword']) . '%');
        }

        $materi = $query->get();
        $editMateri = $request->filled('edit') ? Materi::find($request->get('edit')) : null;

        $summary = [
            'total_materi' => $materi->count(),
            'topik_unik' => $materi->pluck('topik')->filter()->unique()->count(),
        ];

        return view('pembekalan.materi', compact('materi', 'summary', 'filters', 'editMateri'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal_materi' => ['required', 'date'],
            'topik' => ['required', 'string', 'max:255'],
            'isi_materi' => ['nullable', 'string'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'catatan' => ['nullable', 'string'],
            'materi_file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $isiMateri = trim((string) ($validated['isi_materi'] ?? ''));
        $videoUrl = trim((string) ($validated['video_url'] ?? ''));

        if ($isiMateri === '' && $videoUrl === '' && !$request->hasFile('materi_file')) {
            return back()->withErrors([
                'isi_materi' => 'Isi minimal salah satu konten: text, file PDF, atau URL video.',
            ])->withInput();
        }

        $filePath = null;
        if ($request->hasFile('materi_file')) {
            $filePath = $request->file('materi_file')->store('materi-pembekalan', 'public');
        }

        $legacyType = $this->resolveLegacyType($isiMateri, $filePath, $videoUrl);

        Materi::create([
            'tanggal_materi' => $validated['tanggal_materi'],
            'topik' => $validated['topik'],
            'tipe_materi' => $legacyType,
            'isi_materi' => $isiMateri !== '' ? $isiMateri : null,
            'file_pdf_path' => $filePath,
            'video_url' => $videoUrl !== '' ? $videoUrl : null,
            'catatan' => $validated['catatan'] ?? null,
        ]);

        return redirect()->route('pembekalan.materi')->with('success', 'Materi berhasil ditambahkan.');
    }

    public function show(Materi $materi)
    {
        return response()->json($materi);
    }

    public function update(Request $request, Materi $materi)
    {
        $validated = $request->validate([
            'tanggal_materi' => ['required', 'date'],
            'topik' => ['required', 'string', 'max:255'],
            'isi_materi' => ['nullable', 'string'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'catatan' => ['nullable', 'string'],
            'materi_file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'hapus_pdf_lama' => ['nullable', 'boolean'],
        ]);

        $isiMateri = trim((string) ($validated['isi_materi'] ?? ''));
        $videoUrl = trim((string) ($validated['video_url'] ?? ''));
        $removeOldPdf = $request->boolean('hapus_pdf_lama');
        $existingPdfWillRemain = !empty($materi->file_pdf_path) && !$removeOldPdf;

        $hasAnyInput = $isiMateri !== '' || $videoUrl !== '' || $request->hasFile('materi_file') || $existingPdfWillRemain;
        if (!$hasAnyInput) {
            return back()->withErrors([
                'isi_materi' => 'Isi minimal salah satu konten: text, file PDF, atau URL video.',
            ])->withInput();
        }

        $filePath = $materi->file_pdf_path;
        if ($request->hasFile('materi_file')) {
            $filePath = $request->file('materi_file')->store('materi-pembekalan', 'public');
            if (!empty($materi->file_pdf_path) && $materi->file_pdf_path !== $filePath) {
                Storage::disk('public')->delete($materi->file_pdf_path);
            }
        } elseif ($removeOldPdf && !empty($materi->file_pdf_path)) {
            Storage::disk('public')->delete($materi->file_pdf_path);
            $filePath = null;
        }

        $legacyType = $this->resolveLegacyType($isiMateri, $filePath, $videoUrl);

        $materi->update([
            'tanggal_materi' => $validated['tanggal_materi'],
            'topik' => $validated['topik'],
            'tipe_materi' => $legacyType,
            'isi_materi' => $isiMateri !== '' ? $isiMateri : null,
            'file_pdf_path' => $filePath,
            'video_url' => $videoUrl !== '' ? $videoUrl : null,
            'catatan' => $validated['catatan'] ?? null,
        ]);

        return redirect()->route('pembekalan.materi')->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroy(Materi $materi)
    {
        if (!empty($materi->file_pdf_path)) {
            Storage::disk('public')->delete($materi->file_pdf_path);
        }

        $materi->delete();

        return redirect()->route('pembekalan.materi')->with('success', 'Materi berhasil dihapus.');
    }
}
