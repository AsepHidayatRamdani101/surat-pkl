<?php

namespace App\Http\Controllers;

use App\Models\JawabanTugasSiswa;
use App\Models\KelompokBimbingan;
use App\Models\Pembimbing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class JawabanTugasSiswaController extends Controller
{
    public function pageIndex(Request $request)
    {
        $authUser = auth()->user();
        $isPembimbing = $authUser && (Gate::forUser($authUser)->allows('pembimbing') || $authUser->role === 'pembimbing');
        $pembimbingAuthId = null;
        $canInputNilai = $isPembimbing;

        if ($isPembimbing) {
            $pembimbingAuthId = Pembimbing::query()
                ->where('nip_pembimbing', (string) $authUser->username)
                ->value('id');
        }

        $filters = [
            'kelompok_id' => $request->get('kelompok_id'),
            'pembimbing_id' => $request->get('pembimbing_id'),
            'keyword' => $request->get('keyword'),
        ];

        if ($isPembimbing && !empty($pembimbingAuthId)) {
            $filters['pembimbing_id'] = (string) $pembimbingAuthId;
        }

        $query = JawabanTugasSiswa::with([
            'siswa.kelas',
            'tugasPembekalan.materi',
            'nilaiTugas.pembimbing',
        ])->latest('submitted_at')->latest('id');

        if ($isPembimbing) {
            if (empty($pembimbingAuthId)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereHas('siswa.kelompokBimbingan', function ($q) use ($pembimbingAuthId) {
                    $q->where('kelompok_bimbingan.pembimbing_id', $pembimbingAuthId);
                });
            }
        }

        if (!empty($filters['kelompok_id'])) {
            $query->whereHas('siswa.kelompokBimbingan', function ($q) use ($filters) {
                $q->where('kelompok_bimbingan.id', $filters['kelompok_id']);
            });
        }

        if (!empty($filters['pembimbing_id'])) {
            $query->whereHas('siswa.kelompokBimbingan', function ($q) use ($filters) {
                $q->where('kelompok_bimbingan.pembimbing_id', $filters['pembimbing_id']);
            });
        }

        if (!empty($filters['keyword'])) {
            $keyword = trim((string) $filters['keyword']);
            $query->where(function ($q) use ($keyword) {
                $q->where('jawaban_text', 'like', '%' . $keyword . '%')
                    ->orWhereHas('siswa', function ($sq) use ($keyword) {
                        $sq->where('nama_siswa', 'like', '%' . $keyword . '%');
                    })
                    ->orWhereHas('tugasPembekalan', function ($tq) use ($keyword) {
                        $tq->where('judul_tugas', 'like', '%' . $keyword . '%');
                    })
                    ->orWhereHas('tugasPembekalan.materi', function ($mq) use ($keyword) {
                        $mq->where('topik', 'like', '%' . $keyword . '%');
                    });
            });
        }

        $jawaban = $query->get();

        $kelompokOptionsQuery = KelompokBimbingan::with('pembimbing')
            ->withCount('siswa')
            ->orderBy('nama_kelompok');

        $pembimbingOptionsQuery = Pembimbing::orderBy('nama_pembimbing');

        if ($isPembimbing) {
            if (empty($pembimbingAuthId)) {
                $kelompokOptionsQuery->whereRaw('1 = 0');
                $pembimbingOptionsQuery->whereRaw('1 = 0');
            } else {
                $kelompokOptionsQuery->where('pembimbing_id', $pembimbingAuthId);
                $pembimbingOptionsQuery->whereKey($pembimbingAuthId);
            }
        }

        $kelompokOptions = $kelompokOptionsQuery->get();

        $pembimbingOptions = $pembimbingOptionsQuery->get(['id', 'nama_pembimbing']);

        return view('pembekalan.jawaban_siswa', compact('jawaban', 'filters', 'kelompokOptions', 'pembimbingOptions', 'canInputNilai'));
    }

    public function index(Request $request)
    {
        $query = JawabanTugasSiswa::with(['tugasPembekalan', 'siswa', 'nilaiTugas'])->latest();

        if ($request->filled('tugas_pembekalan_id')) {
            $query->where('tugas_pembekalan_id', $request->tugas_pembekalan_id);
        }

        if ($request->filled('siswa_id')) {
            $query->where('siswa_id', $request->siswa_id);
        }

        return response()->json($query->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tugas_pembekalan_id' => ['required', 'exists:tugas_pembekalans,id'],
            'siswa_id' => ['required', 'exists:siswa,id'],
            'jawaban_text' => ['nullable', 'string'],
            'lampiran_path' => ['nullable', 'string', 'max:255'],
            'submitted_at' => ['nullable', 'date'],
        ]);

        $jawaban = JawabanTugasSiswa::updateOrCreate(
            [
                'tugas_pembekalan_id' => $validated['tugas_pembekalan_id'],
                'siswa_id' => $validated['siswa_id'],
            ],
            $validated
        );

        return response()->json($jawaban, 201);
    }

    public function show(JawabanTugasSiswa $jawabanTugasSiswa)
    {
        return response()->json($jawabanTugasSiswa->load(['tugasPembekalan', 'siswa', 'nilaiTugas']));
    }

    public function update(Request $request, JawabanTugasSiswa $jawabanTugasSiswa)
    {
        $validated = $request->validate([
            'tugas_pembekalan_id' => ['required', 'exists:tugas_pembekalans,id'],
            'siswa_id' => ['required', 'exists:siswa,id'],
            'jawaban_text' => ['nullable', 'string'],
            'lampiran_path' => ['nullable', 'string', 'max:255'],
            'submitted_at' => ['nullable', 'date'],
        ]);

        $jawabanTugasSiswa->update($validated);

        return response()->json($jawabanTugasSiswa);
    }

    public function destroy(JawabanTugasSiswa $jawabanTugasSiswa)
    {
        $jawabanTugasSiswa->delete();

        return response()->json(['message' => 'Jawaban deleted']);
    }
}
