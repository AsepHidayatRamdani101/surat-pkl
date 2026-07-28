<?php

namespace App\Http\Controllers;

use App\Models\MessageTemplate;
use App\Models\MessageLog;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\Facades\DataTables;

class MessageTemplateController extends Controller
{
    public function index()
    {
        $templates = MessageTemplate::with('createdBy')->latest()->get();
        return view('message_template.index', compact('templates'));
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {
            $templates = MessageTemplate::with('createdBy')->latest();

            return DataTables::of($templates)
                ->addIndexColumn()
                ->addColumn('tipe', function ($row) {
                    $badges = [
                        'informasi' => 'badge-info',
                        'pengumuman' => 'badge-warning',
                        'undangan' => 'badge-primary',
                        'lainnya' => 'badge-secondary',
                    ];
                    $badge = $badges[$row->tipe_template] ?? 'badge-secondary';
                    return '<span class="badge ' . $badge . '">' . ucfirst($row->tipe_template) . '</span>';
                })
                ->addColumn('pembuat', function ($row) {
                    return $row->createdBy->name ?? 'Admin';
                })
                ->addColumn('tanggal', function ($row) {
                    return $row->created_at->format('d/m/Y H:i');
                })
                ->addColumn('aksi', function ($row) {
                    return '
                        <button class="btn btn-sm btn-info btnEdit" data-id="' . $row->id . '">Edit</button>
                        <button class="btn btn-sm btn-success btnKirim" data-id="' . $row->id . '">Kirim</button>
                        <button class="btn btn-sm btn-danger btnHapus" data-id="' . $row->id . '">Hapus</button>
                    ';
                })
                ->rawColumns(['tipe', 'aksi'])
                ->make(true);
        }
    }

    public function create()
    {
        return view('message_template.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_template' => 'required|string|max:255',
            'isi_template' => 'required|string|max:1000',
            'tipe_template' => 'required|in:informasi,pengumuman,undangan,lainnya',
        ]);

        $validated['created_by'] = auth()->id();

