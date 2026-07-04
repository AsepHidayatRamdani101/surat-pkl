<?php

namespace App\Http\Controllers;

use App\Models\JawabanTugasSiswa;
use App\Models\NilaiTugasPembekalan;
use App\Models\Pembimbing;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class NilaiTugasPembekalanController extends Controller
{
    public function pageStore(Request $request, JawabanTugasSiswa $jawabanTugasSiswa)
    {
        $user = auth()->user();
        if (!$user || (!Gate::forUser($user)->allows('pembimbing') && $user->role !== 'pembimbing')) {
            throw new AuthorizationException('Akses hanya untuk pembimbing.');
        }

        $pembimbing = Pembimbing::query()
            ->where('nip_pembimbing', (string) $user->username)
            ->first();

        if (!$pembimbing) {
            throw new AuthorizationException('Data pembimbing untuk akun ini tidak ditemukan.');
        }

        $isAuthorized = $jawabanTugasSiswa->siswa()
            ->whereHas('kelompokBimbingan', function ($query) use ($pembimbing) {
                $query->where('kelompok_bimbingan.pembimbing_id', $pembimbing->id);
            })
            ->exists();

        if (!$isAuthorized) {
            throw new AuthorizationException('Anda tidak berwenang memberi nilai untuk siswa ini.');
        }

        $this->ensureJawabanSudahSubmit($jawabanTugasSiswa);

        $validated = $request->validate([
            'nilai' => ['required', 'numeric', 'min:0', 'max:100'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        NilaiTugasPembekalan::updateOrCreate(
            ['jawaban_tugas_siswa_id' => $jawabanTugasSiswa->id],
            [
                'jawaban_tugas_siswa_id' => $jawabanTugasSiswa->id,
                'pembimbing_id' => $pembimbing->id,
                'nilai' => $validated['nilai'],
                'catatan' => $validated['catatan'] ?? null,
                'dinilai_at' => now(),
            ]
        );

        return redirect()->route('pembekalan.jawaban-siswa')->with('success', 'Nilai tugas berhasil disimpan.');
    }

    public function index(Request $request)
    {
        $query = NilaiTugasPembekalan::with(['jawabanTugasSiswa.tugasPembekalan', 'jawabanTugasSiswa.siswa', 'pembimbing'])->latest();

        if ($request->filled('pembimbing_id')) {
            $query->where('pembimbing_id', $request->pembimbing_id);
        }

        return response()->json($query->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jawaban_tugas_siswa_id' => ['required', 'exists:jawaban_tugas_siswas,id'],
            'pembimbing_id' => ['required', 'exists:pembimbings,id'],
            'nilai' => ['required', 'numeric', 'min:0', 'max:100'],
            'catatan' => ['nullable', 'string'],
            'dinilai_at' => ['nullable', 'date'],
        ]);

        $jawabanTugasSiswa = JawabanTugasSiswa::findOrFail($validated['jawaban_tugas_siswa_id']);
        $this->ensureJawabanSudahSubmit($jawabanTugasSiswa);

        $nilai = NilaiTugasPembekalan::updateOrCreate(
            ['jawaban_tugas_siswa_id' => $validated['jawaban_tugas_siswa_id']],
            $validated
        );

        return response()->json($nilai, 201);
    }

    public function show(NilaiTugasPembekalan $nilaiTugasPembekalan)
    {
        return response()->json($nilaiTugasPembekalan->load(['jawabanTugasSiswa.siswa', 'jawabanTugasSiswa.tugasPembekalan', 'pembimbing']));
    }

    public function update(Request $request, NilaiTugasPembekalan $nilaiTugasPembekalan)
    {
        $validated = $request->validate([
            'jawaban_tugas_siswa_id' => ['required', 'exists:jawaban_tugas_siswas,id'],
            'pembimbing_id' => ['required', 'exists:pembimbings,id'],
            'nilai' => ['required', 'numeric', 'min:0', 'max:100'],
            'catatan' => ['nullable', 'string'],
            'dinilai_at' => ['nullable', 'date'],
        ]);

        $jawabanTugasSiswa = JawabanTugasSiswa::findOrFail($validated['jawaban_tugas_siswa_id']);
        $this->ensureJawabanSudahSubmit($jawabanTugasSiswa);

        $nilaiTugasPembekalan->update($validated);

        return response()->json($nilaiTugasPembekalan);
    }

    public function destroy(NilaiTugasPembekalan $nilaiTugasPembekalan)
    {
        $nilaiTugasPembekalan->delete();

        return response()->json(['message' => 'Nilai tugas deleted']);
    }

    private function ensureJawabanSudahSubmit(JawabanTugasSiswa $jawabanTugasSiswa): void
    {
        if (empty($jawabanTugasSiswa->submitted_at)) {
            throw ValidationException::withMessages([
                'nilai' => 'Nilai tidak dapat diinput karena jawaban siswa belum disubmit.',
            ]);
        }
    }
}
