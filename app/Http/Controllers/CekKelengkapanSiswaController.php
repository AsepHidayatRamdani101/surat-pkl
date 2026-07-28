<?php

namespace App\Http\Controllers;

use App\Models\AbsensiPembekalan;
use App\Models\CekKelengkapanSiswa;
use App\Models\KelengkapanSiswaItem;
use App\Models\KelompokBimbingan;
use App\Models\Pembimbing;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;

class CekKelengkapanSiswaController extends Controller
{
    public function index(Request $request)
    {
        $pembimbingAuthId = $this->getAuthorizedPembimbingId();

        $filters = [
            'tanggal_awal' => $request->get('tanggal_awal'),
            'tanggal_akhir' => $request->get('tanggal_akhir'),
            'pembimbing_id' => $request->get('pembimbing_id'),
            'kelompok_id' => $request->get('kelompok_id'),
            'sesi_cek' => $request->get('sesi_cek'),
            'status_kelengkapan' => $request->get('status_kelengkapan'),
            'keyword' => $request->get('keyword'),
        ];

        if (!empty($pembimbingAuthId)) {
            $filters['pembimbing_id'] = (string) $pembimbingAuthId;
        }

        $bulkInput = [
            'kelompok_id' => $request->get('kelompok_id_input'),
            'tanggal_cek' => $request->get('tanggal_cek_input', now()->toDateString()),
            'sesi_cek' => $request->get('sesi_cek_input', 'datang'),
        ];

        $items = KelengkapanSiswaItem::query()
            ->where('is_active', true)
            ->orderBy('urutan')
            ->orderBy('nama_item')
            ->get(['id', 'nama_item', 'deskripsi', 'urutan']);

        $records = $this->buildFilteredQuery($filters, $pembimbingAuthId)->get();

        $pembimbingOptionsQuery = Pembimbing::query()->orderBy('nama_pembimbing');
        if (!empty($pembimbingAuthId)) {
            $pembimbingOptionsQuery->whereKey($pembimbingAuthId);
        }
        $pembimbingOptions = $pembimbingOptionsQuery->get(['id', 'nama_pembimbing']);

        $kelompokOptionsQuery = KelompokBimbingan::with(['pembimbing', 'pembimbings'])
            ->withCount('siswa')
            ->orderBy('nama_kelompok');

        if (!empty($pembimbingAuthId)) {
            $this->scopeKelompokForPembimbing($kelompokOptionsQuery, $pembimbingAuthId);
        }

        $kelompokOptions = $kelompokOptionsQuery->get();

        if (!empty($pembimbingAuthId) && empty($bulkInput['kelompok_id'])) {
            $bulkInput['kelompok_id'] = $kelompokOptions->first()?->id;
        }

        return view('pembekalan.kelengkapan', [
            'records' => $records,
            'filters' => $filters,
            'bulkInput' => $bulkInput,
            'items' => $items,
            'pembimbingOptions' => $pembimbingOptions,
            'kelompokOptions' => $kelompokOptions,
            'isPembimbingOnly' => !empty($pembimbingAuthId),
        ]);
    }

    public function getDashboardCardCounts(Request $request)
    {
        $pembimbingAuthId = $this->getAuthorizedPembimbingId();

        $filters = [
            'tanggal_awal' => $request->get('tanggal_awal'),
            'tanggal_akhir' => $request->get('tanggal_akhir'),
            'pembimbing_id' => $request->get('pembimbing_id'),
            'kelompok_id' => $request->get('kelompok_id'),
            'sesi_cek' => $request->get('sesi_cek'),
            'status_kelengkapan' => $request->get('status_kelengkapan'),
            'keyword' => $request->get('keyword'),
        ];

        $records = $this->buildFilteredQuery($filters, $pembimbingAuthId)->get();

        // Calculate statistics
        $lengkapCount = $records->where('is_lengkap', true)->count();
        $belumLengkapCount = $records->where('is_lengkap', false)->count();
        
        // Get distinct counts
        $guruSudahInput = $records->pluck('pembimbing_id')->unique()->count();
        $siswaTerinput = $records->pluck('siswa_id')->unique()->count();

        return response()->json([
            'siswa_lengkap' => $lengkapCount,
            'siswa_belum_lengkap' => $belumLengkapCount,
            'guru_sudah_input' => $guruSudahInput,
            'guru_belum_input' => 0, // Will be calculated on frontend or handled separately
            'siswa_terinput' => $siswaTerinput,
            'siswa_belum_input' => 0, // Will be calculated on frontend or handled separately
        ]);
    }

