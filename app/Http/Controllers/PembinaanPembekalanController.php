<?php

namespace App\Http\Controllers;

use App\Models\KelompokBimbingan;
use App\Models\Pembimbing;
use App\Models\PembinaanPembekalan;
use App\Models\Siswa;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PembinaanPembekalanController extends Controller
{
    private const JENIS_PEMBINAAN = [
        'terlambat_hadir' => 'Terlambat hadir',
        'tanpa_registrasi' => 'Tidak melakukan registrasi',
        'tidak_mengikuti_apel' => 'Tidak mengikuti apel',
        'seragam_tidak_sesuai' => 'Seragam tidak sesuai ketentuan',
        'tanpa_perlengkapan' => 'Tidak membawa perlengkapan pembelajaran',
        'tanpa_pakaian_olahraga' => 'Tidak membawa pakaian olahraga (kegiatan lapangan)',
        'hp_tanpa_izin' => 'Menggunakan telepon genggam tanpa izin',
        'mengobrol_saat_materi' => 'Mengobrol saat materi berlangsung',
        'tidur_saat_kegiatan' => 'Tidur saat kegiatan',
        'keluar_tanpa_izin' => 'Keluar ruangan tanpa izin',
        'tidak_mengikuti_instruksi' => 'Tidak mengikuti instruksi pembimbing',
        'mengganggu_ketertiban' => 'Mengganggu ketertiban kegiatan',
        'tidak_menjaga_kebersihan' => 'Tidak menjaga kebersihan',
        'perilaku_tidak_sopan' => 'Perilaku tidak sopan',
        'lainnya' => 'Lainnya',
    ];

    private const TINDAKAN_PEMBINAAN = [
        'teguran_lisan' => 'Teguran lisan',
        'teguran_tertulis' => 'Teguran tertulis',
        'pendampingan_guru' => 'Pendampingan oleh Guru Pembimbing',
        'rangkuman_materi' => 'Penugasan membuat rangkuman materi',
        'refleksi_budaya_kerja' => 'Penugasan membuat refleksi tentang budaya kerja',
        'bantu_panitia' => 'Penugasan membantu panitia setelah kegiatan',
        'pembekalan_susulan' => 'Mengikuti pembekalan susulan',
        'pemanggilan_ortu' => 'Pemanggilan orang tua/wali (apabila diperlukan)',
        'lainnya' => 'Lainnya',
    ];

    private const HASIL_PEMBINAAN = [
        'memahami_kesalahan' => 'Peserta memahami kesalahan yang dilakukan.',
        'bersedia_memperbaiki' => 'Peserta bersedia memperbaiki perilaku.',
        'perlu_lanjutan' => 'Perlu dilakukan pembinaan lanjutan.',
        'perlu_koordinasi_ortu' => 'Perlu koordinasi dengan orang tua/wali.',
    ];

    private const TINGKAT_PEMBINAAN = [
        'tahap_1' => [
            'label' => 'Tahap I',
            'kriteria' => 'Pelanggaran ringan pertama',
            'tindak_lanjut' => 'Teguran lisan dan pembinaan langsung',
        ],
        'tahap_2' => [
            'label' => 'Tahap II',
            'kriteria' => 'Pelanggaran ringan berulang',
            'tindak_lanjut' => 'Formulir Pembinaan (FRM-PKL-02) dan pendampingan',
        ],
        'tahap_3' => [
            'label' => 'Tahap III',
            'kriteria' => 'Pelanggaran sedang atau berulang',
            'tindak_lanjut' => 'Pembinaan oleh Koordinator Pembekalan dan guru pembimbing',
        ],
        'tahap_4' => [
            'label' => 'Tahap IV',
            'kriteria' => 'Pelanggaran berat atau berulang kali',
            'tindak_lanjut' => 'Koordinasi dengan orang tua/wali dan kepala program keahlian',
        ],
    ];

    public function index(Request $request)
    {
        $pembimbingAuthId = $this->getAuthorizedPembimbingId();

        $filters = [
            'tanggal_awal' => $request->get('tanggal_awal'),
            'tanggal_akhir' => $request->get('tanggal_akhir'),
            'pembimbing_id' => $request->get('pembimbing_id'),
            'siswa_id' => $request->get('siswa_id'),
            'keyword' => $request->get('keyword'),
        ];

        if (!empty($pembimbingAuthId)) {
            $filters['pembimbing_id'] = (string) $pembimbingAuthId;
        }

        $records = $this->buildFilteredQuery($filters)->get();

        $pembimbingOptionsQuery = Pembimbing::query()->orderBy('nama_pembimbing');
        if (!empty($pembimbingAuthId)) {
            $pembimbingOptionsQuery->whereKey($pembimbingAuthId);
        }
        $pembimbingOptions = $pembimbingOptionsQuery->get(['id', 'nama_pembimbing', 'nip_pembimbing']);

        $siswaOptionsQuery = Siswa::with(['kelas', 'kelompokBimbingan.pembimbing'])
            ->whereHas('kelompokBimbingan', function ($q) {
                $q->whereNotNull('kelompok_bimbingan.pembimbing_id');
            })
            ->orderBy('nama_siswa');

        if (!empty($pembimbingAuthId)) {
            $siswaOptionsQuery->whereHas('kelompokBimbingan', function ($q) use ($pembimbingAuthId) {
                $q->where('kelompok_bimbingan.pembimbing_id', $pembimbingAuthId);
            });
        }

        $siswaOptions = $siswaOptionsQuery->get(['id', 'nama_siswa', 'nis', 'kelas_id']);

        return view('pembekalan.pembinaan', [
            'records' => $records,
            'filters' => $filters,
            'pembimbingOptions' => $pembimbingOptions,
            'siswaOptions' => $siswaOptions,
            'jenisPembinaanOptions' => self::JENIS_PEMBINAAN,
            'tindakanPembinaanOptions' => self::TINDAKAN_PEMBINAAN,
            'hasilPembinaanOptions' => self::HASIL_PEMBINAAN,
            'tingkatPembinaanOptions' => self::TINGKAT_PEMBINAAN,
            'isPembimbingOnly' => !empty($pembimbingAuthId),
        ]);
    }

    public function exportExcel(Request $request)
    {
        $pembimbingAuthId = $this->getAuthorizedPembimbingId();

        $filters = [
            'tanggal_awal' => $request->get('tanggal_awal'),
            'tanggal_akhir' => $request->get('tanggal_akhir'),
            'pembimbing_id' => $request->get('pembimbing_id'),
            'siswa_id' => $request->get('siswa_id'),
            'keyword' => $request->get('keyword'),
        ];

        if (!empty($pembimbingAuthId)) {
            $filters['pembimbing_id'] = (string) $pembimbingAuthId;
        }

        $records = $this->buildFilteredQuery($filters)->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Pembinaan');

        $sheet->setCellValue('A1', 'Rekap Pembinaan Peserta Pembekalan PKL');
        $sheet->setCellValue('A2', 'Total Data');
        $sheet->setCellValue('B2', $records->count());

        $headers = ['No', 'Tanggal', 'Peserta', 'NIS', 'Kelas', 'Jurusan', 'Pembimbing', 'Tempat', 'Tingkat', 'Kronologi'];
        $headerRow = 4;

        foreach ($headers as $index => $header) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column . $headerRow, $header);
        }

        $row = $headerRow + 1;
        foreach ($records as $index => $item) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, optional($item->tanggal_formulir)->format('d-m-Y') ?? '-');
            $sheet->setCellValue('C' . $row, $item->siswa->nama_siswa ?? '-');
            $sheet->setCellValue('D' . $row, $item->siswa->nis ?? '-');
            $sheet->setCellValue('E' . $row, optional($item->siswa->kelas)->nama_kelas ?? '-');
            $sheet->setCellValue('F' . $row, optional(optional($item->siswa->kelas)->jurusan)->nama_jurusan ?? '-');
            $sheet->setCellValue('G' . $row, $item->pembimbing->nama_pembimbing ?? '-');
            $sheet->setCellValue('H' . $row, $item->tempat ?? '-');
            $sheet->setCellValue('I' . $row, self::TINGKAT_PEMBINAAN[$item->tingkat_pembinaan]['label'] ?? '-');
            $sheet->setCellValue('J' . $row, $item->kronologi ?? '-');
            $row++;
        }

        foreach (range('A', 'J') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'rekap-pembinaan-pembekalan-' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function exportPdf(Request $request)
    {
        $pembimbingAuthId = $this->getAuthorizedPembimbingId();

        $filters = [
            'tanggal_awal' => $request->get('tanggal_awal'),
            'tanggal_akhir' => $request->get('tanggal_akhir'),
            'pembimbing_id' => $request->get('pembimbing_id'),
            'siswa_id' => $request->get('siswa_id'),
            'keyword' => $request->get('keyword'),
        ];

        if (!empty($pembimbingAuthId)) {
            $filters['pembimbing_id'] = (string) $pembimbingAuthId;
        }

        $records = $this->buildFilteredQuery($filters)->get();

        $summary = [
            'total' => $records->count(),
            'tahap_1' => $records->where('tingkat_pembinaan', 'tahap_1')->count(),
            'tahap_2' => $records->where('tingkat_pembinaan', 'tahap_2')->count(),
            'tahap_3' => $records->where('tingkat_pembinaan', 'tahap_3')->count(),
            'tahap_4' => $records->where('tingkat_pembinaan', 'tahap_4')->count(),
        ];

        $pdf = Pdf::loadView('pembekalan.pembinaan_rekap_pdf', [
            'records' => $records,
            'filters' => $filters,
            'summary' => $summary,
            'tingkatPembinaanOptions' => self::TINGKAT_PEMBINAAN,
        ])->setPaper('a4', 'landscape');

        $filename = 'rekap-pembinaan-pembekalan-' . now()->format('Ymd_His') . '.pdf';

        if ($request->boolean('stream')) {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }

    public function store(Request $request)
    {
        $pembimbingAuthId = $this->getAuthorizedPembimbingId();
        $data = $this->validatePayload($request, $pembimbingAuthId);

        PembinaanPembekalan::create($data);

        return redirect()->route('pembekalan.pembinaan')->with('success', 'Data pembinaan berhasil ditambahkan.');
    }

    public function update(Request $request, PembinaanPembekalan $pembinaanPembekalan)
    {
        $pembimbingAuthId = $this->getAuthorizedPembimbingId();

        if (!empty($pembimbingAuthId) && (int) $pembinaanPembekalan->pembimbing_id !== (int) $pembimbingAuthId) {
            throw new UnauthorizedException('Anda tidak berwenang memperbarui data ini.');
        }

        $data = $this->validatePayload($request, $pembimbingAuthId);

        $pembinaanPembekalan->update($data);

        return redirect()->route('pembekalan.pembinaan')->with('success', 'Data pembinaan berhasil diperbarui.');
    }

    public function destroy(PembinaanPembekalan $pembinaanPembekalan)
    {
        $pembimbingAuthId = $this->getAuthorizedPembimbingId();

        if (!empty($pembimbingAuthId) && (int) $pembinaanPembekalan->pembimbing_id !== (int) $pembimbingAuthId) {
            throw new UnauthorizedException('Anda tidak berwenang menghapus data ini.');
        }

        $pembinaanPembekalan->delete();

        return redirect()->route('pembekalan.pembinaan')->with('success', 'Data pembinaan berhasil dihapus.');
    }

    public function print(PembinaanPembekalan $pembinaanPembekalan)
    {
        $data = $this->resolvePrintData($pembinaanPembekalan);

        return view('pembekalan.pembinaan_print', $data);
    }

    public function pdf(PembinaanPembekalan $pembinaanPembekalan)
    {
        $data = $this->resolvePrintData($pembinaanPembekalan);

        $pdf = Pdf::loadView('pembekalan.pembinaan_pdf', $data)->setPaper('a4', 'portrait');

        return $pdf->stream('formulir-pembinaan-pkl-' . $pembinaanPembekalan->id . '.pdf');
    }

    private function resolvePrintData(PembinaanPembekalan $pembinaanPembekalan): array
    {
        $pembimbingAuthId = $this->getAuthorizedPembimbingId();

        if (!empty($pembimbingAuthId) && (int) $pembinaanPembekalan->pembimbing_id !== (int) $pembimbingAuthId) {
            throw new UnauthorizedException('Anda tidak berwenang membuka formulir ini.');
        }

        $pembinaanPembekalan->loadMissing(['siswa.kelas.jurusan', 'pembimbing']);

        return [
            'record' => $pembinaanPembekalan,
            'jenisPembinaanOptions' => self::JENIS_PEMBINAAN,
            'tindakanPembinaanOptions' => self::TINDAKAN_PEMBINAAN,
            'hasilPembinaanOptions' => self::HASIL_PEMBINAAN,
            'tingkatPembinaanOptions' => self::TINGKAT_PEMBINAAN,
        ];
    }

    private function validatePayload(Request $request, ?int $pembimbingAuthId): array
    {
        $validated = $request->validate([
            'siswa_id' => ['required', 'exists:siswa,id'],
            'pembimbing_id' => ['nullable', 'exists:pembimbings,id'],
            'tanggal_formulir' => ['required', 'date'],
            'waktu_formulir' => ['nullable', 'string', 'max:20'],
            'tempat' => ['nullable', 'string', 'max:191'],
            'kronologi' => ['nullable', 'string'],
            'komitmen_peserta' => ['nullable', 'string'],
            'catatan_guru' => ['nullable', 'string'],
            'jenis_pembinaan' => ['nullable', 'array'],
            'jenis_pembinaan.*' => ['in:' . implode(',', array_keys(self::JENIS_PEMBINAAN))],
            'jenis_pembinaan_lainnya' => ['nullable', 'string', 'max:255'],
            'tindakan_pembinaan' => ['nullable', 'array'],
            'tindakan_pembinaan.*' => ['in:' . implode(',', array_keys(self::TINDAKAN_PEMBINAAN))],
            'tindakan_pembinaan_lainnya' => ['nullable', 'string', 'max:255'],
            'hasil_pembinaan' => ['nullable', 'array'],
            'hasil_pembinaan.*' => ['in:' . implode(',', array_keys(self::HASIL_PEMBINAAN))],
            'tingkat_pembinaan' => ['nullable', 'in:' . implode(',', array_keys(self::TINGKAT_PEMBINAAN))],
        ]);

        if (!empty($pembimbingAuthId)) {
            $validated['pembimbing_id'] = $pembimbingAuthId;
        } elseif (empty($validated['pembimbing_id'])) {
            $resolvedPembimbingId = $this->resolvePembimbingForSiswa((int) $validated['siswa_id']);

            if (empty($resolvedPembimbingId)) {
                throw ValidationException::withMessages([
                    'siswa_id' => 'Siswa belum terhubung dengan kelompok yang memiliki pembimbing.',
                ]);
            }

            $validated['pembimbing_id'] = $resolvedPembimbingId;
        }

        $this->validatePembimbingForSiswa((int) $validated['siswa_id'], (int) $validated['pembimbing_id']);

        $validated['jenis_pembinaan'] = array_values(array_unique($validated['jenis_pembinaan'] ?? []));
        $validated['tindakan_pembinaan'] = array_values(array_unique($validated['tindakan_pembinaan'] ?? []));
        $validated['hasil_pembinaan'] = array_values(array_unique($validated['hasil_pembinaan'] ?? []));

        return $validated;
    }

    private function buildFilteredQuery(array $filters)
    {
        $query = PembinaanPembekalan::query()
            ->with(['siswa.kelas.jurusan', 'pembimbing'])
            ->latest('tanggal_formulir')
            ->latest('id');

        if (!empty($filters['tanggal_awal'])) {
            $query->whereDate('tanggal_formulir', '>=', $filters['tanggal_awal']);
        }

        if (!empty($filters['tanggal_akhir'])) {
            $query->whereDate('tanggal_formulir', '<=', $filters['tanggal_akhir']);
        }

        if (!empty($filters['pembimbing_id'])) {
            $query->where('pembimbing_id', $filters['pembimbing_id']);
        }

        if (!empty($filters['siswa_id'])) {
            $query->where('siswa_id', $filters['siswa_id']);
        }

        if (!empty($filters['keyword'])) {
            $keyword = trim((string) $filters['keyword']);
            $query->where(function ($q) use ($keyword) {
                $q->where('kronologi', 'like', '%' . $keyword . '%')
                    ->orWhere('catatan_guru', 'like', '%' . $keyword . '%')
                    ->orWhereHas('siswa', function ($sq) use ($keyword) {
                        $sq->where('nama_siswa', 'like', '%' . $keyword . '%')
                            ->orWhere('nis', 'like', '%' . $keyword . '%');
                    })
                    ->orWhereHas('pembimbing', function ($pq) use ($keyword) {
                        $pq->where('nama_pembimbing', 'like', '%' . $keyword . '%');
                    });
            });
        }

        return $query;
    }

    private function getAuthorizedPembimbingId(): ?int
    {
        $authUser = auth()->user();

        if ($authUser && Gate::forUser($authUser)->allows('panitia')) {
            return null;
        }

        if ($authUser && (Gate::forUser($authUser)->allows('pembimbing') || $authUser->role === 'pembimbing')) {
            $pembimbingAuthId = Pembimbing::query()
                ->where('nip_pembimbing', (string) $authUser->username)
                ->value('id');

            if (empty($pembimbingAuthId)) {
                throw new UnauthorizedException('Data pembimbing untuk akun ini tidak ditemukan.');
            }

            return (int) $pembimbingAuthId;
        }

        throw new UnauthorizedException('Anda tidak memiliki akses ke menu pembinaan pembekalan.');
    }

    private function validatePembimbingForSiswa(int $siswaId, int $pembimbingId): void
    {
        $isAllowed = KelompokBimbingan::query()
            ->where('pembimbing_id', $pembimbingId)
            ->whereHas('siswa', function ($q) use ($siswaId) {
                $q->where('siswa.id', $siswaId);
            })
            ->exists();

        if (!$isAllowed) {
            throw ValidationException::withMessages([
                'siswa_id' => 'Siswa tidak berada pada kelompok bimbingan pembimbing terpilih.',
            ]);
        }
    }

    private function resolvePembimbingForSiswa(int $siswaId): ?int
    {
        return KelompokBimbingan::query()
            ->whereNotNull('pembimbing_id')
            ->whereHas('siswa', function ($q) use ($siswaId) {
                $q->where('siswa.id', $siswaId);
            })
            ->value('pembimbing_id');
    }
}
