<?php

namespace App\Http\Controllers;

use App\Models\AbsensiControl;
use App\Models\AbsensiPkl;
use App\Models\Pembimbing;
use App\Models\ResumePklBulanan;
use App\Models\Siswa;
use App\Models\UploadAbsensiJurnalPkl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PklController extends Controller
{
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
            'siswaOptions' => $definition['type'] !== 'setting' ? $this->siswaOptions($pembimbingId) : collect(),
            'pembimbingOptions' => auth()->user()->role === 'panitia' ? Pembimbing::orderBy('nama_pembimbing')->get() : collect(),
            'setting' => $definition['type'] === 'setting' ? AbsensiControl::first() : null,
        ]);
    }

    public function data(string $module)
    {
        $definition = $this->definition($module);
        if ($definition['type'] === 'setting') {
            return response()->json(AbsensiControl::first());
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
                'interval_hari' => ['required', 'integer', 'in:14'],
            ]);
            $data['updated_by'] = auth()->id();
            return response()->json(AbsensiControl::updateOrCreate(['id' => 1], $data));
        }

        $record = $this->newRecord($definition['type'], $request);
        return response()->json($record, 201);
    }

    public function update(Request $request, string $module, int $id)
    {
        $definition = $this->definition($module);
        abort_if($definition['type'] === 'setting', 405);
        $record = $this->findRecord($definition['type'], $id);
        $record->update($this->validatedRecord($definition['type'], $request, $record));

        return response()->json($record->fresh());
    }

    public function destroy(string $module, int $id)
    {
        $definition = $this->definition($module);
        abort_if($definition['type'] === 'setting', 405);
        $record = $this->findRecord($definition['type'], $id);
        if (!empty($record->file_path)) {
            Storage::disk('public')->delete($record->file_path);
        }
        $record->delete();

        return response()->json(['message' => 'Data berhasil dihapus.']);
    }

    private function newRecord(string $type, Request $request)
    {
        $data = $this->validatedRecord($type, $request);
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
                'file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
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
        unset($data['file']);
        $data['siswa_id'] = $siswaId;

        if ($type === 'absensi') {
            $data['pembimbing_id'] = auth()->user()->role === 'pembimbing'
                ? $this->pembimbingId()
                : $request->validate(['pembimbing_id' => ['required', 'exists:pembimbings,id']])['pembimbing_id'];
        } elseif (auth()->user()->role !== 'siswa' && $request->filled('status')) {
            $data['status'] = $request->input('status');
        }

        if ($request->hasFile('file')) {
            if ($record?->file_path) {
                Storage::disk('public')->delete($record->file_path);
            }
            $data['file_path'] = $request->file('file')->store('pkl', 'public');
        }

        return $data;
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
            return $query->whereHas('siswa.kelompokBimbingan', fn (Builder $builder) => $builder->where('kelompok_bimbingan.pembimbing_id', $pembimbingId));
        }

        return $query;
    }

    private function findRecord(string $type, int $id)
    {
        return $this->recordsQuery($type)->findOrFail($id);
    }

    private function siswaOptions(?int $pembimbingId)
    {
        $query = Siswa::with('kelas')->orderBy('nama_siswa');
        if ($pembimbingId) {
            $query->whereHas('kelompokBimbingan', fn (Builder $builder) => $builder->where('kelompok_bimbingan.pembimbing_id', $pembimbingId));
        }
        return $query->get();
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