        MessageTemplate::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Template berhasil dibuat.']);
        }

        return redirect()->route('message-template.index')->with('success', 'Template berhasil dibuat.');
    }

    public function edit(MessageTemplate $messageTemplate)
    {
        return view('message_template.form', compact('messageTemplate'));
    }

    public function update(Request $request, MessageTemplate $messageTemplate)
    {
        $validated = $request->validate([
            'nama_template' => 'required|string|max:255',
            'isi_template' => 'required|string|max:1000',
            'tipe_template' => 'required|in:informasi,pengumuman,undangan,lainnya',
        ]);

        $messageTemplate->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Template berhasil diperbarui.']);
        }

        return redirect()->route('message-template.index')->with('success', 'Template berhasil diperbarui.');
    }

    public function destroy(MessageTemplate $messageTemplate)
    {
        $messageTemplate->delete();
        return response()->json(['success' => true, 'message' => 'Template berhasil dihapus.']);
    }

    public function getForm(MessageTemplate $messageTemplate = null)
    {
        $data = $messageTemplate ? $messageTemplate->toArray() : [];
        return response()->json($data);
    }

    public function send(MessageTemplate $messageTemplate)
    {
        return view('message_template.send', compact('messageTemplate'));
    }

    public function sendMessage()
    {
        return view('message_template.send-message');
    }

    public function sendModal(MessageTemplate $messageTemplate)
    {
        $siswas = Siswa::orderBy('nama_siswa')->get(['id', 'nis', 'nama_siswa', 'no_hp_siswa']);
        return response()->json(['template' => $messageTemplate, 'siswas' => $siswas]);
    }

    public function apiSiswaList()
    {
        $siswas = Siswa::whereNotNull('no_hp_siswa')
            ->where('no_hp_siswa', '!=', '')
            ->orderBy('nama_siswa')
            ->get(['id', 'nis', 'nama_siswa', 'no_hp_siswa']);
        
        return response()->json($siswas);
    }

    public function sendPersonal(Request $request, MessageTemplate $messageTemplate)
    {
        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'message' => 'nullable|string|max:1000',
        ]);

        $siswa = Siswa::findOrFail($validated['siswa_id']);

        if (!$siswa->no_hp_siswa) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Nomor HP siswa tidak tersedia.'], 400);
            }
            return back()->with('error', 'Nomor HP siswa tidak tersedia.');
        }

        // Gunakan custom message jika ada, jika tidak gunakan template
        $pesan = !empty($validated['message']) ? $validated['message'] : $messageTemplate->isi_template;

        $response = $this->sendViaffonnte(
            $siswa->no_hp_siswa,
            $pesan
        );

        $status = $response['success'] ? 'terkirim' : 'gagal';

        MessageLog::create([
            'template_id' => $messageTemplate->id,
            'nomor_penerima' => $siswa->no_hp_siswa,
            'isi_pesan' => $pesan,
            'tipe_pengiriman' => 'personal',
            'status_pengiriman' => $status,
            'response_fonnte' => json_encode($response),
            'dikirim_oleh' => auth()->id(),
        ]);

        $message = $response['success'] 
            ? 'Pesan berhasil dikirim ke ' . $siswa->nama_siswa . ' (' . $siswa->no_hp_siswa . ')'
            : 'Gagal mengirim pesan ke ' . $siswa->nama_siswa . '.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $response['success'],
                'message' => $message
            ], $response['success'] ? 200 : 400);
        }

        return back()->with($response['success'] ? 'success' : 'error', $message);
    }

    public function sendMass(Request $request, MessageTemplate $messageTemplate)
    {
        $validated = $request->validate([
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'exists:siswa,id',
            'message' => 'nullable|string|max:1000',
        ]);

        $siswas = Siswa::whereIn('id', $validated['siswa_ids'])
            ->whereNotNull('no_hp_siswa')
            ->where('no_hp_siswa', '!=', '')
            ->get();

        if ($siswas->isEmpty()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Tidak ada siswa dengan nomor HP yang valid.'], 400);
            }
            return back()->with('error', 'Tidak ada siswa dengan nomor HP yang valid.');
        }

        // Gunakan custom message jika ada, jika tidak gunakan template
        $pesan = !empty($validated['message']) ? $validated['message'] : $messageTemplate->isi_template;
        $successCount = 0;
        $failCount = 0;

        foreach ($siswas as $siswa) {
            $response = $this->sendViaffonnte(
                $siswa->no_hp_siswa,
                $pesan
            );

            $status = $response['success'] ? 'terkirim' : 'gagal';

            MessageLog::create([
                'template_id' => $messageTemplate->id,
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

        $message = "Pengiriman selesai. Berhasil: {$successCount}, Gagal: {$failCount}";
        
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }
        
        return back()->with('success', $message);
    }

    private function sendViaffonnte($nomorTujuan, $pesan)
    {
        try {
            // Format nomor HP: 62xxxxx (tanpa 0 di awal)
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

    public function logs()
    {
        return view('message_template.logs');
    }

    public function logsData(Request $request)
    {
        if ($request->ajax()) {
            $logs = MessageLog::with(['template', 'sentBy'])
                ->latest()
                ->when($request->filled('status'), function ($query) use ($request) {
                    $query->where('status_pengiriman', $request->input('status'));
                })
                ->when($request->filled('tipe'), function ($query) use ($request) {
                    $query->where('tipe_pengiriman', $request->input('tipe'));
                });

            return DataTables::of($logs)
                ->addIndexColumn()
                ->addColumn('template_name', function ($row) {
                    return $row->template?->nama_template ?? 'Template Dihapus';
                })
                ->addColumn('nomor_hp', function ($row) {
                    return $row->nomor_penerima;
                })
                ->addColumn('tipe', function ($row) {
                    $badges = [
                        'personal' => 'badge-info',
                        'masal' => 'badge-warning',
                    ];
                    $badge = $badges[$row->tipe_pengiriman] ?? 'badge-secondary';
                    return '<span class="badge ' . $badge . '">' . ucfirst($row->tipe_pengiriman) . '</span>';
                })
                ->addColumn('status', function ($row) {
                    $badges = [
                        'terkirim' => 'badge-success',
                        'pending' => 'badge-warning',
                        'gagal' => 'badge-danger',
                    ];
                    $badge = $badges[$row->status_pengiriman] ?? 'badge-secondary';
                    return '<span class="badge ' . $badge . '">' . ucfirst($row->status_pengiriman) . '</span>';
                })
                ->addColumn('pengirim', function ($row) {
                    return $row->sentBy?->name ?? 'Admin';
                })
                ->addColumn('tanggal', function ($row) {
                    return $row->created_at->format('d/m/Y H:i');
                })
                ->rawColumns(['tipe', 'status'])
                ->make(true);
        }
    }
}