    public function inputStudents(Request $request)
    {
        $pembimbingAuthId = $this->getAuthorizedPembimbingId();

        $validated = $request->validate([
            'kelompok_id' => ['required', 'exists:kelompok_bimbingan,id'],
            'tanggal_cek' => ['required', 'date'],
            'sesi_cek' => ['required', 'in:datang,pulang'],
        ]);

        $items = KelengkapanSiswaItem::query()
            ->where('is_active', true)
            ->orderBy('urutan')
            ->orderBy('nama_item')
            ->get(['id', 'nama_item', 'deskripsi']);

        if ($items->isEmpty()) {
            return response()->json([
                'message' => 'Daftar kelengkapan belum diinput oleh admin.',
                'items' => [],
                'students' => [],
            ]);
        }

        $selectedKelompokQuery = KelompokBimbingan::with(['pembimbing', 'pembimbings', 'siswa.kelas'])
            ->whereKey($validated['kelompok_id']);

        if (!empty($pembimbingAuthId)) {
            $this->scopeKelompokForPembimbing($selectedKelompokQuery, $pembimbingAuthId);
        }

        $selectedKelompok = $selectedKelompokQuery->first();

        if (!$selectedKelompok) {
            return response()->json([
                'message' => 'Kelompok tidak ditemukan atau tidak dapat diakses.',
            ], 404);
        }

        $studentIds = $selectedKelompok->siswa->pluck('id')->all();

        $existingChecks = CekKelengkapanSiswa::query()
            ->whereDate('tanggal_cek', $validated['tanggal_cek'])
            ->where('sesi_cek', $validated['sesi_cek'])
            ->whereIn('siswa_id', $studentIds)
            ->get()
            ->keyBy('siswa_id');

        $students = $selectedKelompok->siswa
            ->sortBy('nama_siswa')
            ->values()
            ->map(function ($siswa) use ($existingChecks) {
                $record = $existingChecks->get($siswa->id);
                $checkedItemIds = collect($record?->item_checks ?? [])
                    ->filter(fn($item) => !empty($item['is_checked']))
                    ->pluck('id')
                    ->map(fn($id) => (int) $id)
                    ->values()
                    ->all();

                return [
                    'siswa_id' => (int) $siswa->id,
                    'nama_siswa' => (string) $siswa->nama_siswa,
                    'kelas' => $siswa->kelas ? (string) $siswa->kelas->nama_kelas : null,
                    'checked_item_ids' => $checkedItemIds,
                    'catatan' => (string) ($record?->catatan ?? ''),
                    'is_lengkap' => (bool) ($record?->is_lengkap ?? false),
                    'has_record' => $record !== null,
                ];
            })
            ->values();

        return response()->json([
            'kelompok' => [
                'id' => (int) $selectedKelompok->id,
                'nama_kelompok' => (string) $selectedKelompok->nama_kelompok,
                'pembimbing' => $selectedKelompok->pembimbing?->nama_pembimbing
                    ?? $selectedKelompok->pembimbings->pluck('nama_pembimbing')->join(', '),
            ],
            'tanggal_cek' => (string) $validated['tanggal_cek'],
            'sesi_cek' => (string) $validated['sesi_cek'],
            'items' => $items,
            'students' => $students,
        ]);
    }

