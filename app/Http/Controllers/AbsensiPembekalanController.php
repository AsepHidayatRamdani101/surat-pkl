<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AbsensiPembekalan;
use App\Models\KelompokBimbingan;
use App\Models\Pembimbing;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;

class AbsensiPembekalanController extends Controller
{
    public function pageIndex(Request $request)
    {
        return redirect()->route('pembekalan.absensi.riwayat');
    }

    public function pageInput(Request $request)
    {
        return $this->renderPage($request, 'input');
    }

    public function pageRiwayat(Request $request)
    {
        return $this->renderPage($request, 'riwayat');
    }

    public function pageFormulir(Request $request)
    {
        $data = $this->resolveFormulirData($request);

        return view('pembekalan.formulir_kehadiran', $data);
    }

    public function pageFormulirPdf(Request $request)
    {
        $data = $this->resolveFormulirData($request);
        $filename = 'formulir-kehadiran-pembekalan-' . $data['filters']['tanggal_formulir'] . '.pdf';

        $pdf = Pdf::loadView('pembekalan.formulir_kehadiran_pdf', $data)
            ->setPaper('A4', 'portrait');

        return $pdf->stream($filename);
    }

    public function pageInputStudents(Request $request)
    {
        $pembimbingAuthId = $this->getAuthorizedPembimbingForAbsensiPage();

        $validated = $request->validate([
            'kelompok_id' => ['required', 'exists:kelompok_bimbingan,id'],
            'tanggal_absensi' => ['required', 'date'],
        ]);

        $selectedKelompokQuery = KelompokBimbingan::with(['pembimbing', 'siswa.kelas'])
            ->whereKey($validated['kelompok_id']);

        if (!empty($pembimbingAuthId)) {
            $selectedKelompokQuery->where('pembimbing_id', $pembimbingAuthId);
        }

        $selectedKelompok = $selectedKelompokQuery->first();

        if (!$selectedKelompok) {
            return response()->json([
                'message' => 'Kelompok tidak ditemukan atau tidak dapat diakses.',
            ], 404);
        }

        $studentIds = $selectedKelompok->siswa->pluck('id')->all();

        $existingAbsensi = AbsensiPembekalan::query()
            ->whereDate('tanggal_absensi', $validated['tanggal_absensi'])
            ->whereIn('siswa_id', $studentIds)
            ->get()
            ->keyBy('siswa_id');

        $students = $selectedKelompok->siswa
            ->sortBy('nama_siswa')
            ->values()
            ->map(function ($siswa) use ($existingAbsensi) {
                $absensi = $existingAbsensi->get($siswa->id);

                return [
                    'siswa_id' => (int) $siswa->id,
                    'nama_siswa' => (string) $siswa->nama_siswa,
                    'kelas' => $siswa->kelas ? (string) $siswa->kelas->nama_kelas : null,
                    'status' => (string) ($absensi->status ?? 'hadir'),
                    'keterangan' => (string) ($absensi->keterangan ?? ''),
                ];
            })
            ->values();

        return response()->json([
            'kelompok' => [
                'id' => (int) $selectedKelompok->id,
                'nama_kelompok' => (string) $selectedKelompok->nama_kelompok,
                'pembimbing' => $selectedKelompok->pembimbing
                    ? (string) $selectedKelompok->pembimbing->nama_pembimbing
                    : null,
            ],
            'tanggal_absensi' => (string) $validated['tanggal_absensi'],
            'students' => $students,
        ]);
    }

