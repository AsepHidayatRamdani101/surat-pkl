<?php

namespace App\Http\Controllers;

use App\Models\AbsensiControl;
use App\Models\AbsensiJurnalRekap;
use App\Models\AbsensiPkl;
use App\Models\Pembimbing;
use App\Models\ResumePklBulanan;
use App\Models\Siswa;
use App\Models\UploadAbsensiJurnalPkl;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PklController extends Controller
{
    private const PKL_INTERVAL_DAYS = 30;

    private const MODULES = [
        'setting-absensi' => ['title' => 'Setting Absen PKL', 'roles' => ['panitia'], 'type' => 'setting'],
        'absensi-pembimbing' => ['title' => 'Data Absensi oleh Pembimbing', 'roles' => ['panitia'], 'type' => 'absensi'],
        'input-absensi' => ['title' => 'Input Absensi PKL', 'roles' => ['pembimbing'], 'type' => 'absensi'],
        'resume' => ['title' => 'Data Resume PKL per Bulan', 'roles' => ['panitia', 'pembimbing'], 'type' => 'resume'],
        'input-resume' => ['title' => 'Input Resume PKL Bulanan', 'roles' => ['siswa'], 'type' => 'resume'],
        'upload-absensi-jurnal' => ['title' => 'Data Upload Absensi Jurnal PKL', 'roles' => ['panitia', 'pembimbing', 'siswa'], 'type' => 'jurnal'],
    ];

    public function index(string $module)
    {
        $definition = $this->definition($module);
        $pembimbingId = $this->pembimbingId();

        return view('pkl.index', [
            'title' => $definition['title'],
            'module' => $module,
            'type' => $definition['type'],
            'isPanitia' => auth()->user()->role === 'panitia',
            'isSiswa' => auth()->user()->role === 'siswa',
            'isPembimbing' => auth()->user()->role === 'pembimbing',
            'siswaOptions' => $definition['type'] !== 'setting' ? $this->siswaOptions($pembimbingId) : collect(),
            'pembimbingOptions' => auth()->user()->role === 'panitia' ? Pembimbing::orderBy('nama_pembimbing')->get() : collect(),
            'setting' => $definition['type'] === 'setting' ? AbsensiControl::first() : null,
        ]);
    }

    public function data(Request $request, string $module)
    {
        $definition = $this->definition($module);
        if ($definition['type'] === 'setting') {
            return response()->json(AbsensiControl::first());
        }

        if ($definition['type'] === 'resume' && in_array(auth()->user()->role, ['pembimbing', 'panitia'], true)) {
            return $this->resumeBimbingan($request);
        }

        $records = $this->recordsQuery($definition['type'])
            ->latest($definition['type'] === 'absensi' ? 'periode_mulai' : ($definition['type'] === 'resume' ? 'bulan' : 'tanggal'))
            ->get();

        return response()->json($records);
    }

    public function store(Request $request, string $module)
    {
        $definition = $this->definition($module);
        if ($definition['type'] === 'setting') {
            $data = $request->validate([
                'is_active' => ['required', 'boolean'],
                'periode_dimulai_pada' => ['nullable', 'date'],
                'interval_hari' => ['required', 'integer', 'in:' . self::PKL_INTERVAL_DAYS],
            ]);
            $data['updated_by'] = auth()->id();
            return response()->json(AbsensiControl::updateOrCreate(['id' => 1], $data));
        }

        $record = $this->newRecord($definition['type'], $request);
        $status = $definition['type'] === 'resume' && !$record->wasRecentlyCreated ? 200 : 201;
        return response()->json($record, $status);
    }

    public function update(Request $request, string $module, int $id)
    {
        $definition = $this->definition($module);
        abort_if($definition['type'] === 'setting', 405);
        $record = $this->findRecord($definition['type'], $id);
        $record->update($this->validatedRecord($definition['type'], $request, $record));
        if ($definition['type'] === 'resume') {
            $this->appendResumeFiles($record, $request->file('files', []) ?? []);
        }

        return response()->json($record->fresh());
    }

    public function destroy(string $module, int $id)
    {
        $definition = $this->definition($module);
        abort_if($definition['type'] === 'setting', 405);
        $record = $this->findRecord($definition['type'], $id);
        $filePaths = $definition['type'] === 'resume'
            ? $record->attachments
            : array_filter([$record->file_path]);
        if ($filePaths) {
            Storage::disk('public')->delete($filePaths);
        }
        $record->delete();

        return response()->json(['message' => 'Data berhasil dihapus.']);
    }

    public function jurnalRekap(Request $request)
    {
        abort_unless(in_array(auth()->user()->role, ['pembimbing', 'panitia'], true), 403);

        $requested = $request->input('periode_mulai');
        $periodeMulai = $requested
            ? Carbon::parse($requested)->startOfMonth()
            : Carbon::today()->startOfMonth();
        $periodeSelesai = $periodeMulai->copy()->endOfMonth();

        $pembimbingId = $this->selectedPembimbingId($request);
        $siswaList = $this->siswaOptions($pembimbingId);
        $siswaIds = $siswaList->pluck('id');

        $rekapMap = AbsensiJurnalRekap::whereIn('siswa_id', $siswaIds)
            ->where('periode_mulai', $periodeMulai->format('Y-m-d'))
            ->get()
            ->keyBy('siswa_id');

        $berkasMap = UploadAbsensiJurnalPkl::whereIn('siswa_id', $siswaIds)
            ->whereBetween('tanggal', [$periodeMulai->format('Y-m-d'), $periodeSelesai->format('Y-m-d')])
            ->orderBy('tanggal')
            ->get()
            ->groupBy('siswa_id');

        $rows = $siswaList->map(function ($siswa) use ($rekapMap, $berkasMap, $pembimbingId) {
            $rekap = $rekapMap->get($siswa->id);
            return [
                'siswa_id' => $siswa->id,
                'nama_siswa' => $siswa->nama_siswa,
                'kelas' => optional($siswa->kelas)->nama_kelas,
                'kelompok' => $this->resolveKelompokNama($siswa, $pembimbingId),
                'jumlah_hadir' => $rekap->jumlah_hadir ?? 0,
                'jumlah_izin' => $rekap->jumlah_izin ?? 0,
                'jumlah_sakit' => $rekap->jumlah_sakit ?? 0,
                'jumlah_alpa' => $rekap->jumlah_alpa ?? 0,
                'berkas' => ($berkasMap->get($siswa->id) ?? collect())->map(fn ($item) => [
                    'tanggal' => $item->tanggal->format('Y-m-d'),
                    'judul' => $item->judul,
                    'file_path' => $item->file_path,
                ])->values(),
            ];
        });

        $data = $rows->sortBy([['kelompok', 'asc'], ['nama_siswa', 'asc']])->values();

        return response()->json([
            'periode_mulai' => $periodeMulai->format('Y-m-d'),
            'periode_selesai' => $periodeSelesai->format('Y-m-d'),
            'interval_hari' => $periodeMulai->daysInMonth,
            'data' => $data,
        ]);
    }

    private function resumeBimbingan(Request $request)
    {
        $bulan = $request->filled('bulan')
            ? Carbon::createFromFormat('Y-m', $request->input('bulan'))->startOfMonth()
            : Carbon::now()->startOfMonth();

        $pembimbingId = $this->selectedPembimbingId($request);
        $siswaList = $this->siswaOptions($pembimbingId);
        $siswaIds = $siswaList->pluck('id');

        $resumeMap = ResumePklBulanan::whereIn('siswa_id', $siswaIds)
            ->whereYear('bulan', $bulan->year)
            ->whereMonth('bulan', $bulan->month)
            ->latest('updated_at')
            ->get()
            ->keyBy('siswa_id');

        $rows = $siswaList->map(function ($siswa) use ($resumeMap, $pembimbingId, $bulan) {
            $resume = $resumeMap->get($siswa->id);
            return [
                'id' => $resume->id ?? null,
                'siswa_id' => $siswa->id,
                'nama_siswa' => $siswa->nama_siswa,
                'kelas' => optional($siswa->kelas)->nama_kelas,
                'kelompok' => $this->resolveKelompokNama($siswa, $pembimbingId),
                'bulan' => $bulan->format('Y-m'),
                'judul' => $resume->judul ?? null,
                'isi_resume' => $resume->isi_resume ?? null,
                'file_paths' => $resume->attachments ?? [],
                'status' => $resume->status ?? null,
            ];
        });

        return response()->json([
            'bulan' => $bulan->format('Y-m'),
            'data' => $rows->sortBy([['kelompok', 'asc'], ['nama_siswa', 'asc']])->values(),
        ]);
    }

    private function resolveKelompokNama($siswa, ?int $pembimbingId): string
    {
        $kelompok = $siswa->kelompokBimbingan
            ->first(fn ($k) => !$pembimbingId
                || (int) $k->pembimbing_id === $pembimbingId
                || $k->pembimbings->pluck('id')->contains($pembimbingId))
            ?? $siswa->kelompokBimbingan->first();

        return $kelompok->nama_kelompok ?? 'Belum ada kelompok';
    }

    private function selectedPembimbingId(Request $request): ?int
    {
        if (auth()->user()->role === 'pembimbing') {
            return $this->pembimbingId();
        }

        if (auth()->user()->role === 'panitia' && $request->filled('pembimbing_id')) {
            return (int) $request->validate(['pembimbing_id' => ['exists:pembimbings,id']])['pembimbing_id'];
        }

        return null;
    }

    public function simpanJurnalRekap(Request $request)
    {
        abort_unless(in_array(auth()->user()->role, ['pembimbing', 'panitia'], true), 403);

        $data = $request->validate([
            'siswa_id' => ['required', 'exists:siswa,id'],
            'periode_mulai' => ['required', 'date'],
            'periode_selesai' => ['required', 'date', 'after_or_equal:periode_mulai'],
            'jumlah_hadir' => ['required', 'integer', 'min:0'],
            'jumlah_izin' => ['required', 'integer', 'min:0'],
            'jumlah_sakit' => ['required', 'integer', 'min:0'],
            'jumlah_alpa' => ['required', 'integer', 'min:0'],
        ]);

        $pembimbingId = $this->pembimbingId();
        if ($pembimbingId) {
            abort_unless($this->siswaOptions($pembimbingId)->pluck('id')->contains((int) $data['siswa_id']), 403);
        }

        $data['updated_by'] = auth()->id();
        $rekap = AbsensiJurnalRekap::updateOrCreate(
            ['siswa_id' => $data['siswa_id'], 'periode_mulai' => $data['periode_mulai']],
            $data
        );

        return response()->json($rekap);
    }

    public function simpanJurnalRekapBulk(Request $request)
    {
        abort_unless(in_array(auth()->user()->role, ['pembimbing', 'panitia'], true), 403);

        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.siswa_id' => ['required', 'exists:siswa,id'],
            'items.*.periode_mulai' => ['required', 'date'],
            'items.*.periode_selesai' => ['required', 'date', 'after_or_equal:items.*.periode_mulai'],
            'items.*.jumlah_hadir' => ['required', 'integer', 'min:0'],
            'items.*.jumlah_izin' => ['required', 'integer', 'min:0'],
            'items.*.jumlah_sakit' => ['required', 'integer', 'min:0'],
            'items.*.jumlah_alpa' => ['required', 'integer', 'min:0'],
        ]);

        $pembimbingId = $this->pembimbingId();
        $allowedSiswaIds = $pembimbingId ? $this->siswaOptions($pembimbingId)->pluck('id') : null;
        $userId = auth()->id();

        foreach ($data['items'] as $item) {
            if ($allowedSiswaIds !== null) {
                abort_unless($allowedSiswaIds->contains((int) $item['siswa_id']), 403);
            }
            $item['updated_by'] = $userId;
            AbsensiJurnalRekap::updateOrCreate(
                ['siswa_id' => $item['siswa_id'], 'periode_mulai' => $item['periode_mulai']],
                $item
            );
        }

        return response()->json(['message' => 'Rekap absensi berhasil disimpan.', 'count' => count($data['items'])]);
    }

    private function newRecord(string $type, Request $request)
    {
        $data = $this->validatedRecord($type, $request);

        if ($type === 'resume') {
            $data['bulan'] = Carbon::createFromFormat('Y-m', $data['bulan'])
                ->startOfMonth()
                ->toDateString();
            $existing = ResumePklBulanan::where('siswa_id', $data['siswa_id'])
                ->whereDate('bulan', $data['bulan'])
                ->first();
            $record = ResumePklBulanan::updateOrCreate(
                ['siswa_id' => $data['siswa_id'], 'bulan' => $data['bulan']],
                $data
            );
            $this->appendResumeFiles($record, $request->file('files', []) ?? []);

            return $record;
        }

        return $this->modelClass($type)::create($data);
    }

    private function validatedRecord(string $type, Request $request, $record = null): array
    {
        $siswaId = $this->requestedSiswaId($request);
        $journalFileRule = $record
            ? ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120']
            : ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'];
        $rules = match ($type) {
            'absensi' => [
                'periode_mulai' => ['required', 'date'],
                'periode_selesai' => ['required', 'date', 'after_or_equal:periode_mulai'],
                'status' => ['required', Rule::in(['hadir', 'izin', 'sakit', 'alpa'])],
                'keterangan' => ['nullable', 'string', 'max:2000'],
            ],
            'resume' => [
                'bulan' => ['required', 'date_format:Y-m'],
                'judul' => ['required', 'string', 'max:150'],
                'isi_resume' => ['required', 'string', 'max:10000'],
                'files' => ['nullable', 'array', 'max:10'],
                'files.*' => ['file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'],
                'status' => ['nullable', Rule::in(['dikirim', 'ditinjau', 'disetujui', 'perlu_revisi'])],
            ],
            default => [
                'tanggal' => ['required', 'date'],
                'judul' => ['required', 'string', 'max:150'],
                'keterangan' => ['nullable', 'string', 'max:2000'],
                'file' => $journalFileRule,
                'status' => ['nullable', Rule::in(['dikirim', 'ditinjau', 'disetujui', 'perlu_revisi'])],
            ],
        };
        $data = $request->validate($rules);
        unset($data['file'], $data['files']);
        $data['siswa_id'] = $siswaId;

        if ($type === 'absensi') {
            $data['pembimbing_id'] = auth()->user()->role === 'pembimbing'
                ? $this->pembimbingId()
                : $request->validate(['pembimbing_id' => ['required', 'exists:pembimbings,id']])['pembimbing_id'];
        } elseif (auth()->user()->role !== 'siswa' && $request->filled('status')) {
            $data['status'] = $request->input('status');
        }

        if ($type !== 'resume' && $request->hasFile('file')) {
            if ($record?->file_path) {
                Storage::disk('public')->delete($record->file_path);
            }
            $data['file_path'] = $request->file('file')->store('pkl', 'public');
        }

        return $data;
    }

    private function appendResumeFiles(ResumePklBulanan $record, array $files): void
    {
        if (!$files) {
            return;
        }

        $newPaths = [];
        try {
            foreach ($files as $file) {
                $path = $file->store('pkl', 'public');
                if (!$path) {
                    throw new \RuntimeException('Lampiran resume gagal disimpan.');
                }
                $newPaths[] = $path;
            }

            $record->file_paths = array_values(array_unique(array_merge(
                $record->file_paths ?? [],
                $newPaths
            )));
            $record->save();
        } catch (\Throwable $exception) {
            if ($newPaths) {
                Storage::disk('public')->delete($newPaths);
            }
            throw $exception;
        }
    }

    private function requestedSiswaId(Request $request): int
    {
        if (auth()->user()->role === 'siswa') {
            $siswaId = Siswa::where('nis', (string) auth()->user()->username)->value('id');
            abort_if(!$siswaId, 403, 'Data siswa untuk akun ini tidak ditemukan.');
            return (int) $siswaId;
        }

        $siswaId = $request->validate(['siswa_id' => ['required', 'exists:siswa,id']])['siswa_id'];
        $pembimbingId = $this->pembimbingId();
        if ($pembimbingId) {
            abort_unless($this->siswaOptions($pembimbingId)->pluck('id')->contains((int) $siswaId), 403);
        }
        return (int) $siswaId;
    }

    private function recordsQuery(string $type): Builder
    {
        $query = $this->modelClass($type)::with($type === 'absensi' ? ['siswa.kelas', 'pembimbing'] : ['siswa.kelas']);
        $user = auth()->user();

        if ($user->role === 'siswa') {
            return $query->where('siswa_id', Siswa::where('nis', (string) $user->username)->value('id'));
        }

        if ($user->role === 'pembimbing') {
            $pembimbingId = $this->pembimbingId();
            if ($type === 'absensi') {
                return $query->where('pembimbing_id', $pembimbingId);
            }
            return $query->whereHas('siswa.kelompokBimbingan', fn (Builder $builder) => $this->scopeKelompokForPembimbing($builder, $pembimbingId));
        }

        return $query;
    }

    private function findRecord(string $type, int $id)
    {
        return $this->recordsQuery($type)->findOrFail($id);
    }

    private function siswaOptions(?int $pembimbingId)
    {
        $query = Siswa::with(['kelas', 'kelompokBimbingan.pembimbings'])->orderBy('nama_siswa');
        if ($pembimbingId) {
            $query->whereHas('kelompokBimbingan', fn (Builder $builder) => $this->scopeKelompokForPembimbing($builder, $pembimbingId));
        }
        return $query->get();
    }

    private function scopeKelompokForPembimbing(Builder $query, int $pembimbingId): Builder
    {
        return $query->where(function (Builder $kelompokQuery) use ($pembimbingId) {
            $kelompokQuery->where('kelompok_bimbingan.pembimbing_id', $pembimbingId)
                ->orWhereHas('pembimbings', fn (Builder $mentorQuery) => $mentorQuery->where('pembimbings.id', $pembimbingId));
        });
    }

    private function pembimbingId(): ?int
    {
        if (auth()->user()->role !== 'pembimbing') {
            return null;
        }
        $id = Pembimbing::where('nip_pembimbing', (string) auth()->user()->username)->value('id');
        abort_if(!$id, 403, 'Data pembimbing untuk akun ini tidak ditemukan.');
        return (int) $id;
    }

    private function modelClass(string $type): string
    {
        return match ($type) {
            'absensi' => AbsensiPkl::class,
            'resume' => ResumePklBulanan::class,
            'jurnal' => UploadAbsensiJurnalPkl::class,
        };
    }

    private function definition(string $module): array
    {
        $definition = self::MODULES[$module] ?? abort(404);
        abort_unless(in_array(auth()->user()->role, $definition['roles'], true), 403);
        return $definition;
    }
}