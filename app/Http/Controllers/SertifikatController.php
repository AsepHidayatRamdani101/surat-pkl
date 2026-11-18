<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsSertifikatData;
use App\Models\FonnteSetting;
use App\Models\MessageLog;
use App\Models\Pembimbing;
use App\Models\Siswa;
use App\Models\TempatPkl;
use App\Models\SertifikatAspek;
use App\Models\SertifikatPenilaian;
use App\Services\PembagianWilayahService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\Facades\DataTables;

class SertifikatController extends Controller
{
    use BuildsSertifikatData;

    // ===== Siswa: form pemilik & pembimbing perusahaan/instansi =====

    public function siswaForm()
    {
        $siswa = $this->resolveSiswa();

        $tempatPkl = TempatPkl::with(['perusahaan', 'pembimbingPerusahaan'])
            ->where('siswa_id', $siswa->id)
            ->latest('id')
            ->first();

        if (!$tempatPkl) {
            return redirect()->route('dashboard')
                ->with('error', 'Data tempat PKL belum tersedia. Hubungi kepala program/panitia.');
        }

        // Rujukan dari sistem: pemilik dari menu Perusahaan, pembimbing dari menu Pembimbing Perusahaan.
        $pemilikSistem = $tempatPkl->perusahaan?->nama_pemilik_perusahaan;
        $pembimbingSistem = $tempatPkl->pembimbingPerusahaan?->nama_pembimbing
            ?: $tempatPkl->nama_pembimbing;
        $nipPembimbingSistem = $tempatPkl->pembimbingPerusahaan?->NIP
            ?: $tempatPkl->nip_pembimbing;

        $namaPemilik = $tempatPkl->nama_pemilik_instansi ?: $pemilikSistem;
        $namaPembimbing = $tempatPkl->nama_pembimbing_instansi ?: $pembimbingSistem;
        $nipPemilik = $tempatPkl->nip_pemilik_instansi;
        $nipPembimbing = $tempatPkl->nip_pembimbing_instansi ?: $nipPembimbingSistem;

        return view('sertifikat.form_siswa', compact(
            'siswa',
            'tempatPkl',
            'pemilikSistem',
            'pembimbingSistem',
            'nipPembimbingSistem',
            'namaPemilik',
            'namaPembimbing',
            'nipPemilik',
            'nipPembimbing'
        ));
    }

    public function siswaFormStore(Request $request)
    {
        $siswa = $this->resolveSiswa();

        $request->validate([
            'nama_pemilik_instansi' => 'required|string|max:255',
            'nip_pemilik_instansi' => 'nullable|string|max:50',
            'pemilik_sama_pembimbing' => 'nullable|boolean',
            'nama_pembimbing_instansi' => 'required_without:pemilik_sama_pembimbing|nullable|string|max:255',
            'nip_pembimbing_instansi' => 'nullable|string|max:50',
        ], [
            'nama_pemilik_instansi.required' => 'Nama pemilik perusahaan/instansi wajib diisi.',
            'nama_pembimbing_instansi.required_without' => 'Nama pembimbing perusahaan/instansi wajib diisi jika pemilik tidak sama dengan pembimbing.',
        ]);

        $tempatPkl = TempatPkl::where('siswa_id', $siswa->id)
            ->latest('id')
            ->first();

        if (!$tempatPkl) {
            return redirect()->route('dashboard')
                ->with('error', 'Data tempat PKL belum tersedia. Hubungi kepala program/panitia.');
        }

        $pemilikSama = $request->boolean('pemilik_sama_pembimbing');

        $tempatPkl->update([
            'nama_pemilik_instansi' => $request->nama_pemilik_instansi,
            'nip_pemilik_instansi' => $request->nip_pemilik_instansi ?: null,
            'pemilik_sama_pembimbing' => $pemilikSama,
            'nama_pembimbing_instansi' => $pemilikSama
                ? $request->nama_pemilik_instansi
                : $request->nama_pembimbing_instansi,
            'nip_pembimbing_instansi' => $pemilikSama
                ? ($request->nip_pemilik_instansi ?: null)
                : ($request->nip_pembimbing_instansi ?: null),
        ]);

        return redirect()->route('sertifikat.pelaksanaan.form')
            ->with('success', 'Data pemilik & pembimbing perusahaan/instansi berhasil disimpan.');
    }

