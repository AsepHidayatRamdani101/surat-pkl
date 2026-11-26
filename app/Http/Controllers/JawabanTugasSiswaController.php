<?php

namespace App\Http\Controllers;

use App\Models\JawabanTugasSiswa;
use App\Models\KelompokBimbingan;
use App\Models\Materi;
use App\Models\Pembimbing;
use App\Models\Siswa;
use App\Models\TugasPembekalan;
use Illuminate\Http\Request;
use App\Support\JawabanFormatter;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

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
            'kelompok_id'   => $request->get('kelompok_id'),
            'pembimbing_id' => $request->get('pembimbing_id'),
            'materi_id'     => $request->get('materi_id'),
            'status'        => $request->get('status'),
            'keyword'       => $request->get('keyword'),
        ];

        if ($isPembimbing && !empty($pembimbingAuthId)) {
            $filters['pembimbing_id'] = (string) $pembimbingAuthId;
        }

        $isFiltered = $request->has('filtered');
        $rows = collect();

        if ($isFiltered) {
            if (!empty($filters['materi_id'])) {
                // Student-centric: show all students in bimbingan with their jawaban for this materi
                $tugas = TugasPembekalan::with(['materi'])
                    ->where('materi_id', $filters['materi_id'])
                    ->first();

                $siswaQuery = Siswa::with(['kelas'])->orderBy('nama_siswa');

                if ($isPembimbing) {
                    if (empty($pembimbingAuthId)) {
                        $siswaQuery->whereRaw('1 = 0');
                    } else {
                        $siswaQuery->whereHas('kelompokBimbingan', fn ($q) => $q->untukPembimbing($pembimbingAuthId));
                    }
                }

                if (!empty($filters['kelompok_id'])) {
                    $siswaQuery->whereHas('kelompokBimbingan', fn ($q) => $q->where('kelompok_bimbingan.id', $filters['kelompok_id']));
                } elseif (!empty($filters['pembimbing_id'])) {
                    $siswaQuery->whereHas('kelompokBimbingan', fn ($q) => $q->untukPembimbing($filters['pembimbing_id']));
                }

                $siswaList = $siswaQuery->get();

                $jawabanMap = $tugas
                    ? JawabanTugasSiswa::with(['nilaiTugas'])->where('tugas_pembekalan_id', $tugas->id)->get()->keyBy('siswa_id')
                    : collect();

                $rows = $siswaList->map(function ($siswa) use ($tugas, $jawabanMap) {
                    $jawaban = $jawabanMap->get($siswa->id);
                    if (!$jawaban || !$jawaban->submitted_at) {
                        $status = 'belum';
                    } elseif ($jawaban->nilaiTugas) {
                        $status = 'selesai';
                    } else {
                        $status = 'proses';
                    }

                    return (object) [
                        'jawaban_id'      => $jawaban?->id,
                        'submitted_at'    => $jawaban?->submitted_at,
                        'jawaban_text'    => $jawaban?->jawaban_text,
                        'siswa'           => $siswa,
                        'tugasPembekalan' => $tugas,
                        'nilaiTugas'      => $jawaban?->nilaiTugas,
                        'status'          => $status,
                    ];
                });

                if (!empty($filters['status'])) {
                    $rows = $rows->filter(fn ($r) => $r->status === $filters['status'])->values();
                }
            } else {
                // Jawaban-centric: show submitted jawaban (existing behavior)
                $query = JawabanTugasSiswa::with([
                    'siswa.kelas',
                    'tugasPembekalan.materi',
                    'nilaiTugas.pembimbing',
                ])->latest('submitted_at')->latest('id');

                if ($isPembimbing) {
                    if (empty($pembimbingAuthId)) {
                        $query->whereRaw('1 = 0');
                    } else {
                        $query->whereHas('siswa.kelompokBimbingan', fn ($q) => $q->untukPembimbing($pembimbingAuthId));
                    }
                }

                if (!empty($filters['kelompok_id'])) {
                    $query->whereHas('siswa.kelompokBimbingan', fn ($q) => $q->where('kelompok_bimbingan.id', $filters['kelompok_id']));
                }

                if (!empty($filters['pembimbing_id'])) {
                    $query->whereHas('siswa.kelompokBimbingan', fn ($q) => $q->untukPembimbing($filters['pembimbing_id']));
                }

                if (!empty($filters['keyword'])) {
                    $keyword = trim((string) $filters['keyword']);
                    $query->where(function ($q) use ($keyword) {
                        $q->where('jawaban_text', 'like', '%' . $keyword . '%')
                            ->orWhereHas('siswa', fn ($sq) => $sq->where('nama_siswa', 'like', '%' . $keyword . '%'))
                            ->orWhereHas('tugasPembekalan', fn ($tq) => $tq->where('judul_tugas', 'like', '%' . $keyword . '%'))
                            ->orWhereHas('tugasPembekalan.materi', fn ($mq) => $mq->where('topik', 'like', '%' . $keyword . '%'));
                    });
                }

                $jawabanList = $query->get();

                $rows = $jawabanList->map(function ($item) {
                    if (!$item->submitted_at) {
                        $status = 'belum';
                    } elseif ($item->nilaiTugas) {
                        $status = 'selesai';
                    } else {
                        $status = 'proses';
                    }

                    return (object) [
                        'jawaban_id'      => $item->id,
                        'submitted_at'    => $item->submitted_at,
                        'jawaban_text'    => $item->jawaban_text,
                        'siswa'           => $item->siswa,
                        'tugasPembekalan' => $item->tugasPembekalan,
                        'nilaiTugas'      => $item->nilaiTugas,
                        'status'          => $status,
                    ];
                });

                if (!empty($filters['status'])) {
                    $rows = $rows->filter(fn ($r) => $r->status === $filters['status'])->values();
                }
            }
        }

        [$detail, $soalMap] = $this->buildDetail($rows);

        $kelompokOptionsQuery = KelompokBimbingan::with('pembimbing')
            ->withCount('siswa')
            ->orderBy('nama_kelompok');

        $pembimbingOptionsQuery = Pembimbing::orderBy('nama_pembimbing');

        if ($isPembimbing) {
            if (empty($pembimbingAuthId)) {
                $kelompokOptionsQuery->whereRaw('1 = 0');
                $pembimbingOptionsQuery->whereRaw('1 = 0');
            } else {
                $kelompokOptionsQuery->untukPembimbing($pembimbingAuthId);
                $pembimbingOptionsQuery->whereKey($pembimbingAuthId);
            }
        }

        $kelompokOptions = $kelompokOptionsQuery->get();
        $pembimbingOptions = $pembimbingOptionsQuery->get(['id', 'nama_pembimbing']);
        $materiOptions = Materi::orderByDesc('tanggal_materi')->get(['id', 'topik', 'tanggal_materi']);

        return view('pembekalan.jawaban_siswa', compact(
            'rows', 'filters', 'kelompokOptions', 'pembimbingOptions',
            'materiOptions', 'canInputNilai', 'isFiltered', 'detail', 'soalMap'
        ));
    }

    /**
     * Data ringkas untuk modal penilaian: satu entri per jawaban dan satu entri soal per tugas.
     */
    private function buildDetail($rows): array
    {
        $detail = [];
        $soalMap = [];

        foreach ($rows->filter(fn ($r) => $r->jawaban_id) as $r) {
            $tugas = $r->tugasPembekalan;
            $tugasId = (int) ($tugas->id ?? 0);

            if ($tugas && !isset($soalMap[$tugasId])) {
                $soalMap[$tugasId] = $this->buildSoal($tugas);
            }

            $nilai = $r->nilaiTugas?->nilai;

            $detail[$r->jawaban_id] = [
                'id' => (int) $r->jawaban_id,
                'tugas_id' => $tugasId,
                'siswa' => $r->siswa->nama_siswa ?? '-',
                'kelas' => $r->siswa->kelas->nama_kelas ?? '',
                'materi' => $tugas?->materi?->topik ?? '-',
                'tugas' => $tugas?->judul_tugas ?? '-',
                'submitted' => $r->submitted_at ? \Carbon\Carbon::parse($r->submitted_at)->format('d-m-Y H:i') : null,
                'html' => JawabanFormatter::render($r->jawaban_text),
                'nilai' => $nilai === null ? '' : rtrim(rtrim(number_format((float) $nilai, 2, '.', ''), '0'), '.'),
                'catatan' => (string) ($r->nilaiTugas?->catatan ?? ''),
                'status' => $r->status,
            ];
        }

        return [$detail, $soalMap];
    }

    private function buildSoal(TugasPembekalan $tugas): array
    {
        $files = collect(is_array($tugas->soal_files) ? $tugas->soal_files : [])->map(function ($path) {
            return [
                'name' => basename((string) $path),
                'ext' => strtolower(pathinfo((string) $path, PATHINFO_EXTENSION)),
                'url' => asset('storage/' . $path),
                'exists' => Storage::disk('public')->exists($path),
            ];
        })->values()->all();

        return [
            'prompts' => array_values(array_filter((array) $tugas->soal_parsed_prompts, 'is_string')),
            'essay' => array_values(array_filter((array) $tugas->soal_essay, fn ($v) => is_string($v) && trim($v) !== '')),
            'files' => $files,
        ];
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
