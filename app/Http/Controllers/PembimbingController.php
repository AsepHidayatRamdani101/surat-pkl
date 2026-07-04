<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FormatsExcelSheets;
use App\Models\Jurusan;
use App\Models\Pembimbing;
use App\Models\User;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Contracts\DataTable;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PembimbingController extends Controller
{
    use FormatsExcelSheets;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pembimbing = Pembimbing::all();
        $jurusan = Jurusan::orderBy('nama_jurusan')->get();
        return view('pembimbing.index', compact('pembimbing', 'jurusan'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pembimbing.create');
    }

    public function data(Request $request)
    {
        $perusahaan = Pembimbing::with('jurusan')->get();
        $existingUsernames = User::query()
            ->whereIn('username', $perusahaan->pluck('nip_pembimbing')->map(fn($nip) => (string) $nip)->all())
            ->pluck('username')
            ->flip();

        $accountStatus = $request->input('account_status');
        if ($accountStatus === 'without') {
            $perusahaan = $perusahaan->filter(fn($row) => !isset($existingUsernames[(string) $row->nip_pembimbing]))->values();
        } elseif ($accountStatus === 'with') {
            $perusahaan = $perusahaan->filter(fn($row) => isset($existingUsernames[(string) $row->nip_pembimbing]))->values();
        }

        return DataTables::of($perusahaan)
            ->addColumn('jurusan_nama', function ($perusahaan) {
                return $perusahaan->jenis_guru === 'adaptif_normatif'
                    ? 'Semua Jurusan'
                    : ($perusahaan->jurusan->nama_jurusan ?? '-');
            })
            ->addColumn('jenis_guru_label', function ($perusahaan) {
                return $perusahaan->jenis_guru === 'adaptif_normatif'
                    ? 'Adaptif Normatif'
                    : 'Guru Produktif';
            })
            ->addColumn('jumlah_siswa', function ($perusahaan) {
                $hasil = (36 / 44) * (int) $perusahaan->jumlah_jam;
                return (int) round($hasil);
            })
            ->addColumn('status_akun', function ($perusahaan) use ($existingUsernames) {
                $hasAccount = isset($existingUsernames[(string) $perusahaan->nip_pembimbing]);

                return $hasAccount
                    ? '<span class="badge badge-success">Sudah Ada</span>'
                    : '<span class="badge badge-secondary">Belum Ada</span>';
            })
            ->addColumn('aksi', function ($perusahaan) {
                return '
                    <a href="javascript:void(0)" class="btn btn-sm btn-warning btnEdit" 
                    data-id="' . $perusahaan->id . '"
                    data-nama="' . $perusahaan->nama_pembimbing . '"
                      data-nip="' . $perusahaan->nip_pembimbing . '"
                      data-jabatan="' . $perusahaan->jabatan_pembimbing . '"
                      data-jenis="' . $perusahaan->jenis_kelamin . '"
                                            data-nohp="' . $perusahaan->no_hp_pembimbing . '"
                                            data-jumlah-jam="' . $perusahaan->jumlah_jam . '"
                                            data-jenis-guru="' . $perusahaan->jenis_guru . '"
                                            data-jurusan-id="' . $perusahaan->jurusan_id . '"                  
                    >Edit</a>
                    <a href="javascript:void(0)" class="btn btn-sm btn-danger btnHapus" data-id="' . $perusahaan->id . '">Hapus</a>
                ';
            })
            ->addIndexColumn()
            ->rawColumns(['status_akun', 'aksi'])
            ->make(true);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_pembimbing' => 'required',
            'nip_pembimbing' => 'required',
            'jenis_kelamin' => 'required',
            'jabatan_pembimbing' => 'required',
            'no_hp_pembimbing' => 'required',
            'jumlah_jam' => 'required|integer|min:0',
            'jenis_guru' => 'required|in:adaptif_normatif,guru_produktif',
            'jurusan_id' => 'nullable|exists:jurusan,id|required_if:jenis_guru,guru_produktif',
        ]);

        $jenisKelamin = $this->normalizeJenisKelamin($request->jenis_kelamin);

        if ($jenisKelamin === null) {
            return response()->json(['message' => 'Jenis kelamin tidak valid.'], 422);
        }

        Pembimbing::create([
            'nama_pembimbing' => $request->nama_pembimbing,
            'nip_pembimbing' => $request->nip_pembimbing,
            'jenis_kelamin' => $jenisKelamin,
            'jabatan_pembimbing' => $request->jabatan_pembimbing,
            'no_hp_pembimbing' => $request->no_hp_pembimbing,
            'jumlah_jam' => $request->jumlah_jam,
            'jenis_guru' => $request->jenis_guru,
            'jurusan_id' => $request->jenis_guru === 'adaptif_normatif' ? null : $request->jurusan_id,

        ]);

        return redirect()->route('pembimbing.index')->with('success', 'Data Pembimbing Berhasil Ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $pembimbing = Pembimbing::findOrFail($id);
        return response()->json($pembimbing);
    }


    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        $file = $request->file('file');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = array_map(function ($v) {
            return array_values($v);
        }, array_slice($sheet->toArray(), 1));

        foreach ($rows as $row) {
            if (!empty($row[0])) {
                $jenisGuru = $this->normalizeJenisGuru($row[6] ?? null);
                $jurusanId = null;

                if ($jenisGuru === 'guru_produktif' && !empty($row[7])) {
                    $jurusan = Jurusan::where('nama_jurusan', $row[7])
                        ->orWhere('kode_jurusan', $row[7])
                        ->first();

                    $jurusanId = $jurusan?->id;
                }

                $pembimbing = new Pembimbing([
                    'nama_pembimbing' => $row[0],
                    'nip_pembimbing' => $row[1],
                    'jenis_kelamin' => $row[2],
                    'jabatan_pembimbing' => $row[3],
                    'no_hp_pembimbing' => $row[4],
                    'jumlah_jam' => is_numeric($row[5] ?? null) ? (int) $row[5] : 0,
                    'jenis_guru' => $jenisGuru,
                    'jurusan_id' => $jurusanId,
                ]);
                $pembimbing->save();
            }
        }

        return redirect()->back()->with('success', 'Data Pembimbing Berhasil Diimport');
    }

    public function exportExcel()
    {
        $pembimbing = Pembimbing::with('jurusan')->get();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Nama');
        $sheet->setCellValue('B1', 'NIP');
        $sheet->setCellValue('C1', 'Jenis Kelamin');
        $sheet->setCellValue('D1', 'Jabatan');
        $sheet->setCellValue('E1', 'No HP');
        $sheet->setCellValue('F1', 'Jumlah Jam');
        $sheet->setCellValue('G1', 'Jenis Guru');
        $sheet->setCellValue('H1', 'Jurusan');
        $row = 2;
        foreach ($pembimbing as $p) {
            $sheet->setCellValue('A' . $row, $p->nama_pembimbing);
            $sheet->setCellValueExplicit('B' . $row, (string) $p->nip_pembimbing, DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $row, $p->jenis_kelamin);
            $sheet->setCellValue('D' . $row, $p->jabatan_pembimbing);
            $sheet->setCellValue('E' . $row, $p->no_hp_pembimbing);
            $sheet->setCellValue('F' . $row, $p->jumlah_jam);
            $sheet->setCellValue('G' . $row, $this->friendlyJenisGuru($p->jenis_guru));
            $sheet->setCellValue('H' . $row, $p->jenis_guru === 'adaptif_normatif' ? 'Semua Jurusan' : ($p->jurusan->nama_jurusan ?? ''));
            $row++;
        }

        $this->applyExcelTableFormatting($sheet, 'H', $row - 1);

        $writer = new Xlsx($spreadsheet);
        $filename = 'pembimbing.xlsx';
        $writer->save($filename);
        return response()->download('pembimbing.xlsx')->deleteFileAfterSend(true);
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Nama');
        $sheet->setCellValue('B1', 'NIP');
        $sheet->setCellValue('C1', 'Jenis Kelamin');
        $sheet->setCellValue('D1', 'Jabatan');
        $sheet->setCellValue('E1', 'No HP');
        $sheet->setCellValue('F1', 'Jumlah Jam');
        $sheet->setCellValue('G1', 'Jenis Guru');
        $sheet->setCellValue('H1', 'Jurusan');

        $sheet->setCellValue('A2', 'Contoh Nama Guru');
        $sheet->setCellValueExplicit('B2', '198503122010011001', DataType::TYPE_STRING);
        $sheet->setCellValue('C2', 'Laki-laki');
        $sheet->setCellValue('D2', 'Guru');
        $sheet->setCellValue('E2', '081210000001');
        $sheet->setCellValue('F2', '24');
        $sheet->setCellValue('G2', 'Adaptif Normatif');
        $sheet->setCellValue('H2', 'Semua Jurusan');

        $this->applyExcelTableFormatting($sheet, 'H', 2);

        $writer = new Xlsx($spreadsheet);
        $fileName = 'template_import_pembimbing_' . date('d_m_Y_H_i_s') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        $pembimbing = Pembimbing::findOrFail($id);
        return response()->json($pembimbing);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_pembimbing' => 'required',
            'nip_pembimbing' => 'required',
            'jenis_kelamin' => 'required',
            'jabatan_pembimbing' => 'required',
            'no_hp_pembimbing' => 'required',
            'jumlah_jam' => 'required|integer|min:0',
            'jenis_guru' => 'required|in:adaptif_normatif,guru_produktif',
            'jurusan_id' => 'nullable|exists:jurusan,id|required_if:jenis_guru,guru_produktif',
        ]);

        $jenisKelamin = $this->normalizeJenisKelamin($request->jenis_kelamin);

        if ($jenisKelamin === null) {
            return response()->json(['message' => 'Jenis kelamin tidak valid.'], 422);
        }

        $pembimbing = Pembimbing::findOrFail($id);
        $pembimbing->update([
            'nama_pembimbing' => $request->nama_pembimbing,
            'nip_pembimbing' => $request->nip_pembimbing,
            'jenis_kelamin' => $jenisKelamin,
            'jabatan_pembimbing' => $request->jabatan_pembimbing,
            'no_hp_pembimbing' => $request->no_hp_pembimbing,
            'jumlah_jam' => $request->jumlah_jam,
            'jenis_guru' => $request->jenis_guru,
            'jurusan_id' => $request->jenis_guru === 'adaptif_normatif' ? null : $request->jurusan_id,
        ]);

        return response()->json(['success' => 'Data Pembimbing Berhasil Diupdate']);
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $pembimbing = Pembimbing::findOrFail($id);
        $pembimbing->delete();
        return response()->json(['success' => 'Data Pembimbing Berhasil Dihapus']);
    }

    private function normalizeJenisGuru($value): string
    {
        $normalized = strtolower(trim((string) $value));

        return match ($normalized) {
            'guru_produktif', 'guru produktif' => 'guru_produktif',
            default => 'adaptif_normatif',
        };
    }

    private function normalizeJenisKelamin($value): ?string
    {
        $normalized = strtolower(trim((string) $value));

        return match ($normalized) {
            'laki-laki', 'laki laki', 'lakilaki', 'laki-laki ' => 'Laki-laki',
            'perempuan' => 'Perempuan',
            default => null,
        };
    }

    private function friendlyJenisGuru(string $value): string
    {
        return $value === 'guru_produktif' ? 'Guru Produktif' : 'Adaptif Normatif';
    }

    public function generateAccounts()
    {
        $pembimbingList = Pembimbing::orderBy('nama_pembimbing')->get();

        if ($pembimbingList->isEmpty()) {
            return response()->json(['message' => 'Data pembimbing belum tersedia.'], 422);
        }

        $roleName = 'pembimbing';
        $role = Role::query()->firstOrCreate([
            'name' => $roleName,
            'guard_name' => 'web',
        ]);

        $password = 'guru12345';
        $created = 0;
        $updated = 0;

        foreach ($pembimbingList as $pembimbing) {
            $username = (string) $pembimbing->nip_pembimbing;
            $email = $username . '@pembimbing.local';

            $user = User::query()
                ->where('username', $username)
                ->orWhere('email', $email)
                ->first();

            if ($user) {
                $user->update([
                    'name' => $pembimbing->nama_pembimbing,
                    'username' => $username,
                    'email' => $email,
                    'password' => $password,
                    'role' => $roleName,
                ]);
                $updated++;
            } else {
                $user = User::create([
                    'name' => $pembimbing->nama_pembimbing,
                    'username' => $username,
                    'email' => $email,
                    'password' => $password,
                    'role' => $roleName,
                ]);
                $created++;
            }

            $user->syncRoles([$role->name]);
        }

        return response()->json([
            'message' => "Generate akun pembimbing selesai. Baru: {$created}, diperbarui: {$updated}. Username = NIP, password = guru12345",
        ]);
    }
}