    // ===== Siswa: lihat & download sertifikat pelaksanaan =====

    public function siswaCetakPelaksanaan()
    {
        $siswa = $this->resolveSiswa();
        $data = $this->buildSertifikatPelaksanaanDataForSiswa($siswa);

        if (!$data) {
            return redirect()->route('dashboard')
                ->with('error', 'Data tempat PKL belum tersedia, sertifikat pelaksanaan belum dapat dicetak.');
        }

        $data['downloadUrl'] = route('sertifikat.pelaksanaan.download');
        $data['backUrl'] = route('sertifikat.pelaksanaan.form');

        return view('sertifikat.pelaksanaan_cetak', $data);
    }

    public function siswaDownloadPelaksanaan()
    {
        $siswa = $this->resolveSiswa();
        $data = $this->buildSertifikatPelaksanaanDataForSiswa($siswa, true);

        if (!$data) {
            return redirect()->route('dashboard')
                ->with('error', 'Data tempat PKL belum tersedia, sertifikat pelaksanaan belum dapat dicetak.');
        }

        $pdf = Pdf::loadView('sertifikat.pelaksanaan_pdf', $data)->setPaper('A4', 'landscape');

        return $pdf->download('sertifikat-pelaksanaan-pkl-' . $siswa->nis . '.pdf');
    }

    // ===== Admin: menu cetak sertifikat =====