    public function bulkStore(Request $request)
    {
        $pembimbingAuthId = $this->getAuthorizedPembimbingId();

        $validated = $request->validate([
            'kelompok_id' => ['required', 'exists:kelompok_bimbingan,id'],
            'tanggal_cek' => ['required', 'date'],
            'sesi_cek' => ['required', 'in:datang,pulang'],
            'siswa_ids' => ['required', 'array', 'min:1'],
            'siswa_ids.*' => ['required', 'exists:siswa,id'],
            'catatans' => ['nullable', 'array'],
            'catatans.*' => ['nullable', 'string', 'max:1000'],
            'item_checks' => ['nullable', 'array'],
        ]);

        $items = KelengkapanSiswaItem::query()
            ->where('is_active', true)
            ->orderBy('urutan')
            ->orderBy('nama_item')
            ->get(['id', 'nama_item']);

        if ($items->isEmpty()) {
            throw ValidationException::withMessages([
                'kelengkapan' => 'Daftar kelengkapan belum diinput oleh admin.',
            ]);
        }

        $kelompok = KelompokBimbingan::with(['siswa:id', 'pembimbings:id'])->findOrFail($validated['kelompok_id']);

        $mentorIds = $kelompok->pembimbings->pluck('id')->map(fn($id) => (int) $id)->all();
        if (empty($mentorIds) && !empty($kelompok->pembimbing_id)) {
            $mentorIds = [(int) $kelompok->pembimbing_id];
        }

        if (!empty($pembimbingAuthId) && !in_array((int) $pembimbingAuthId, $mentorIds, true)) {
            throw new UnauthorizedException('Anda tidak berwenang mengisi kelengkapan untuk kelompok ini.');
        }

        $allowedSiswaIds = $kelompok->siswa->pluck('id')->map(fn($id) => (int) $id)->all();
        $submittedSiswaIds = collect($validated['siswa_ids'])->map(fn($id) => (int) $id)->all();
        $invalidSiswaIds = array_diff($submittedSiswaIds, $allowedSiswaIds);

        if (!empty($invalidSiswaIds)) {
            throw ValidationException::withMessages([
                'siswa_ids' => 'Terdapat siswa yang bukan anggota kelompok terpilih.',
            ]);
        }

        $pembimbingIdToSave = !empty($pembimbingAuthId)
            ? $pembimbingAuthId
            : (!empty($kelompok->pembimbing_id) ? (int) $kelompok->pembimbing_id : (int) ($mentorIds[0] ?? 0));
        $itemChecksInput = $request->input('item_checks', []);
        $createdCount = 0;
        $updatedCount = 0;

        foreach ($submittedSiswaIds as $siswaId) {
            $this->validatePembimbingForSiswa($siswaId, $pembimbingIdToSave);

            $checkedItemIds = collect($itemChecksInput[$siswaId] ?? [])
                ->filter(fn($value) => (string) $value === '1')
                ->keys()
                ->map(fn($id) => (int) $id)
                ->values()
                ->all();

            $itemChecksPayload = $items->map(function ($item) use ($checkedItemIds) {
                return [
                    'id' => (int) $item->id,
                    'nama_item' => (string) $item->nama_item,
                    'is_checked' => in_array((int) $item->id, $checkedItemIds, true),
                ];
            })->all();

            $isLengkap = count($checkedItemIds) === $items->count();

            $record = CekKelengkapanSiswa::updateOrCreate(
                [
                    'siswa_id' => $siswaId,
                    'tanggal_cek' => $validated['tanggal_cek'],
                    'sesi_cek' => $validated['sesi_cek'],
                ],
                [
                    'pembimbing_id' => $pembimbingIdToSave,
                    'item_checks' => $itemChecksPayload,
                    'is_lengkap' => $isLengkap,
                    'catatan' => $validated['catatans'][$siswaId] ?? null,
                ]
            );

            AbsensiPembekalan::query()
                ->where('siswa_id', $siswaId)
                ->whereDate('tanggal_absensi', $validated['tanggal_cek'])
                ->where('sesi_absensi', $validated['sesi_cek'])
                ->update(['atribut_lengkap' => $isLengkap]);

            if ($record->wasRecentlyCreated) {
                $createdCount++;
            } else {
                $updatedCount++;
            }
        }

        return redirect()->route('pembekalan.kelengkapan', [
            'kelompok_id_input' => $validated['kelompok_id'],
            'tanggal_cek_input' => $validated['tanggal_cek'],
            'sesi_cek_input' => $validated['sesi_cek'],
        ])->with('success', "Cek kelengkapan siswa berhasil disimpan. {$createdCount} data ditambahkan, {$updatedCount} data diperbarui.");
    }

