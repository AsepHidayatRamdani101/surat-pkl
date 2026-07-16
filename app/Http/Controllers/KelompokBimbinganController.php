<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\KelompokBimbingan;
use App\Models\Kelas;
use App\Models\Pembimbing;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class KelompokBimbinganController extends Controller
{
    public function index()
    {
        $authUser = auth()->user();
        $isPembimbing = $authUser && (Gate::forUser($authUser)->allows('pembimbing') || $authUser->role === 'pembimbing');
        $canManageKelompok = !$isPembimbing;
        $pembimbingAuthId = null;

        if ($isPembimbing) {
            $pembimbingAuthId = Pembimbing::query()
                ->where('nip_pembimbing', (string) $authUser->username)
                ->value('id');
        }

        $pembimbingQuery = Pembimbing::with('jurusan')
            ->orderByRaw("CASE WHEN jenis_guru = 'guru_produktif' THEN 0 ELSE 1 END")
            ->orderBy('nama_pembimbing');
        if ($isPembimbing) {
            if (empty($pembimbingAuthId)) {
                $pembimbingQuery->whereRaw('1 = 0');
            } else {
                $pembimbingQuery->whereKey($pembimbingAuthId);
            }
        }
        $pembimbing = $pembimbingQuery->get();

        $mentorAssignedSiswaMap = [];
        $kelompokForQuota = KelompokBimbingan::query()
            ->withCount('siswa')
            ->with(['pembimbings:id', 'pembimbing:id'])
            ->get();

        foreach ($kelompokForQuota as $kelompokItem) {
            $mentorIds = $kelompokItem->pembimbings->pluck('id')->map(fn($id) => (int) $id)->all();
            if (empty($mentorIds) && !empty($kelompokItem->pembimbing_id)) {
                $mentorIds = [(int) $kelompokItem->pembimbing_id];
            }

            foreach (array_unique($mentorIds) as $mentorId) {
                $mentorAssignedSiswaMap[$mentorId] = (int) ($mentorAssignedSiswaMap[$mentorId] ?? 0) + (int) ($kelompokItem->siswa_count ?? 0);
            }
        }

        $pembimbingWithQuota = $pembimbing
            ->map(function ($mentor) use ($mentorAssignedSiswaMap) {
                $kuotaJumlahSiswa = (int) ($mentor->jumlah_siswa ?? 0);
                if ($kuotaJumlahSiswa <= 0) {
                    $kuotaJumlahSiswa = $this->calculatePembimbingQuota((int) ($mentor->jumlah_jam ?? 0));
                }

                $assigned = (int) ($mentorAssignedSiswaMap[(int) $mentor->id] ?? 0);
                $sisaKuota = $kuotaJumlahSiswa > 0 ? max(0, $kuotaJumlahSiswa - $assigned) : null;

                $mentor->setAttribute('kuota_total', $kuotaJumlahSiswa > 0 ? $kuotaJumlahSiswa : null);
                $mentor->setAttribute('assigned_count', $assigned);
                $mentor->setAttribute('sisa_kuota', $sisaKuota);
                $mentor->setAttribute('selisih_kuota', $kuotaJumlahSiswa > 0 ? ($assigned - $kuotaJumlahSiswa) : null);

                return $mentor;
            })
            ->sortBy(function ($mentor) {
                return mb_strtolower((string) $mentor->nama_pembimbing);
            })
            ->values();

        $pembimbingAssignable = $pembimbingWithQuota
            ->filter(function ($mentor) {
                // Keep mentors with unknown quota, otherwise keep only those with remaining quota.
                return is_null($mentor->sisa_kuota) || (int) $mentor->sisa_kuota > 0;
            })
            ->values();

        $pembimbingKelebihanKuota = $pembimbingWithQuota
            ->filter(function ($mentor) {
                return !is_null($mentor->kuota_total)
                    && (int) $mentor->assigned_count > (int) $mentor->kuota_total;
            })
            ->values();

        $pembimbingKekuranganKuota = $pembimbingWithQuota
            ->filter(function ($mentor) {
                return !is_null($mentor->kuota_total)
                    && (int) $mentor->assigned_count < (int) $mentor->kuota_total;
            })
            ->values();

        $kelasNamaById = Kelas::query()
            ->pluck('nama_kelas', 'id')
            ->mapWithKeys(fn($nama, $id) => [(string) $id => (string) $nama])
            ->all();

        $pembimbingMeta = $pembimbing->mapWithKeys(function ($mentor) use ($kelasNamaById) {
            $support = $this->resolveMentorClassSupport($mentor);
            $kelasIds = collect($support['kelas_ids'] ?? [])->map(fn($id) => (string) $id)->values()->all();
            $kelasNames = collect($kelasIds)
                ->map(fn($id) => $kelasNamaById[$id] ?? null)
                ->filter()
                ->values()
                ->all();
            $kuotaJumlahSiswa = (int) ($mentor->jumlah_siswa ?? 0);
            if ($kuotaJumlahSiswa <= 0) {
                $kuotaJumlahSiswa = $this->calculatePembimbingQuota((int) ($mentor->jumlah_jam ?? 0));
            }

            return [
                (string) $mentor->id => [
                    'all' => (bool) ($support['all'] ?? false),
                    'kelas_ids' => $kelasIds,
                    'kelas_names' => $kelasNames,
                    'jurusan_id' => $mentor->jurusan_id ? (string) $mentor->jurusan_id : null,
                    'jurusan_nama' => $mentor->jurusan->nama_jurusan ?? ($support['all'] ? 'Semua Jurusan' : '-'),
                    'kuota_siswa' => $kuotaJumlahSiswa,
                ],
            ];
        })->all();

        $siswaQuery = Siswa::with('kelas.jurusan')
            ->orderBy('nama_siswa');
        if ($canManageKelompok) {
            // Untuk form manual, tampilkan hanya siswa yang belum punya kelompok agar tidak double.
            $siswaQuery->whereDoesntHave('kelompokBimbingan');
        }
        if ($isPembimbing) {
            if (empty($pembimbingAuthId)) {
                $siswaQuery->whereRaw('1 = 0');
            } else {
                $siswaQuery->whereHas('kelompokBimbingan', function ($query) use ($pembimbingAuthId) {
                    $query->where(function ($builder) use ($pembimbingAuthId) {
                        $builder->where('kelompok_bimbingan.pembimbing_id', $pembimbingAuthId)
                            ->orWhereHas('pembimbings', function ($mentorQuery) use ($pembimbingAuthId) {
                                $mentorQuery->where('pembimbings.id', $pembimbingAuthId);
                            });
                    });
                });
            }
        }
        $siswa = $siswaQuery->get();

        $kelompokQuery = KelompokBimbingan::with(['pembimbing.jurusan', 'pembimbings.jurusan', 'siswa.kelas.jurusan', 'siswa.suratIzin.perusahaan'])
            ->withCount('siswa')
            ->latest();
        if ($isPembimbing) {
            if (empty($pembimbingAuthId)) {
                $kelompokQuery->whereRaw('1 = 0');
            } else {
                $kelompokQuery->where(function ($query) use ($pembimbingAuthId) {
                    $query->where('pembimbing_id', $pembimbingAuthId)
                        ->orWhereHas('pembimbings', function ($mentorQuery) use ($pembimbingAuthId) {
                            $mentorQuery->where('pembimbings.id', $pembimbingAuthId);
                        });
                });
            }
        }
        $filters = [
            'kelompok_id' => request('kelompok_id'),
            'pembimbing_id' => request('pembimbing_id'),
            'keyword' => request('keyword'),
        ];

        if (!empty($filters['kelompok_id'])) {
            $kelompokQuery->whereKey($filters['kelompok_id']);
        }

        if (!empty($filters['pembimbing_id'])) {
            $kelompokQuery->where(function ($query) use ($filters) {
                $query->where('pembimbing_id', $filters['pembimbing_id'])
                    ->orWhereHas('pembimbings', function ($mentorQuery) use ($filters) {
                        $mentorQuery->where('pembimbings.id', $filters['pembimbing_id']);
                    });
            });
        }

        if (!empty($filters['keyword'])) {
            $keyword = trim((string) $filters['keyword']);
            $kelompokQuery->where(function ($query) use ($keyword) {
                $query->where('nama_kelompok', 'like', '%' . $keyword . '%')
                    ->orWhereHas('pembimbing', function ($pembimbingQuery) use ($keyword) {
                        $pembimbingQuery->where('nama_pembimbing', 'like', '%' . $keyword . '%');
                    })
                    ->orWhereHas('siswa', function ($siswaQuery) use ($keyword) {
                        $siswaQuery->where('nama_siswa', 'like', '%' . $keyword . '%')
                            ->orWhereHas('kelas', function ($kelasQuery) use ($keyword) {
                                $kelasQuery->where('nama_kelas', 'like', '%' . $keyword . '%');
                            });
                    });
            });
        }
        $kelompok = $kelompokQuery->get();
        $kelompokTableRows = $this->buildKelompokGroupedRows($kelompok);

        $kelompokBelumKuotaOptions = $canManageKelompok
            ? KelompokBimbingan::query()
                ->withCount('siswa')
                ->having('siswa_count', '<', 8)
                ->orderBy('nama_kelompok')
                ->get()
            : collect();

        return view('kelompok_bimbingan.index', compact('pembimbing', 'pembimbingAssignable', 'pembimbingKelebihanKuota', 'pembimbingKekuranganKuota', 'siswa', 'kelompok', 'kelompokTableRows', 'canManageKelompok', 'filters', 'pembimbingMeta', 'kelompokBelumKuotaOptions'));
    }

    public function storeManual(Request $request)
    {
        $validated = $request->validate([
            'kelompok_id' => 'nullable|exists:kelompok_bimbingan,id',
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'required|exists:siswa,id',
            'pembimbing_id' => 'required|exists:pembimbings,id',
        ]);

        $mentorId = (int) $validated['pembimbing_id'];
        $mentor = Pembimbing::query()->with('jurusan')->findOrFail($mentorId);

        $mentorSupport = $this->resolveMentorClassSupport($mentor);
        $allowAllClass = (bool) ($mentorSupport['all'] ?? false);
        $allowedClassIds = collect($mentorSupport['kelas_ids'] ?? [])
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (!$allowAllClass) {
            $invalidStudentCount = Siswa::query()
                ->whereIn('id', $validated['siswa_ids'])
                ->where(function ($query) use ($allowedClassIds) {
                    $query->whereNull('kelas_id');

                    if (empty($allowedClassIds)) {
                        return;
                    }

                    $query->orWhereNotIn('kelas_id', $allowedClassIds);
                })
                ->count();

            if ($invalidStudentCount > 0) {
                return back()->withInput()->with('error', 'Ada siswa di luar kelas yang diampu pembimbing terpilih. Periksa pilihan siswa atau ganti pembimbing.');
            }
        }

        $alreadyAssignedQuery = DB::table('kelompok_bimbingan_siswa')
            ->whereIn('siswa_id', $validated['siswa_ids']);

        if (!empty($validated['kelompok_id'])) {
            $alreadyAssignedQuery->where('kelompok_bimbingan_id', '!=', (int) $validated['kelompok_id']);
        }

        $alreadyAssigned = $alreadyAssignedQuery->pluck('siswa_id')->all();

        if (!empty($alreadyAssigned)) {
            return back()->withInput()->with('error', 'Sebagian siswa sudah masuk kelompok lain.');
        }

        $mentorQuota = (int) ($mentor->jumlah_siswa ?? 0);
        if ($mentorQuota <= 0) {
            $mentorQuota = $this->calculatePembimbingQuota((int) ($mentor->jumlah_jam ?? 0));
        }

        if ($mentorQuota <= 0) {
            return back()->withInput()->with('error', 'Kuota pembimbing belum tersedia. Isi jumlah siswa atau jumlah jam pembimbing terlebih dahulu.');
        }

        $assigned = DB::transaction(function () use ($validated, $mentorId, $mentorQuota) {

            if (!empty($validated['kelompok_id'])) {
                $kelompok = KelompokBimbingan::query()
                    ->lockForUpdate()
                    ->findOrFail((int) $validated['kelompok_id']);

                $currentCount = (int) $kelompok->siswa()->count();
                $newCount = $currentCount + count($validated['siswa_ids']);
                if ($newCount > $mentorQuota) {
                    return false;
                }

                $kelompok->update([
                    'pembimbing_id' => $mentorId,
                    'metode' => 'manual',
                    'created_by' => auth()->id(),
                ]);
            } else {
                if (count($validated['siswa_ids']) > $mentorQuota) {
                    return false;
                }

                $kelompok = KelompokBimbingan::create([
                    'nama_kelompok' => 'Kelompok ' . ((int) KelompokBimbingan::query()->count() + 1),
                    'pembimbing_id' => $mentorId,
                    'metode' => 'manual',
                    'created_by' => auth()->id(),
                ]);
            }

            $kelompok->siswa()->syncWithoutDetaching($validated['siswa_ids']);
            $kelompok->pembimbings()->sync([$mentorId]);

            return true;
        });

        if (!$assigned) {
            return back()->withInput()->with('error', 'Jumlah siswa pada kelompok melebihi kuota pembimbing terpilih.');
        }

        return redirect()->route('kelompok-bimbingan.index')->with('success', 'Kelompok manual berhasil diisi.');
    }

    public function generateKelompokKosong()
    {
        $totalSiswa = Siswa::query()->count();
        if ($totalSiswa <= 0) {
            return back()->with('error', 'Data siswa tidak ditemukan.');
        }

        $dummySiswa = range(1, $totalSiswa);
        $chunks = $this->buildGroupChunks($dummySiswa, 8, 13);
        $jumlahKelompok = max(1, count($chunks['chunks']));

        DB::transaction(function () use ($jumlahKelompok) {
            KelompokBimbingan::query()->delete();

            for ($i = 1; $i <= $jumlahKelompok; $i++) {
                KelompokBimbingan::create([
                    'nama_kelompok' => 'Kelompok ' . $i,
                    'pembimbing_id' => null,
                    'metode' => 'manual',
                    'created_by' => auth()->id(),
                ]);
            }
        });

        return redirect()->route('kelompok-bimbingan.index')
            ->with('success', 'Generate kelompok berhasil. Dibuat ' . $jumlahKelompok . ' kelompok kosong berdasarkan total siswa dengan aturan 8-13 siswa per kelompok.');
    }

    public function generateAutomatic()
    {
        $allSiswa = Siswa::with('kelas')
            ->orderBy('kelas_id')
            ->orderBy('nama_siswa')
            ->get();

        if ($allSiswa->isEmpty()) {
            return back()->with('error', 'Data siswa tidak ditemukan.');
        }

        $mentorList = Pembimbing::with('jurusan')
            ->orderByRaw("CASE WHEN jenis_guru = 'guru_produktif' THEN 0 ELSE 1 END")
            ->orderBy('nama_pembimbing')
            ->get();

        if ($mentorList->isEmpty()) {
            return back()->with('error', 'Data pembimbing belum tersedia.');
        }

        $remainingQuota = [];
        $mentorSupportMap = [];
        foreach ($mentorList as $mentor) {
            $quota = (int) ($mentor->jumlah_siswa ?? 0);
            if ($quota <= 0) {
                $quota = $this->calculatePembimbingQuota((int) ($mentor->jumlah_jam ?? 0));
            }

            if ($quota <= 0) {
                continue;
            }

            $remainingQuota[(int) $mentor->id] = $quota;
            $mentorSupportMap[(int) $mentor->id] = $this->resolveMentorClassSupport($mentor);
        }

        if (empty($remainingQuota)) {
            return back()->with('error', 'Kuota siswa pembimbing tidak tersedia. Isi jumlah siswa atau jumlah jam pembimbing terlebih dahulu.');
        }

        $unassigned = $allSiswa->pluck('id')->map(fn($id) => (int) $id)->all();
        $assignedTotal = 0;

        DB::transaction(function () use (&$unassigned, &$assignedTotal, $mentorList, $mentorSupportMap, &$remainingQuota) {
            KelompokBimbingan::query()->delete();
            $nomorKelompok = 1;

            foreach ($mentorList as $mentor) {
                $mentorId = (int) $mentor->id;
                $quota = (int) ($remainingQuota[$mentorId] ?? 0);
                if ($quota <= 0) {
                    continue;
                }

                $support = $mentorSupportMap[$mentorId] ?? ['all' => true, 'kelas_ids' => []];
                $candidateStudents = Siswa::query()
                    ->whereIn('id', $unassigned)
                    ->when(!($support['all'] ?? false), function ($query) use ($support) {
                        $kelasIds = collect($support['kelas_ids'] ?? [])->map(fn($id) => (int) $id)->all();
                        if (empty($kelasIds)) {
                            $query->whereRaw('1 = 0');
                        } else {
                            $query->whereIn('kelas_id', $kelasIds);
                        }
                    })
                    ->orderBy('kelas_id')
                    ->orderBy('nama_siswa')
                    ->get(['id', 'kelas_id'])
                    ->map(function ($item) {
                        return [
                            'id' => (int) $item->id,
                            'kelas_id' => !is_null($item->kelas_id) ? (int) $item->kelas_id : 0,
                        ];
                    })
                    ->all();

                $eligible = $this->takeStudentsBalancedByClass($candidateStudents, $quota);

                if (empty($eligible)) {
                    continue;
                }

                $kelompok = KelompokBimbingan::create([
                    'nama_kelompok' => 'Kelompok ' . $nomorKelompok,
                    'pembimbing_id' => $mentorId,
                    'metode' => 'otomatis',
                    'created_by' => auth()->id(),
                ]);

                $kelompok->siswa()->sync($eligible);
                $kelompok->pembimbings()->sync([$mentorId]);

                $assignedTotal += count($eligible);
                $unassigned = array_values(array_diff($unassigned, $eligible));
                $remainingQuota[$mentorId] = max(0, $quota - count($eligible));
                $nomorKelompok++;

                if (empty($unassigned)) {
                    break;
                }
            }
        });

        if ($assignedTotal === 0) {
            return redirect()->route('kelompok-bimbingan.index')->with('error', 'Tidak ada siswa yang dapat dipetakan. Periksa kelas yang diampu dan kuota pembimbing.');
        }

        if (!empty($unassigned)) {
            return redirect()->route('kelompok-bimbingan.index')->with('warning', 'Generate otomatis berhasil, namun masih ada ' . count($unassigned) . ' siswa belum teralokasi karena kuota pembimbing tidak cukup atau kelas tidak sesuai.');
        }

        return redirect()->route('kelompok-bimbingan.index')->with('success', 'Generate otomatis berhasil. Ketentuan diterapkan: satu kelompok satu pembimbing, kapasitas kelompok mengikuti kuota pembimbing.');
    }

    private function takeStudentsBalancedByClass(array $students, int $limit): array
    {
        if ($limit <= 0 || empty($students)) {
            return [];
        }

        $classBuckets = [];
        $classOrder = [];

        foreach ($students as $student) {
            $classKey = (int) ($student['kelas_id'] ?? 0);
            if (!isset($classBuckets[$classKey])) {
                $classBuckets[$classKey] = [];
                $classOrder[] = $classKey;
            }

            $classBuckets[$classKey][] = (int) ($student['id'] ?? 0);
        }

        if (empty($classOrder)) {
            return [];
        }

        $picked = [];
        while (count($picked) < $limit) {
            $pickedThisRound = false;

            foreach ($classOrder as $classKey) {
                if (count($picked) >= $limit) {
                    break;
                }

                if (!empty($classBuckets[$classKey])) {
                    $picked[] = array_shift($classBuckets[$classKey]);
                    $pickedThisRound = true;
                }
            }

            if (!$pickedThisRound) {
                break;
            }
        }

        return $picked;
    }

    public function addAnggota(Request $request, int $id)
    {
        $kelompok = KelompokBimbingan::with(['pembimbings', 'siswa'])->findOrFail($id);

        $validated = $request->validate([
            'pembimbing_id' => 'nullable|exists:pembimbings,id',
            'siswa_ids' => 'nullable|array',
            'siswa_ids.*' => 'required|exists:siswa,id',
        ]);

        $mentorId = !empty($validated['pembimbing_id']) ? (int) $validated['pembimbing_id'] : null;

        $siswaIds = collect($validated['siswa_ids'] ?? [])
            ->map(fn($value) => (int) $value)
            ->unique()
            ->values();

        if (is_null($mentorId) && $siswaIds->isEmpty()) {
            return back()->with('error', 'Pilih minimal satu pembimbing atau satu siswa untuk ditambahkan.');
        }

        $alreadyAssignedElsewhere = [];
        if ($siswaIds->isNotEmpty()) {
            $alreadyAssignedElsewhere = DB::table('kelompok_bimbingan_siswa')
                ->whereIn('siswa_id', $siswaIds->all())
                ->where('kelompok_bimbingan_id', '!=', $kelompok->id)
                ->pluck('siswa_id')
                ->all();
        }

        if (!empty($alreadyAssignedElsewhere)) {
            return back()->with('error', 'Sebagian siswa sudah masuk kelompok lain. Silakan pilih siswa yang belum memiliki kelompok.');
        }

        $finalMentorId = $mentorId ?: (int) ($kelompok->pembimbing_id ?? 0);
        if ($finalMentorId <= 0) {
            return back()->with('error', 'Kelompok belum memiliki pembimbing. Pilih pembimbing terlebih dahulu.');
        }

        $mentor = Pembimbing::find($finalMentorId);
        if (!$mentor) {
            return back()->with('error', 'Data pembimbing tidak ditemukan.');
        }

        $mentorQuota = (int) ($mentor->jumlah_siswa ?? 0);
        if ($mentorQuota <= 0) {
            $mentorQuota = $this->calculatePembimbingQuota((int) ($mentor->jumlah_jam ?? 0));
        }

        if ($mentorQuota <= 0) {
            return back()->with('error', 'Kuota pembimbing belum tersedia. Isi jumlah siswa atau jumlah jam pembimbing terlebih dahulu.');
        }

        $currentStudentCount = (int) $kelompok->siswa()->count();
        $newStudentCount = $currentStudentCount + $siswaIds->count();
        if ($newStudentCount > $mentorQuota) {
            return back()->with('error', 'Jumlah siswa pada kelompok melebihi kuota pembimbing terpilih.');
        }

        DB::transaction(function () use ($kelompok, $mentorId, $finalMentorId, $siswaIds) {
            if (!is_null($mentorId)) {
                $kelompok->pembimbing_id = (int) $mentorId;
                $kelompok->save();
            }

            $kelompok->pembimbings()->sync([(int) $finalMentorId]);

            if ($siswaIds->isNotEmpty()) {
                $kelompok->siswa()->syncWithoutDetaching($siswaIds->all());
            }
        });

        return redirect()->route('kelompok-bimbingan.index')->with('success', 'Data pembimbing/siswa berhasil ditambahkan ke kelompok.');
    }

    public function removeAnggota(Request $request, int $id)
    {
        $kelompok = KelompokBimbingan::with(['siswa'])->findOrFail($id);

        $validated = $request->validate([
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'required|exists:siswa,id',
        ]);

        $siswaIds = collect($validated['siswa_ids'] ?? [])
            ->map(fn($value) => (int) $value)
            ->unique()
            ->values();

        $existingIds = $kelompok->siswa->pluck('id')
            ->map(fn($value) => (int) $value)
            ->all();

        $toDetach = $siswaIds
            ->filter(fn($idSiswa) => in_array((int) $idSiswa, $existingIds, true))
            ->values();

        if ($toDetach->isEmpty()) {
            return back()->with('error', 'Siswa yang dipilih tidak ditemukan pada kelompok ini.');
        }

        $kelompok->siswa()->detach($toDetach->all());

        return redirect()->route('kelompok-bimbingan.index')->with('success', 'Siswa berhasil dikeluarkan dari kelompok.');
    }

    public function exportExcel(Request $request)
    {
        $kelompok = $this->queryKelompokForCurrentUser($request)->get();
        $groups = $this->buildKelompokGroupedRows($kelompok);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Kelompok Bimbingan');

        $headers = [
            'No',
            'Nama Kelompok',
            'Metode',
            'Pembimbing',
            'No HP Pembimbing',
            'Jumlah Siswa',
            'Siswa per Kelas',
            'Daftar Anggota',
            'No HP Siswa',
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $col++;
        }

        $sheet->getStyle('A1:I1')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E5EDF7'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '6B7280'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(24);

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(14);
        $sheet->getColumnDimension('D')->setWidth(28);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(24);
        $sheet->getColumnDimension('H')->setWidth(34);
        $sheet->getColumnDimension('I')->setWidth(18);

        $rowNum = 2;
        foreach ($groups as $index => $group) {
            $startRow = $rowNum;
            $endRow = $startRow + $group['rowspan'] - 1;

            $sheet->setCellValue('A' . $startRow, $index + 1);
            $sheet->setCellValue('B' . $startRow, $group['nama_kelompok']);
            $sheet->setCellValue('C' . $startRow, $group['metode']);
            $sheet->setCellValue('D' . $startRow, $group['pembimbing']);
            $sheet->setCellValue('E' . $startRow, $group['no_hp_pembimbing']);
            $sheet->setCellValue('F' . $startRow, $group['jumlah_siswa']);

            if ($group['rowspan'] > 1) {
                foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $columnLetter) {
                    $sheet->mergeCells($columnLetter . $startRow . ':' . $columnLetter . $endRow);
                }
            }

            foreach ($group['kelas_rows'] as $kelasRowIndex => $kelasRow) {
                $currentRow = $startRow + $kelasRowIndex;
                $sheet->setCellValue('G' . $currentRow, $kelasRow['siswa_per_kelas']);
                $sheet->setCellValue('H' . $currentRow, implode("\n", $kelasRow['daftar_anggota']));
                $sheet->setCellValue('I' . $currentRow, implode("\n", $kelasRow['daftar_no_hp_siswa']));

                $anggotaCount = max(1, count($kelasRow['daftar_anggota']));
                $sheet->getRowDimension($currentRow)->setRowHeight(max(24, 18 * $anggotaCount));
            }

            $sheet->getStyle('A' . $startRow . ':I' . $endRow)->applyFromArray([
                'borders' => [
                    'outline' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => ['rgb' => '9CA3AF'],
                    ],
                ],
            ]);

            $rowNum = $endRow + 1;
        }

        $lastDataRow = max(1, $rowNum - 1);

        $sheet->getStyle('A1:I' . $lastDataRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '9CA3AF'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        $sheet->getStyle('A2:F' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G2:G' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('H2:I' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->freezePane('A2');

        $fileName = 'kelompok-bimbingan-' . now()->format('Ymd_His') . '.xlsx';
        $tempPath = tempnam(sys_get_temp_dir(), 'kelompok_bimbingan_');

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }

    public function exportPdf(Request $request)
    {
        $kelompok = $this->queryKelompokForCurrentUser($request)->get();
        $groups = $this->buildKelompokGroupedRows($kelompok);

        $pdf = Pdf::loadView('kelompok_bimbingan.export_pdf', [
            'groups' => $groups,
            'generatedAt' => now()->format('d-m-Y H:i'),
        ])->setPaper('a4', 'landscape');

        $fileName = 'kelompok-bimbingan-' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($fileName);
    }

    public function destroy($id)
    {
        $kelompok = KelompokBimbingan::findOrFail($id);
        $kelompok->delete();

        return redirect()->route('kelompok-bimbingan.index')->with('success', 'Kelompok berhasil dihapus.');
    }

    public function reset()
    {
        KelompokBimbingan::query()->delete();

        return redirect()->route('kelompok-bimbingan.index')->with('success', 'Semua kelompok berhasil direset.');
    }

    private function buildGroupChunks(array $siswaIds, int $minSize, int $maxSize): array
    {
        $total = count($siswaIds);

        if ($total === 0) {
            return ['chunks' => [], 'invalid' => false];
        }

        $minGroups = (int) ceil($total / $maxSize);
        $maxGroups = (int) floor($total / $minSize);

        if ($maxGroups >= 1 && $minGroups <= $maxGroups) {
            $groupCount = $minGroups;
            $baseSize = (int) floor($total / $groupCount);
            $remainder = $total % $groupCount;
            $offset = 0;
            $chunks = [];

            for ($i = 0; $i < $groupCount; $i++) {
                $currentSize = $baseSize + ($i < $remainder ? 1 : 0);
                $chunks[] = array_slice($siswaIds, $offset, $currentSize);
                $offset += $currentSize;
            }

            return ['chunks' => $chunks, 'invalid' => false];
        }

        $chunks = array_chunk($siswaIds, $maxSize);

        for ($i = count($chunks) - 1; $i > 0; $i--) {
            while (count($chunks[$i]) < $minSize && count($chunks[$i - 1]) > $minSize) {
                $chunks[$i][] = array_pop($chunks[$i - 1]);
            }
        }

        $invalid = false;
        foreach ($chunks as $chunk) {
            if (count($chunk) < $minSize || count($chunk) > $maxSize) {
                $invalid = true;
                break;
            }
        }

        return ['chunks' => $chunks, 'invalid' => $invalid];
    }

    private function calculatePembimbingQuota(int $jumlahJam): int
    {
        return (int) round((36 / 44) * $jumlahJam);
    }

    private function resolveMentorClassSupport(Pembimbing $mentor): array
    {
        $kelasIds = collect($mentor->kelas_ids ?? [])
            ->filter(fn($value) => $value !== null && $value !== '')
            ->map(fn($value) => (string) $value)
            ->values()
            ->all();

        if (in_array('all', $kelasIds, true)) {
            return ['all' => true, 'kelas_ids' => []];
        }

        $normalizedIds = collect($kelasIds)
            ->filter(fn($value) => ctype_digit((string) $value))
            ->map(fn($value) => (int) $value)
            ->unique()
            ->values()
            ->all();

        if (!empty($normalizedIds)) {
            return ['all' => false, 'kelas_ids' => $normalizedIds];
        }

        if ($mentor->jenis_guru === 'guru_produktif' && !empty($mentor->jurusan_id)) {
            $kelasByJurusan = Kelas::query()
                ->where('jurusan_id', $mentor->jurusan_id)
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->all();

            return ['all' => false, 'kelas_ids' => $kelasByJurusan];
        }

        return ['all' => true, 'kelas_ids' => []];
    }

    private function queryKelompokForCurrentUser(Request $request)
    {
        $authUser = auth()->user();
        $isPembimbing = $authUser && (Gate::forUser($authUser)->allows('pembimbing') || $authUser->role === 'pembimbing');
        $pembimbingAuthId = null;

        if ($isPembimbing) {
            $pembimbingAuthId = Pembimbing::query()
                ->where('nip_pembimbing', (string) $authUser->username)
                ->value('id');
        }

        $query = KelompokBimbingan::with(['pembimbing.jurusan', 'pembimbings.jurusan', 'siswa.kelas'])
            ->withCount('siswa')
            ->latest();

        if ($isPembimbing) {
            if (empty($pembimbingAuthId)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where(function ($builder) use ($pembimbingAuthId) {
                    $builder->where('pembimbing_id', $pembimbingAuthId)
                        ->orWhereHas('pembimbings', function ($mentorQuery) use ($pembimbingAuthId) {
                            $mentorQuery->where('pembimbings.id', $pembimbingAuthId);
                        });
                });
            }
        }

        $filters = [
            'kelompok_id' => $request->get('kelompok_id'),
            'pembimbing_id' => $request->get('pembimbing_id'),
            'keyword' => $request->get('keyword'),
        ];

        if (!empty($filters['kelompok_id'])) {
            $query->whereKey($filters['kelompok_id']);
        }

        if (!empty($filters['pembimbing_id'])) {
            $query->where(function ($builder) use ($filters) {
                $builder->where('pembimbing_id', $filters['pembimbing_id'])
                    ->orWhereHas('pembimbings', function ($mentorQuery) use ($filters) {
                        $mentorQuery->where('pembimbings.id', $filters['pembimbing_id']);
                    });
            });
        }

        if (!empty($filters['keyword'])) {
            $keyword = trim((string) $filters['keyword']);
            $query->where(function ($builder) use ($keyword) {
                $builder->where('nama_kelompok', 'like', '%' . $keyword . '%')
                    ->orWhereHas('pembimbing', function ($pembimbingQuery) use ($keyword) {
                        $pembimbingQuery->where('nama_pembimbing', 'like', '%' . $keyword . '%');
                    })
                    ->orWhereHas('siswa', function ($siswaQuery) use ($keyword) {
                        $siswaQuery->where('nama_siswa', 'like', '%' . $keyword . '%')
                            ->orWhereHas('kelas', function ($kelasQuery) use ($keyword) {
                                $kelasQuery->where('nama_kelas', 'like', '%' . $keyword . '%');
                            });
                    });
            });
        }

        return $query;
    }

    private function buildKelompokGroupedRows(Collection $kelompokCollection): array
    {
        return $kelompokCollection->values()->map(function ($item) {
            $mentorList = $item->pembimbings->isNotEmpty()
                ? $item->pembimbings
                : collect([$item->pembimbing])->filter();

            $kuotaPembimbing = (int) $mentorList->sum(function ($mentor) {
                $kuotaJumlahSiswa = (int) ($mentor->jumlah_siswa ?? 0);
                if ($kuotaJumlahSiswa > 0) {
                    return $kuotaJumlahSiswa;
                }

                return (int) round((36 / 44) * (int) ($mentor->jumlah_jam ?? 0));
            });

            $kelasRows = $item->siswa
                ->sortBy(fn($anggota) => ($anggota->kelas->nama_kelas ?? 'Tanpa Kelas') . '|' . $anggota->nama_siswa)
                ->groupBy(fn($anggota) => $anggota->kelas->nama_kelas ?? 'Tanpa Kelas')
                ->map(function ($anggotaByKelas, $kelasNama) {
                    return [
                        'siswa_per_kelas' => $kelasNama . ' - ' . $anggotaByKelas->count() . ' orang',
                        'daftar_anggota' => $anggotaByKelas
                            ->map(fn($anggota) => (string) $anggota->nama_siswa . ' - ' . ($anggota->kelas->nama_kelas ?? '-'))
                            ->values()
                            ->all(),
                        'daftar_no_hp_siswa' => $anggotaByKelas
                            ->map(fn($anggota) => (string) ($anggota->no_hp_siswa ?: '-'))
                            ->values()
                            ->all(),
                    ];
                })
                ->sortBy('siswa_per_kelas')
                ->values();

            if ($kelasRows->isEmpty()) {
                $kelasRows = collect([[
                    'siswa_per_kelas' => '-',
                    'daftar_anggota' => ['-'],
                    'daftar_no_hp_siswa' => ['-'],
                ]]);
            }

            return [
                'model' => $item,
                'nama_kelompok' => (string) $item->nama_kelompok,
                'metode' => ucfirst((string) $item->metode),
                'pembimbing' => $mentorList
                    ->map(fn($mentor) => (string) ($mentor->nama_pembimbing ?? '-'))
                    ->implode(', ') ?: '-',
                'no_hp_pembimbing' => $mentorList
                    ->map(fn($mentor) => (string) ($mentor->no_hp_pembimbing ?? '-'))
                    ->filter(fn($phone) => trim($phone) !== '')
                    ->implode(', ') ?: '-',
                'jumlah_siswa' => (int) $item->siswa_count,
                'kuota_pembimbing' => $kuotaPembimbing,
                'rowspan' => $kelasRows->count(),
                'kelas_rows' => $kelasRows->all(),
            ];
        })->all();
    }

    private function assignMentorsToGroup(array $candidateMentorIds, int $groupSize, array &$remainingCapacity): array
    {
        $available = collect($candidateMentorIds)
            ->unique()
            ->filter(fn($mentorId) => (int) ($remainingCapacity[$mentorId] ?? 0) > 0)
            ->sortByDesc(fn($mentorId) => (int) ($remainingCapacity[$mentorId] ?? 0))
            ->values()
            ->all();

        if (empty($available)) {
            return [];
        }

        $needed = $groupSize;
        $assignedMentors = [];
        $capacitySnapshot = $remainingCapacity;

        foreach ($available as $mentorId) {
            if ($needed <= 0) {
                break;
            }

            $remaining = (int) ($remainingCapacity[$mentorId] ?? 0);
            if ($remaining <= 0) {
                continue;
            }

            $take = min($remaining, $needed);
            if ($take <= 0) {
                continue;
            }

            $assignedMentors[] = (int) $mentorId;
            $remainingCapacity[$mentorId] = $remaining - $take;
            $needed -= $take;
        }

        if ($needed > 0) {
            $remainingCapacity = $capacitySnapshot;

            return [];
        }

        return $assignedMentors;
    }

    private function distributeQuotaAcrossClasses(int $capacity, array $support, array $availableClassIds): array
    {
        if ($capacity <= 0) {
            return [];
        }

        $availableClassIds = collect($availableClassIds)
            ->map(fn($id) => (int) $id)
            ->unique()
            ->sort()
            ->values();

        if ($availableClassIds->isEmpty()) {
            return [];
        }

        $targetClassIds = $availableClassIds;
        if (!(bool) ($support['all'] ?? false)) {
            $supported = collect($support['kelas_ids'] ?? [])
                ->map(fn($id) => (int) $id)
                ->unique();

            $targetClassIds = $availableClassIds
                ->filter(fn($classId) => $supported->contains($classId))
                ->values();
        }

        if ($targetClassIds->isEmpty()) {
            return [];
        }

        $classCount = $targetClassIds->count();
        $base = (int) floor($capacity / $classCount);
        $remainder = $capacity % $classCount;
        $distribution = [];

        foreach ($targetClassIds as $index => $classId) {
            $distribution[$classId] = $base + ($index < $remainder ? 1 : 0);
        }

        return $distribution;
    }

    private function assignMentorsToGroupByClass(
        array $candidateMentorIds,
        int $kelasId,
        int $groupSize,
        array &$remainingCapacity,
        array &$remainingClassCapacity
    ): array {
        $available = collect($candidateMentorIds)
            ->unique()
            ->filter(function ($mentorId) use ($kelasId, $remainingCapacity, $remainingClassCapacity) {
                $global = (int) ($remainingCapacity[$mentorId] ?? 0);
                $kelas = (int) ($remainingClassCapacity[$mentorId][$kelasId] ?? 0);

                return $global > 0 && $kelas > 0;
            })
            ->sortByDesc(function ($mentorId) use ($kelasId, $remainingClassCapacity, $remainingCapacity) {
                $kelas = (int) ($remainingClassCapacity[$mentorId][$kelasId] ?? 0);
                $global = (int) ($remainingCapacity[$mentorId] ?? 0);

                return ($kelas * 100000) + $global;
            })
            ->values()
            ->all();

        if (empty($available)) {
            return [];
        }

        $needed = $groupSize;
        $assignedMentors = [];
        $capacitySnapshot = $remainingCapacity;
        $classSnapshot = $remainingClassCapacity;

        foreach ($available as $mentorId) {
            if ($needed <= 0) {
                break;
            }

            $remainingGlobal = (int) ($remainingCapacity[$mentorId] ?? 0);
            $remainingKelas = (int) ($remainingClassCapacity[$mentorId][$kelasId] ?? 0);
            if ($remainingGlobal <= 0 || $remainingKelas <= 0) {
                continue;
            }

            $take = min($remainingGlobal, $remainingKelas, $needed);
            if ($take <= 0) {
                continue;
            }

            $assignedMentors[] = (int) $mentorId;
            $remainingCapacity[$mentorId] = $remainingGlobal - $take;
            $remainingClassCapacity[$mentorId][$kelasId] = $remainingKelas - $take;
            $needed -= $take;
        }

        if ($needed > 0) {
            $remainingCapacity = $capacitySnapshot;
            $remainingClassCapacity = $classSnapshot;

            return [];
        }

        return $assignedMentors;
    }
}