    public function adminIndex(Request $request, PembagianWilayahService $pembagianWilayah)
    {
        $search = trim((string) $request->query('q', ''));
        $wilayah = trim((string) $request->query('wilayah', ''));
        $wilayahRows = $pembagianWilayah->rekap();
        $wilayahTerpilih = $wilayahRows->firstWhere('key', $wilayah);
        $perusahaanIds = $wilayahTerpilih['perusahaan_ids'] ?? [];

        $siswaList = Siswa::with(['kelas.jurusan', 'suratIzin.perusahaan'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_siswa', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%");
                });
            })
            ->when($wilayah !== '', function ($query) use ($perusahaanIds) {
                $query->whereHas('suratIzin', fn ($q) => $q->whereIn('perusahaan_id', $perusahaanIds));
            })
            ->orderBy('nama_siswa')
            ->paginate(25)
            ->withQueryString();

        return view('sertifikat.admin_index', compact('siswaList', 'search', 'wilayah', 'wilayahRows'));
    }

    public function editPenilaian(Siswa $siswa)
    {
        $siswa->load('kelas.jurusan');
        $jurusan = $siswa->kelas?->jurusan;
        abort_unless($jurusan, 422, 'Jurusan siswa belum tersedia.');
        $aspeks = SertifikatAspek::where('jurusan_id', $jurusan->id)->orderBy('urutan')->orderBy('id')->get();
        $penilaian = SertifikatPenilaian::where('siswa_id', $siswa->id)->get()->keyBy('sertifikat_aspek_id');
        return view('sertifikat.penilaian', compact('siswa', 'jurusan', 'aspeks', 'penilaian'));
    }

    public function updatePenilaian(Request $request, Siswa $siswa)
    {
        $siswa->load('kelas.jurusan');
        $aspekIds = SertifikatAspek::where('jurusan_id', $siswa->kelas?->jurusan_id)->pluck('id');
        $data = $request->validate([
            'nilai' => ['nullable', 'array'],
            'nilai.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);
        foreach ($aspekIds as $aspekId) {
            $nilai = $data['nilai'][$aspekId] ?? null;
            SertifikatPenilaian::updateOrCreate(
                ['siswa_id' => $siswa->id, 'sertifikat_aspek_id' => $aspekId],
                ['nilai' => $nilai, 'predikat' => $this->predikatSertifikat($nilai)]
            );
        }
        return back()->with('success', 'Penilaian sertifikat berhasil disimpan.');
    }

    private function predikatSertifikat($nilai): ?string
    {
        if ($nilai === null || $nilai === '') return null;
        return match (true) {
            $nilai >= 90 => 'Sangat Baik',
            $nilai >= 80 => 'Baik',
            $nilai >= 70 => 'Cukup',
            default => 'Perlu Bimbingan',
        };
    }

    public function adminCetakPembekalan(Siswa $siswa)
    {
        $data = $this->buildSertifikatPembekalanDataForSiswa($siswa);
        $data['downloadUrl'] = route('sertifikat.admin.pembekalan.download', $siswa->id);
        $data['backUrl'] = route('sertifikat.admin.index');

        return view('siswa.sertifikat_pembekalan', $data);
    }

    public function adminDownloadPembekalan(Siswa $siswa)
    {
        $data = $this->buildSertifikatPembekalanDataForSiswa($siswa, true);

        $pdf = Pdf::loadView('siswa.sertifikat_pembekalan_pdf', $data)->setPaper('A4', 'landscape');

        return $pdf->download('sertifikat-pembekalan-' . $siswa->nis . '.pdf');
    }

    public function adminCetakPelaksanaan(Siswa $siswa)
    {
        $data = $this->buildSertifikatPelaksanaanDataForSiswa($siswa);

        if (!$data) {
            return redirect()->route('sertifikat.admin.index')
                ->with('error', 'Siswa belum memiliki data tempat PKL, sertifikat pelaksanaan belum dapat dicetak.');
        }

        $data['downloadUrl'] = route('sertifikat.admin.pelaksanaan.download', $siswa->id);
        $data['backUrl'] = route('sertifikat.admin.index');

        return view('sertifikat.pelaksanaan_cetak', $data);
    }

    public function adminDownloadPelaksanaan(Siswa $siswa)
    {
        $data = $this->buildSertifikatPelaksanaanDataForSiswa($siswa, true);

        if (!$data) {
            return redirect()->route('sertifikat.admin.index')
                ->with('error', 'Siswa belum memiliki data tempat PKL, sertifikat pelaksanaan belum dapat dicetak.');
        }

        $pdf = Pdf::loadView('sertifikat.pelaksanaan_pdf', $data)->setPaper('A4', 'landscape');

        return $pdf->download('sertifikat-pelaksanaan-pkl-' . $siswa->nis . '.pdf');
    }

    // ===== Admin & pembimbing sekolah (monitoring): data sertifikat =====

    public function dataIndex(Request $request)
    {
        $pembimbingId = $this->resolvePembimbingMonitoringId();

        $baseQuery = TempatPkl::query()
            ->when($pembimbingId, fn ($q) => $q->where('pembimbing_id', $pembimbingId))
            ->whereHas('siswa')
            ->whereIn('tempat_pkl.id', function ($query) use ($pembimbingId) {
                $query->selectRaw('MAX(tempat_pkl.id)')
                    ->from('tempat_pkl')
                    ->whereNotNull('siswa_id')
                    ->when($pembimbingId, fn ($q) => $q->where('pembimbing_id', $pembimbingId))
                    ->groupBy('siswa_id');
            });

        // DataTables meminta JSON pada request AJAX; browser hanya menerima data
        // sesuai halaman yang sedang dibuka.
        if ($request->ajax()) {
            $query = (clone $baseQuery)->with(['siswa.kelas', 'perusahaan']);
            $statusFilter = (string) $request->input('status', '');

            if ($statusFilter === 'sudah') {
                $query->whereNotNull('nama_pemilik_instansi')->where('nama_pemilik_instansi', '<>', '')
                    ->whereNotNull('nama_pembimbing_instansi')->where('nama_pembimbing_instansi', '<>', '');
            } elseif ($statusFilter === 'belum') {
                $query->where(function ($q) {
                    $q->whereNull('nama_pemilik_instansi')->orWhere('nama_pemilik_instansi', '')
                        ->orWhereNull('nama_pembimbing_instansi')->orWhere('nama_pembimbing_instansi', '');
                });
            }

            $normalizePhone = static function ($phone) {
                $number = preg_replace('/\D+/', '', (string) $phone);
                if (str_starts_with($number, '0062')) {
                    $number = substr($number, 2);
                }
                if (str_starts_with($number, '62')) {
                    return '62' . ltrim(substr($number, 2), '0');
                }
                if (str_starts_with($number, '0')) {
                    return '62' . substr($number, 1);
                }
                return str_starts_with($number, '8') ? '62' . $number : $number;
            };

            $isComplete = static fn ($row) => filled($row->nama_pemilik_instansi)
                && filled($row->nama_pembimbing_instansi);

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->filterColumn('siswa.nama_siswa', function ($query, $keyword) {
                    $query->whereHas('siswa', fn ($q) => $q->where('nama_siswa', 'like', "%{$keyword}%"));
                })
                ->filterColumn('siswa.nis', function ($query, $keyword) {
                    $query->whereHas('siswa', fn ($q) => $q->where('nis', 'like', "%{$keyword}%"));
                })
                ->filterColumn('perusahaan.nama_perusahaan', function ($query, $keyword) {
                    $query->whereHas('perusahaan', fn ($q) => $q->where('nama_perusahaan', 'like', "%{$keyword}%"));
                })
                ->addColumn('siswa_nis', fn ($row) => $row->siswa?->nis ?? '-')
                ->addColumn('siswa_nama', fn ($row) => $row->siswa?->nama_siswa ?? '-')
                ->addColumn('siswa_telepon', fn ($row) => $row->siswa?->no_hp_siswa ?? '-')
                ->addColumn('kelas', fn ($row) => $row->siswa?->kelas?->nama_kelas ?? '-')
                ->addColumn('perusahaan_nama', fn ($row) => $row->perusahaan?->nama_perusahaan ?? '-')
                ->addColumn('pemilik', function ($row) {
                    if (blank($row->nama_pemilik_instansi)) {
                        return '<span class="badge badge-danger">Belum diisi</span>';
                    }
                    return e($row->nama_pemilik_instansi)
                        . ($row->nip_pemilik_instansi ? '<br><small class="text-muted">NIP: ' . e($row->nip_pemilik_instansi) . '</small>' : '');
                })
                ->addColumn('pembimbing_perusahaan', function ($row) {
                    if (blank($row->nama_pembimbing_instansi)) {
                        return '<span class="badge badge-danger">Belum diisi</span>';
                    }
                    return e($row->nama_pembimbing_instansi)
                        . ($row->nip_pembimbing_instansi ? '<br><small class="text-muted">NIP: ' . e($row->nip_pembimbing_instansi) . '</small>' : '');
                })
                ->addColumn('status_html', fn ($row) => $isComplete($row)
                    ? '<span class="badge badge-success">Sudah Mengisi</span>'
                    : '<span class="badge badge-warning">Belum Lengkap</span>')
                ->addColumn('aksi', function ($row) use ($normalizePhone, $isComplete) {
                    $phone = $normalizePhone($row->siswa?->no_hp_siswa);
                    if ($phone === '') {
                        return '<span class="badge badge-secondary"><i class="fas fa-phone-slash mr-1"></i> No HP kosong</span>';
                    }
                    $company = $row->perusahaan?->nama_perusahaan ?? 'tempat PKL';
                    $message = $isComplete($row)
                        ? "Halo {$row->siswa->nama_siswa}, data pemilik/pimpinan dan pembimbing {$company} untuk Sertifikat PKL sudah terisi. Silakan periksa kembali melalui menu PKL > Data Pelaksanaan. Terima kasih."
                        : "Halo {$row->siswa->nama_siswa}, mohon segera lengkapi data pemilik/pimpinan dan pembimbing {$company} untuk keperluan Sertifikat PKL melalui menu PKL > Data Pelaksanaan. Terima kasih.";
                    $url = 'https://api.whatsapp.com/send?phone=' . $phone . '&text=' . rawurlencode($message);
                    return '<a href="' . e($url) . '" target="_blank" rel="noopener" class="btn btn-xs btn-success" title="Buka WhatsApp (' . e($phone) . ')"><i class="fab fa-whatsapp mr-1"></i> WA Web</a>';
                })
                ->rawColumns(['pemilik', 'pembimbing_perusahaan', 'status_html', 'aksi'])
                ->toJson();
        }

        $all = (clone $baseQuery)->get(['id', 'perusahaan_id', 'nama_pemilik_instansi', 'nama_pembimbing_instansi']);

        $isTerisi = fn ($t) => filled($t->nama_pemilik_instansi) && filled($t->nama_pembimbing_instansi);

        $siswaSudah = $all->filter($isTerisi)->count();
        $siswaBelum = $all->count() - $siswaSudah;

        $byPerusahaan = $all->groupBy('perusahaan_id');
        $perusahaanTerisi = $byPerusahaan->filter(fn ($rows) => $rows->every($isTerisi))->count();
        $perusahaanBelum = $byPerusahaan->count() - $perusahaanTerisi;

        $statusFilter = (string) $request->query('status', '');

        return view('sertifikat.data_index', compact(
            'statusFilter',
            'siswaSudah',
            'siswaBelum',
            'perusahaanTerisi',
            'perusahaanBelum'
        ));
    }

    public function kirimWaPengisian(Siswa $siswa)
    {
        $pembimbingId = $this->resolvePembimbingMonitoringId();

        $tempatPkl = TempatPkl::with('perusahaan')
            ->where('siswa_id', $siswa->id)
            ->when($pembimbingId, fn ($q) => $q->where('pembimbing_id', $pembimbingId))
            ->latest('id')
            ->first();

        if (!$tempatPkl) {
            return back()->with('error', 'Data tempat PKL siswa tidak ditemukan.');
        }

        if (filled($tempatPkl->nama_pemilik_instansi) && filled($tempatPkl->nama_pembimbing_instansi)) {
            return back()->with('error', 'Siswa ' . $siswa->nama_siswa . ' sudah mengisi data pemilik & pembimbing perusahaan.');
        }

        if (!$siswa->no_hp_siswa) {
            return back()->with('error', 'Nomor HP siswa ' . $siswa->nama_siswa . ' tidak tersedia.');
        }

        $kosong = [];
        if (blank($tempatPkl->nama_pemilik_instansi)) {
            $kosong[] = 'Pemilik/Pimpinan Perusahaan';
        }
        if (blank($tempatPkl->nama_pembimbing_instansi)) {
            $kosong[] = 'Pembimbing Perusahaan';
        }

        $namaPerusahaan = $tempatPkl->perusahaan->nama_perusahaan ?? 'tempat PKL kamu';

        $pesan = "Halo *{$siswa->nama_siswa}*,\n\n"
            . "Mohon segera lengkapi data *" . implode('* dan *', $kosong) . "* "
            . "di {$namaPerusahaan} untuk keperluan Sertifikat Pelaksanaan PKL.\n\n"
            . "Silakan isi melalui menu *PKL > Data Pelaksanaan* di aplikasi:\n"
            . route('sertifikat.pelaksanaan.form') . "\n\n"
            . "Terima kasih.";

        $response = $this->sendViaFonnte($siswa->no_hp_siswa, $pesan);
        $status = $response['success'] ? 'terkirim' : 'gagal';

        MessageLog::create([
            'template_id' => null,
            'nomor_penerima' => $siswa->no_hp_siswa,
            'isi_pesan' => $pesan,
            'tipe_pengiriman' => 'personal',
            'status_pengiriman' => $status,
            'response_fonnte' => json_encode($response),
            'dikirim_oleh' => auth()->id(),
        ]);

        if ($response['success']) {
            return back()->with('success', 'Pesan WA berhasil dikirim ke ' . $siswa->nama_siswa . ' (' . $siswa->no_hp_siswa . ').');
        }

        return back()->with('error', 'Gagal mengirim WA ke ' . $siswa->nama_siswa . '. ' . ($response['error'] ?? ''));
    }

    public function kirimWaPengisianMassal()
    {
        $pembimbingId = $this->resolvePembimbingMonitoringId();

        $belumLengkap = TempatPkl::with(['siswa', 'perusahaan'])
            ->when($pembimbingId, fn ($q) => $q->where('pembimbing_id', $pembimbingId))
            ->whereHas('siswa')
            ->orderByDesc('id')
            ->get()
            ->unique('siswa_id')
            ->reject(fn ($t) => filled($t->nama_pemilik_instansi) && filled($t->nama_pembimbing_instansi))
            ->values();

        if ($belumLengkap->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa yang belum mengisi data pemilik & pembimbing perusahaan.');
        }

        $successCount = 0;
        $failCount = 0;
        $tanpaHp = 0;

        foreach ($belumLengkap as $tempatPkl) {
            $siswa = $tempatPkl->siswa;

            if (!$siswa->no_hp_siswa) {
                $tanpaHp++;
                continue;
            }

            $kosong = [];
            if (blank($tempatPkl->nama_pemilik_instansi)) {
                $kosong[] = 'Pemilik/Pimpinan Perusahaan';
            }
            if (blank($tempatPkl->nama_pembimbing_instansi)) {
                $kosong[] = 'Pembimbing Perusahaan';
            }

            $namaPerusahaan = $tempatPkl->perusahaan->nama_perusahaan ?? 'tempat PKL kamu';

            $pesan = "Halo *{$siswa->nama_siswa}*,\n\n"
                . "Mohon segera lengkapi data *" . implode('* dan *', $kosong) . "* "
                . "di {$namaPerusahaan} untuk keperluan Sertifikat Pelaksanaan PKL.\n\n"
                . "Silakan isi melalui menu *PKL > Data Pelaksanaan* di aplikasi:\n"
                . route('sertifikat.pelaksanaan.form') . "\n\n"
                . "Terima kasih.";

            $response = $this->sendViaFonnte($siswa->no_hp_siswa, $pesan);
            $status = $response['success'] ? 'terkirim' : 'gagal';

            MessageLog::create([
                'template_id' => null,
                'nomor_penerima' => $siswa->no_hp_siswa,
                'isi_pesan' => $pesan,
                'tipe_pengiriman' => 'masal',
                'status_pengiriman' => $status,
                'response_fonnte' => json_encode($response),
                'dikirim_oleh' => auth()->id(),
            ]);

            if ($response['success']) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        $message = "Pengiriman WA massal selesai. Berhasil: {$successCount}, Gagal: {$failCount}";
        if ($tanpaHp > 0) {
            $message .= ", Tanpa No HP: {$tanpaHp}";
        }

        return back()->with($successCount > 0 ? 'success' : 'error', $message);
    }

    private function resolvePembimbingMonitoringId(): ?int
    {
        $user = auth()->user();

        if (!$user || !in_array($user->role, ['panitia', 'pembimbing'], true)) {
            abort(403);
        }

        if ($user->role !== 'pembimbing') {
            return null;
        }

        $id = Pembimbing::where('nip_pembimbing', (string) $user->username)->value('id');
        abort_if(!$id, 403, 'Data pembimbing untuk akun ini tidak ditemukan.');

        return (int) $id;
    }

    private function sendViaFonnte($nomorTujuan, $pesan): array
    {
        try {
            $nomorTujuan = preg_replace('/^0/', '62', $nomorTujuan);

            $response = Http::withHeaders([
                'Authorization' => FonnteSetting::activeToken(),
            ])->asForm()->post('https://api.fonnte.com/send', [
                'target' => $nomorTujuan,
                'message' => $pesan,
            ]);

            $data = $response->json();

            return [
                'success' => $data['status'] ?? false,
                'message_id' => $data['data']['id_message'] ?? null,
                'error' => $data['reason'] ?? null,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function resolveSiswa(): Siswa
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'siswa') {
            abort(403);
        }

        $siswa = Siswa::with('kelas.jurusan')
            ->where('nis', (string) $user->username)
            ->first();

        if (!$siswa) {
            abort(404, 'Data siswa tidak ditemukan.');
        }

        return $siswa;
    }
}