    private function renderPage(Request $request, string $pageMode)
    {
        $authUser = auth()->user();
        $isPanitia = $authUser && Gate::forUser($authUser)->allows('panitia');
        $isPembimbing = $authUser && (Gate::forUser($authUser)->allows('pembimbing') || $authUser->role === 'pembimbing');
        $canManageAbsensi = $isPanitia || $isPembimbing;
        $showInputSection = $pageMode === 'input';
        $showRiwayatSection = $pageMode === 'riwayat';
        $pembimbingAuthId = null;

        if ($isPembimbing) {
            $pembimbingAuthId = Pembimbing::query()
                ->where('nip_pembimbing', (string) $authUser->username)
                ->value('id');
        }

        $filters = [
            'tanggal_awal' => $request->get('tanggal_awal'),
            'tanggal_akhir' => $request->get('tanggal_akhir'),
            'pembimbing_id' => $request->get('pembimbing_id'),
            'kelompok_id' => $request->get('kelompok_id'),
            'status' => $request->get('status'),
            'keyword' => $request->get('keyword'),
        ];

        $bulkInput = [
            'kelompok_id' => $request->get('kelompok_id_input'),
            'tanggal_absensi' => $request->get('tanggal_absensi_input', now()->toDateString()),
        ];

        if ($isPembimbing && !empty($pembimbingAuthId)) {
            $filters['pembimbing_id'] = (string) $pembimbingAuthId;
        }

        $query = AbsensiPembekalan::with(['pembimbing', 'siswa.kelas', 'siswa.kelompokBimbingan'])
            ->latest('tanggal_absensi')
            ->latest('id');

        if ($isPembimbing) {
            if (empty($pembimbingAuthId)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('pembimbing_id', $pembimbingAuthId);
            }
        }

        if (!empty($filters['tanggal_awal'])) {
            $query->whereDate('tanggal_absensi', '>=', $filters['tanggal_awal']);
        }

        if (!empty($filters['tanggal_akhir'])) {
            $query->whereDate('tanggal_absensi', '<=', $filters['tanggal_akhir']);
        }

        if (!empty($filters['pembimbing_id'])) {
            $query->where('pembimbing_id', $filters['pembimbing_id']);
        }

        if (!empty($filters['kelompok_id'])) {
            $query->whereHas('siswa.kelompokBimbingan', function ($q) use ($filters) {
                $q->where('kelompok_bimbingan.id', $filters['kelompok_id']);
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['keyword'])) {
            $keyword = trim((string) $filters['keyword']);
            $query->where(function ($q) use ($keyword) {
                $q->where('keterangan', 'like', '%' . $keyword . '%')
                    ->orWhereHas('siswa', function ($sq) use ($keyword) {
                        $sq->where('nama_siswa', 'like', '%' . $keyword . '%');
                    })
                    ->orWhereHas('pembimbing', function ($pq) use ($keyword) {
                        $pq->where('nama_pembimbing', 'like', '%' . $keyword . '%');
                    })
                    ->orWhereHas('siswa.kelompokBimbingan', function ($kq) use ($keyword) {
                        $kq->where('nama_kelompok', 'like', '%' . $keyword . '%');
                    });
            });
        }

        $absensi = $query->get();
        $pembimbingOptionsQuery = Pembimbing::orderBy('nama_pembimbing');
        if ($isPembimbing) {
            if (empty($pembimbingAuthId)) {
                $pembimbingOptionsQuery->whereRaw('1 = 0');
            } else {
                $pembimbingOptionsQuery->whereKey($pembimbingAuthId);
            }
        }
        $pembimbingOptions = $pembimbingOptionsQuery->get(['id', 'nama_pembimbing', 'nip_pembimbing']);

        $siswaOptionsQuery = Siswa::with(['kelas', 'kelompokBimbingan.pembimbing'])
            ->orderBy('nama_siswa');
        if ($isPembimbing) {
            if (empty($pembimbingAuthId)) {
                $siswaOptionsQuery->whereRaw('1 = 0');
            } else {
                $siswaOptionsQuery->whereHas('kelompokBimbingan', function ($q) use ($pembimbingAuthId) {
                    $q->where('kelompok_bimbingan.pembimbing_id', $pembimbingAuthId);
                });
            }
        }
        $siswaOptions = $siswaOptionsQuery->get(['id', 'nama_siswa', 'kelas_id']);

        $kelompokOptionsQuery = KelompokBimbingan::with('pembimbing')
            ->withCount('siswa')
            ->orderBy('nama_kelompok');

        if ($isPembimbing) {
            if (empty($pembimbingAuthId)) {
                $kelompokOptionsQuery->whereRaw('1 = 0');
            } else {
                $kelompokOptionsQuery->where('pembimbing_id', $pembimbingAuthId);
            }
        }

        $kelompokOptions = $kelompokOptionsQuery->get();

        $selectedKelompok = null;
        $selectedKelompokStudents = collect();

        if (!empty($bulkInput['kelompok_id'])) {
            $selectedKelompokQuery = KelompokBimbingan::with(['pembimbing', 'siswa.kelas'])
                ->whereKey($bulkInput['kelompok_id']);

            if ($isPembimbing && !empty($pembimbingAuthId)) {
                $selectedKelompokQuery->where('pembimbing_id', $pembimbingAuthId);
            }

            $selectedKelompok = $selectedKelompokQuery->first();

            if ($selectedKelompok) {
                $studentIds = $selectedKelompok->siswa->pluck('id')->all();

                $existingAbsensi = AbsensiPembekalan::query()
                    ->whereDate('tanggal_absensi', $bulkInput['tanggal_absensi'])
                    ->whereIn('siswa_id', $studentIds)
                    ->get()
                    ->keyBy('siswa_id');

                $selectedKelompokStudents = $selectedKelompok->siswa
                    ->sortBy('nama_siswa')
                    ->values()
                    ->map(function ($siswa) use ($existingAbsensi) {
                        return (object) [
                            'siswa' => $siswa,
                            'absensi' => $existingAbsensi->get($siswa->id),
                        ];
                    });
            }
        }

        return view('pembekalan.absensi', compact(
            'absensi',
            'filters',
            'pembimbingOptions',
            'siswaOptions',
            'canManageAbsensi',
            'kelompokOptions',
            'bulkInput',
            'selectedKelompok',
            'selectedKelompokStudents',
            'showInputSection',
            'showRiwayatSection'
        ));
    }

    public function pageBulkStore(Request $request)
    {
        $pembimbingAuthId = $this->getAuthorizedPembimbingForAbsensiPage();

        $validated = $request->validate([
            'kelompok_id' => ['required', 'exists:kelompok_bimbingan,id'],
            'tanggal_absensi' => ['required', 'date'],
            'siswa_ids' => ['required', 'array', 'min:1'],
            'siswa_ids.*' => ['required', 'exists:siswa,id'],
            'statuses' => ['required', 'array'],
            'statuses.*' => ['required', 'in:hadir,izin,alpa'],
            'keterangans' => ['nullable', 'array'],
            'keterangans.*' => ['nullable', 'string', 'max:1000'],
        ]);

        $kelompok = KelompokBimbingan::with('siswa:id')->findOrFail($validated['kelompok_id']);

        if (!empty($pembimbingAuthId) && (int) $kelompok->pembimbing_id !== (int) $pembimbingAuthId) {
            throw new UnauthorizedException('Anda tidak berwenang mengisi absensi untuk kelompok ini.');
        }

        $allowedSiswaIds = $kelompok->siswa->pluck('id')->map(fn($id) => (int) $id)->all();
        $submittedSiswaIds = collect($validated['siswa_ids'])->map(fn($id) => (int) $id)->all();
        $invalidSiswaIds = array_diff($submittedSiswaIds, $allowedSiswaIds);

        if (!empty($invalidSiswaIds)) {
            throw ValidationException::withMessages([
                'siswa_ids' => 'Terdapat siswa yang bukan anggota kelompok terpilih.',
            ]);
        }

        $createdCount = 0;
        $updatedCount = 0;
        $pembimbingIdToSave = !empty($pembimbingAuthId) ? $pembimbingAuthId : (int) $kelompok->pembimbing_id;

        foreach ($submittedSiswaIds as $siswaId) {
            $status = $validated['statuses'][$siswaId] ?? null;
            if (empty($status)) {
                throw ValidationException::withMessages([
                    'statuses' => 'Status absensi untuk semua siswa wajib dipilih.',
                ]);
            }

            $this->validatePembimbingForSiswa((int) $siswaId, (int) $pembimbingIdToSave);

            $absensi = AbsensiPembekalan::updateOrCreate(
                [
                    'siswa_id' => $siswaId,
                    'tanggal_absensi' => $validated['tanggal_absensi'],
                ],
                [
                    'pembimbing_id' => $pembimbingIdToSave,
                    'status' => $status,
                    'keterangan' => $validated['keterangans'][$siswaId] ?? null,
                ]
            );

            if ($absensi->wasRecentlyCreated) {
                $createdCount++;
            } else {
                $updatedCount++;
            }
        }

        $message = "Absensi kelompok berhasil disimpan. {$createdCount} data ditambahkan, {$updatedCount} data diperbarui.";

        return redirect()->route('pembekalan.absensi.input', [
            'kelompok_id_input' => $validated['kelompok_id'],
            'tanggal_absensi_input' => $validated['tanggal_absensi'],
        ])->with('success', $message);
    }

    public function pageStore(Request $request)
    {
        $pembimbingAuthId = $this->getAuthorizedPembimbingForAbsensiPage();

        $validated = $request->validate([
            'pembimbing_id' => ['required', 'exists:pembimbings,id'],
            'siswa_id' => ['required', 'exists:siswa,id'],
            'tanggal_absensi' => ['required', 'date'],
            'status' => ['required', 'in:hadir,izin,alpa'],
            'keterangan' => ['nullable', 'string'],
        ]);

        if (!empty($pembimbingAuthId)) {
            $validated['pembimbing_id'] = $pembimbingAuthId;
        }

        $this->validatePembimbingForSiswa((int) $validated['siswa_id'], (int) $validated['pembimbing_id']);

        $absensi = AbsensiPembekalan::updateOrCreate(
            [
                'siswa_id' => $validated['siswa_id'],
                'tanggal_absensi' => $validated['tanggal_absensi'],
            ],
            $validated
        );

        $message = $absensi->wasRecentlyCreated
            ? 'Absensi berhasil ditambahkan.'
            : 'Absensi pada tanggal tersebut sudah ada, data diperbarui.';

        return redirect()->route('pembekalan.absensi.riwayat')->with('success', $message);
    }

    public function pageUpdate(Request $request, AbsensiPembekalan $absensiPembekalan)
    {
        $pembimbingAuthId = $this->getAuthorizedPembimbingForAbsensiPage();

        $validated = $request->validate([
            'pembimbing_id' => ['required', 'exists:pembimbings,id'],
            'siswa_id' => ['required', 'exists:siswa,id'],
            'tanggal_absensi' => ['required', 'date'],
            'status' => ['required', 'in:hadir,izin,alpa'],
            'keterangan' => ['nullable', 'string'],
        ]);

        if (!empty($pembimbingAuthId)) {
            if ((int) $absensiPembekalan->pembimbing_id !== (int) $pembimbingAuthId) {
                throw new UnauthorizedException('Anda tidak berwenang mengubah absensi ini.');
            }
            $validated['pembimbing_id'] = $pembimbingAuthId;
        }

        $this->validatePembimbingForSiswa((int) $validated['siswa_id'], (int) $validated['pembimbing_id']);

        $existsConflict = AbsensiPembekalan::query()
            ->where('siswa_id', $validated['siswa_id'])
            ->whereDate('tanggal_absensi', $validated['tanggal_absensi'])
            ->whereKeyNot($absensiPembekalan->id)
            ->exists();

        if ($existsConflict) {
            return back()->withErrors([
                'tanggal_absensi' => 'Siswa sudah memiliki data absensi pada tanggal tersebut.',
            ])->withInput();
        }

        $absensiPembekalan->update($validated);

        return redirect()->route('pembekalan.absensi.riwayat')->with('success', 'Absensi berhasil diperbarui.');
    }

    public function pageDestroy(AbsensiPembekalan $absensiPembekalan)
    {
        $pembimbingAuthId = $this->getAuthorizedPembimbingForAbsensiPage();
        if (!empty($pembimbingAuthId) && (int) $absensiPembekalan->pembimbing_id !== (int) $pembimbingAuthId) {
            throw new UnauthorizedException('Anda tidak berwenang menghapus absensi ini.');
        }

        $absensiPembekalan->delete();

        return redirect()->route('pembekalan.absensi.riwayat')->with('success', 'Absensi berhasil dihapus.');
    }

    public function index(Request $request)
    {
        $query = AbsensiPembekalan::with(['pembimbing', 'siswa'])->latest('tanggal_absensi');

        if ($request->filled('pembimbing_id')) {
            $query->where('pembimbing_id', $request->pembimbing_id);
        }

        if ($request->filled('siswa_id')) {
            $query->where('siswa_id', $request->siswa_id);
        }

        return response()->json($query->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pembimbing_id' => ['required', 'exists:pembimbings,id'],
            'siswa_id' => ['required', 'exists:siswa,id'],
            'tanggal_absensi' => ['required', 'date'],
            'status' => ['required', 'in:hadir,izin,alpa'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $this->validatePembimbingForSiswa((int) $validated['siswa_id'], (int) $validated['pembimbing_id']);

        $absensi = AbsensiPembekalan::updateOrCreate(
            [
                'siswa_id' => $validated['siswa_id'],
                'tanggal_absensi' => $validated['tanggal_absensi'],
            ],
            $validated
        );

        return response()->json($absensi, 201);
    }

    public function show(AbsensiPembekalan $absensiPembekalan)
    {
        return response()->json($absensiPembekalan->load(['pembimbing', 'siswa']));
    }

    public function update(Request $request, AbsensiPembekalan $absensiPembekalan)
    {
        $validated = $request->validate([
            'pembimbing_id' => ['required', 'exists:pembimbings,id'],
            'siswa_id' => ['required', 'exists:siswa,id'],
            'tanggal_absensi' => ['required', 'date'],
            'status' => ['required', 'in:hadir,izin,alpa'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $this->validatePembimbingForSiswa((int) $validated['siswa_id'], (int) $validated['pembimbing_id']);

        $absensiPembekalan->update($validated);

        return response()->json($absensiPembekalan);
    }

    public function destroy(AbsensiPembekalan $absensiPembekalan)
    {
        $absensiPembekalan->delete();

        return response()->json(['message' => 'Absensi deleted']);
    }

    private function validatePembimbingForSiswa(int $siswaId, int $pembimbingId): void
    {
        $hasKelompok = Siswa::query()
            ->whereKey($siswaId)
            ->whereHas('kelompokBimbingan')
            ->exists();

        if (!$hasKelompok) {
            throw ValidationException::withMessages([
                'siswa_id' => 'Siswa belum memiliki kelompok bimbingan. Silakan atur kelompok bimbingan terlebih dahulu.',
            ]);
        }

        $isMatch = Siswa::query()
            ->whereKey($siswaId)
            ->whereHas('kelompokBimbingan', function ($query) use ($pembimbingId) {
                $query->where('kelompok_bimbingan.pembimbing_id', $pembimbingId);
            })
            ->exists();

        if (!$isMatch) {
            throw ValidationException::withMessages([
                'pembimbing_id' => 'Pembimbing yang dipilih tidak sesuai dengan kelompok bimbingan siswa.',
            ]);
        }
    }

    private function getAuthorizedPembimbingForAbsensiPage(): ?int
    {
        $authUser = auth()->user();
        if (!$authUser) {
            throw new UnauthorizedException('Silakan login terlebih dahulu.');
        }

        if (Gate::forUser($authUser)->allows('panitia')) {
            return null;
        }

        if (Gate::forUser($authUser)->allows('pembimbing') || $authUser->role === 'pembimbing') {
            $pembimbingAuthId = Pembimbing::query()
                ->where('nip_pembimbing', (string) $authUser->username)
                ->value('id');

            if (empty($pembimbingAuthId)) {
                throw new UnauthorizedException('Data pembimbing untuk akun ini tidak ditemukan.');
            }

            return (int) $pembimbingAuthId;
        }

        throw new UnauthorizedException('Anda tidak memiliki akses ke modul absensi ini.');
    }

    private function resolveFormulirData(Request $request): array
    {
        $pembimbingAuthId = $this->getAuthorizedPembimbingForAbsensiPage();

        $validated = $request->validate([
            'pembimbing_id' => ['nullable', 'exists:pembimbings,id'],
            'kelompok_id' => ['nullable', 'exists:kelompok_bimbingan,id'],
            'tanggal_formulir' => ['nullable', 'date'],
        ]);

        $authUser = auth()->user();
        $isPanitia = $authUser && Gate::forUser($authUser)->allows('panitia');

        $filters = [
            'pembimbing_id' => $validated['pembimbing_id'] ?? null,
            'kelompok_id' => $validated['kelompok_id'] ?? null,
            'tanggal_formulir' => $validated['tanggal_formulir'] ?? now()->toDateString(),
        ];

        if (!empty($pembimbingAuthId)) {
            $filters['pembimbing_id'] = (string) $pembimbingAuthId;
        }

        $pembimbingOptionsQuery = Pembimbing::query()->orderBy('nama_pembimbing');
        if (!empty($pembimbingAuthId)) {
            $pembimbingOptionsQuery->whereKey($pembimbingAuthId);
        }
        $pembimbingOptions = $pembimbingOptionsQuery->get(['id', 'nama_pembimbing']);

        $kelompokOptionsQuery = KelompokBimbingan::with('pembimbing')
            ->withCount('siswa')
            ->orderBy('nama_kelompok');

        if (!empty($pembimbingAuthId)) {
            $kelompokOptionsQuery->where('pembimbing_id', $pembimbingAuthId);
        } elseif (!empty($filters['pembimbing_id'])) {
            $kelompokOptionsQuery->where('pembimbing_id', $filters['pembimbing_id']);
        }

        $kelompokOptions = $kelompokOptionsQuery->get();

        $selectedKelompok = null;
        $students = collect();

        if (!empty($filters['kelompok_id'])) {
            $selectedKelompokQuery = KelompokBimbingan::with(['pembimbing', 'siswa.kelas'])
                ->whereKey($filters['kelompok_id']);

            if (!empty($pembimbingAuthId)) {
                $selectedKelompokQuery->where('pembimbing_id', $pembimbingAuthId);
            }

            $selectedKelompok = $selectedKelompokQuery->first();

            if (!$selectedKelompok) {
                throw ValidationException::withMessages([
                    'kelompok_id' => 'Kelompok tidak ditemukan atau tidak dapat diakses.',
                ]);
            }

            $students = $selectedKelompok->siswa
                ->sortBy('nama_siswa')
                ->values();
        }

        $selectedPembimbing = null;
        if (!empty($filters['pembimbing_id'])) {
            $selectedPembimbing = $pembimbingOptions->firstWhere('id', (int) $filters['pembimbing_id']);
        }

        return [
            'filters' => $filters,
            'pembimbingOptions' => $pembimbingOptions,
            'kelompokOptions' => $kelompokOptions,
            'selectedKelompok' => $selectedKelompok,
            'students' => $students,
            'isPanitia' => $isPanitia,
            'selectedPembimbing' => $selectedPembimbing,
        ];
    }
}