    public function laporan(Request $request)
    {
        $filters = [
            'pembimbing_id' => $request->get('pembimbing_id'),
            'tanggal_awal' => $request->get('tanggal_awal'),
            'tanggal_akhir' => $request->get('tanggal_akhir'),
            'sesi_cek' => $request->get('sesi_cek'),
            'status_kelengkapan' => $request->get('status_kelengkapan'),
        ];

        $query = CekKelengkapanSiswa::with(['pembimbing', 'siswa.kelas', 'siswa.kelompokBimbingan'])
            ->latest('tanggal_cek')
            ->latest('id');

        if (!empty($filters['pembimbing_id'])) {
            $query->where('pembimbing_id', $filters['pembimbing_id']);
        }

        if (!empty($filters['tanggal_awal'])) {
            $query->whereDate('tanggal_cek', '>=', $filters['tanggal_awal']);
        }

        if (!empty($filters['tanggal_akhir'])) {
            $query->whereDate('tanggal_cek', '<=', $filters['tanggal_akhir']);
        }

        if (!empty($filters['sesi_cek'])) {
            $query->where('sesi_cek', $filters['sesi_cek']);
        }

        if ($filters['status_kelengkapan'] === 'lengkap') {
            $query->where('is_lengkap', true);
        } elseif ($filters['status_kelengkapan'] === 'belum') {
            $query->where('is_lengkap', false);
        }

        $data = $query->get();

        $summary = [
            'total_cek' => $data->count(),
            'lengkap' => $data->where('is_lengkap', true)->count(),
            'belum_lengkap' => $data->where('is_lengkap', false)->count(),
            'siswa_unik' => $data->pluck('siswa_id')->unique()->count(),
            'pembimbing_unik' => $data->pluck('pembimbing_id')->unique()->count(),
        ];

        $pembimbingOptions = Pembimbing::orderBy('nama_pembimbing')->get(['id', 'nama_pembimbing']);
        $items = KelengkapanSiswaItem::where('is_active', true)->orderBy('urutan')->orderBy('nama_item')->get();

        return view('pembekalan.laporan_kelengkapan', compact(
            'data',
            'summary',
            'filters',
            'pembimbingOptions',
            'items'
        ));
    }

    private function buildFilteredQuery(array $filters, ?int $pembimbingAuthId)
    {
        $query = CekKelengkapanSiswa::with(['pembimbing.kelompokBimbingan', 'siswa.kelas', 'siswa.kelompokBimbingan'])
            ->latest('tanggal_cek')
            ->latest('id');

        if (!empty($pembimbingAuthId)) {
            $query->where('pembimbing_id', $pembimbingAuthId);
        }

        if (!empty($filters['tanggal_awal'])) {
            $query->whereDate('tanggal_cek', '>=', $filters['tanggal_awal']);
        }

        if (!empty($filters['tanggal_akhir'])) {
            $query->whereDate('tanggal_cek', '<=', $filters['tanggal_akhir']);
        }

        if (!empty($filters['pembimbing_id'])) {
            $query->where('pembimbing_id', $filters['pembimbing_id']);
        }

        if (!empty($filters['kelompok_id'])) {
            $query->whereHas('siswa.kelompokBimbingan', function ($q) use ($filters) {
                $q->where('kelompok_bimbingan.id', $filters['kelompok_id']);
            });
        }

        if (!empty($filters['sesi_cek'])) {
            $query->where('sesi_cek', $filters['sesi_cek']);
        }

        if ($filters['status_kelengkapan'] !== null && $filters['status_kelengkapan'] !== '') {
            $query->where('is_lengkap', $filters['status_kelengkapan'] === 'lengkap');
        }

        if (!empty($filters['keyword'])) {
            $keyword = trim((string) $filters['keyword']);
            $query->where(function ($q) use ($keyword) {
                $q->where('catatan', 'like', '%' . $keyword . '%')
                    ->orWhereHas('siswa', function ($siswaQuery) use ($keyword) {
                        $siswaQuery->where('nama_siswa', 'like', '%' . $keyword . '%');
                    })
                    ->orWhereHas('pembimbing', function ($pembimbingQuery) use ($keyword) {
                        $pembimbingQuery->where('nama_pembimbing', 'like', '%' . $keyword . '%');
                    });
            });
        }

        return $query;
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
                $query->where(function ($kelompokQuery) use ($pembimbingId) {
                    $kelompokQuery->where('kelompok_bimbingan.pembimbing_id', $pembimbingId)
                        ->orWhereHas('pembimbings', function ($mentorQuery) use ($pembimbingId) {
                            $mentorQuery->where('pembimbings.id', $pembimbingId);
                        });
                });
            })
            ->exists();

        if (!$isMatch) {
            throw ValidationException::withMessages([
                'pembimbing_id' => 'Pembimbing yang dipilih tidak sesuai dengan kelompok bimbingan siswa.',
            ]);
        }
    }

    private function getAuthorizedPembimbingId(): ?int
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

        throw new UnauthorizedException('Anda tidak memiliki akses ke modul kelengkapan siswa ini.');
    }

    private function scopeKelompokForPembimbing($query, int $pembimbingId): void
    {
        $query->where(function ($kelompokQuery) use ($pembimbingId) {
            $kelompokQuery->where('pembimbing_id', $pembimbingId)
                ->orWhereHas('pembimbings', function ($mentorQuery) use ($pembimbingId) {
                    $mentorQuery->where('pembimbings.id', $pembimbingId);
                });
        });
    }
}