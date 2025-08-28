<?php

namespace App\Http\Controllers;

use App\Models\Monitoring;
use App\Models\Pembimbing;
use App\Models\Perusahaan;
use App\Models\Siswa;
use App\Models\TempatPkl;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Writer\Pdf as WriterPdf;
use Yajra\DataTables\Facades\DataTables;

class MonitoringController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Grouping by perusahaan
            if (auth()->user()->role == 'panitia') {
                $grouped = TempatPkl::with(['siswa', 'perusahaan', 'pembimbing'])
                    ->get();
            } else {
                $grouped = TempatPkl::with(['siswa', 'perusahaan', 'pembimbing'])
                    ->whereHas('siswa.kelas.jurusan', function ($query) {
                        $query->where('id', auth()->user()->jurusan_id);
                    });
            }

            return DataTables::of($grouped)
                ->addIndexColumn()
                ->addColumn('aksi', function ($row) {
                    return '
                        <button class="btn btn-sm btn-warning btn-edit" 
                        data-id="' . $row->id . '"
                        data-perusahaan="' . $row->perusahaan_id . '"
                        data-siswa="' . $row->siswa_id . '"
                        data-tanggal-mulai="' . $row->tanggal_mulai . '"
                        data-tanggal-selesai="' . $row->tanggal_selesai . '"
                        >Edit
                        </button>
                        <button class="btn btn-sm btn-success btnUpdateKesediaan" 
                        data-id="' . $row->id . '"
                        >Upload
                        </button>
                        <button class="btn btn-sm btn-danger btn-hapus" data-id="' . $row['id'] . '">
                            Hapus
                        </button>
                        
                    ';
                })
                ->rawColumns(['aksi'])
                ->make(true);
        }

        $siswa = Siswa::orderBy('nama_siswa')->get();
        $perusahaan = Perusahaan::orderBy('nama_perusahaan')->get();
        $pembimbing = Pembimbing::orderBy('nama_pembimbing')->get();
        //   var_dump($siswa);
        return view('monitoring.index', compact('siswa', 'perusahaan', 'pembimbing'));
    }


    /**
     * Display a listing of the resource in data table.
     */
    public function index_cetak(Request $request)
    {
        if ($request->ajax()) {
            // Grouping by perusahaan
            $grouped = TempatPkl::with(['siswa.kelas', 'perusahaan'])
                ->get()
                ->groupBy('perusahaan_id', 'pembimbing_id')
                ->map(function ($group) {
                    return [
                        'id' => $group->first()->perusahaan_id,
                        'perusahaan' => $group->first()->perusahaan->nama_perusahaan,
                        'siswa' => $group->pluck('siswa.nama_siswa')->implode(', '),
                        'pembimbing' => $group->first()->pembimbing->nama_pembimbing ?? '-'
                    ];
                })->values();

            return DataTables::of($grouped)
                ->addIndexColumn()
                ->addColumn('aksi', function ($row) {
                    return '
                        <button type="button" class="btn btn-sm btn-warning btn-edit-pembimbing" data-id="' . $row['id'] . '" data-pembimbing="' . $row['pembimbing'] . '">Edit Pembimbing</button>
                        <a href="' . route('monitoring.cetak-monitoring', $row['id']) . '" target="_blank" class="btn btn-sm btn-success btn-cetak" data-id="' . $row['id'] . '">Cetak</a>

                        <a href="' . route('monitoring.cetak-sppd', $row['id']) . '" target="_blank" class="btn btn-sm btn-info btn-cetak" data-id="' . $row['id'] . '">Cetak SPPD</a>';
                })
                ->rawColumns(['aksi'])
                ->make(true);
        }

        $siswa = Siswa::all();
        $perusahaan = Perusahaan::all();
        $pembimbing = Pembimbing::all();
        //   var_dump($siswa);
        return view('monitoring.index_cetak', compact('siswa', 'perusahaan', 'pembimbing'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('monitoring.create');
    }


    /**
     * cetak monitoring perusahaan.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function cetak($id)
    {
        $data = DB::table('tempat_pkl as tp')
            ->leftJoin('siswa as s', 'tp.siswa_id', '=', 's.id')
            ->leftJoin('perusahaan as p', 'tp.perusahaan_id', '=', 'p.id')
            ->leftJoin('pembimbings as pb', 'tp.pembimbing_id', '=', 'pb.id')
            ->select(
                's.id as siswa_id',
                's.nama_siswa',
                'p.id as perusahaan_id',
                'p.nama_perusahaan',
                'pb.nama_pembimbing as nama_pembimbing',
                'tp.tanggal_mulai',
                'tp.tanggal_selesai',
            )
            ->where('p.id', $id)
            ->get();

        $pdf = pdf::loadView('tempat_pkl.cetak', compact('data'));
        return $pdf->stream('surat-izin-pkl.pdf'); // atau ->download('namafile.pdf');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function lihatdata(Request $request, $id)
    {

        $data = TempatPkl::with(['siswa', 'perusahaan', 'pembimbing'])->where('perusahaan_id', $id)->first();
        return response()->json($data);
    }


    public function cetakMonitoring($id)
    {
        $data = TempatPkl::with(['siswa.kelas.jurusan', 'perusahaan', 'pembimbing'])
            ->where('perusahaan_id', $id)
            ->get();


        $pdf = pdf::loadView('monitoring.cetak_st', compact('data'));
        return $pdf->stream('surat-izin-pkl.pdf'); // atau ->download('namafile.pdf');

    }

    public function cetakSppd($id)
    {
        $data = TempatPkl::with(['siswa.kelas.jurusan', 'perusahaan', 'pembimbing'])
            ->where('perusahaan_id', $id)
            ->get();

        $pdf = pdf::loadView('monitoring.cetak_sppd', compact('data'));
        return $pdf->stream('surat-perjalanan-dinas.pdf'); // atau ->download('namafile.pdf');
    }



    /**
     * Show the form for editing the specified resource.
     */

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Monitoring $monitoring)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Monitoring $monitoring)
    {
        //
    }
}
