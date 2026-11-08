<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsSertifikatData;
use App\Models\MessageLog;
use App\Models\Pembimbing;
use App\Models\Siswa;
use App\Models\TempatPkl;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;

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
        $data = $this->buildSertifikatPelaksanaanDataForSiswa($siswa);

        if (!$data) {
            return redirect()->route('dashboard')
                ->with('error', 'Data tempat PKL belum tersedia, sertifikat pelaksanaan belum dapat dicetak.');
        }

        $pdf = Pdf::loadView('sertifikat.pelaksanaan_pdf', $data)->setPaper('A4', 'landscape');

        return $pdf->download('sertifikat-pelaksanaan-pkl-' . $siswa->nis . '.pdf');
    }

    // ===== Admin: menu cetak sertifikat =====

    public function adminIndex(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $siswaList = Siswa::with(['kelas.jurusan', 'suratIzin.perusahaan'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_siswa', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%");
                });
            })
            ->orderBy('nama_siswa')
            ->paginate(25)
            ->withQueryString();

        return view('sertifikat.admin_index', compact('siswaList', 'search'));
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
        $data = $this->buildSertifikatPembekalanDataForSiswa($siswa);

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
        $data = $this->buildSertifikatPelaksanaanDataForSiswa($siswa);

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

        $search = trim((string) $request->query('q', ''));
        $statusFilter = $request->query('status', '');

        $all = TempatPkl::with(['siswa.kelas', 'perusahaan', 'pembimbing', 'pembimbingPerusahaan'])
            ->when($pembimbingId, fn ($q) => $q->where('pembimbing_id', $pembimbingId))
            ->whereHas('siswa')
            ->orderByDesc('id')
            ->get()
            ->unique('siswa_id')
            ->values();

        $isTerisi = fn ($t) => filled($t->nama_pemilik_instansi) && filled($t->nama_pembimbing_instansi);

        $siswaSudah = $all->filter($isTerisi)->count();
        $siswaBelum = $all->count() - $siswaSudah;

        $byPerusahaan = $all->groupBy('perusahaan_id');
        $perusahaanTerisi = $byPerusahaan->filter(fn ($rows) => $rows->every($isTerisi))->count();
        $perusahaanBelum = $byPerusahaan->count() - $perusahaanTerisi;

        $rows = $all;

        if ($search !== '') {
            $needle = mb_strtolower($search);
            $rows = $rows->filter(function ($t) use ($needle) {
                return str_contains(mb_strtolower($t->siswa->nama_siswa ?? ''), $needle)
                    || str_contains(mb_strtolower($t->siswa->nis ?? ''), $needle)
                    || str_contains(mb_strtolower($t->perusahaan->nama_perusahaan ?? ''), $needle);
            });
        }

        if ($statusFilter === 'sudah') {
            $rows = $rows->filter($isTerisi);
        } elseif ($statusFilter === 'belum') {
            $rows = $rows->reject($isTerisi);
        }

        $rows = $rows->sortBy(fn ($t) => mb_strtolower($t->siswa->nama_siswa ?? ''))->values();

        $perPage = 25;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $tempatList = new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('sertifikat.data_index', compact(
            'tempatList',
            'search',
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
                'Authorization' => env('FONNTE_TOKEN'),
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
