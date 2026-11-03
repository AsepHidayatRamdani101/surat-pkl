<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsSertifikatData;
use App\Models\Siswa;
use App\Models\TempatPkl;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

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
