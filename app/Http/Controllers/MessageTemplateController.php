<?php

namespace App\Http\Controllers;

use App\Models\MessageTemplate;
use App\Models\MessageLog;
use App\Models\Siswa;
use App\Models\Pembimbing;
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

    public function apiGuruList()
    {
        $gurus = Pembimbing::whereNotNull('no_hp_pembimbing')
            ->where('no_hp_pembimbing', '!=', '')
            ->orderBy('nama_pembimbing')
            ->get(['id', 'nip_pembimbing', 'nama_pembimbing', 'no_hp_pembimbing']);
        
        return response()->json($gurus);
    }

    public function apiOrangtuaList()
    {
        $orangtuas = Siswa::whereNotNull('no_hp_ortu')
            ->where('no_hp_ortu', '!=', '')
            ->orderBy('nama_ortu')
            ->get(['id', 'nis', 'nama_ortu', 'no_hp_ortu']);
        
        return response()->json($orangtuas);
    }

    public function sendPersonal(Request $request, MessageTemplate $messageTemplate)
    {
        $recipientType = $request->input('recipient_type', 'siswa');
        
        if ($recipientType === 'guru') {
            return $this->sendToGuru($request, $messageTemplate);
        } elseif ($recipientType === 'orangtua') {
            return $this->sendToOrangtua($request, $messageTemplate);
        } else {
            return $this->sendToSiswa($request, $messageTemplate);
        }
    }

    private function sendToSiswa(Request $request, MessageTemplate $messageTemplate)
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

        $pesan = !empty($validated['message']) ? $validated['message'] : $messageTemplate->isi_template;
        $response = $this->sendViaffonnte($siswa->no_hp_siswa, $pesan);
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
            return response()->json(['success' => $response['success'], 'message' => $message], $response['success'] ? 200 : 400);
        }
        return back()->with($response['success'] ? 'success' : 'error', $message);
    }

    private function sendToGuru(Request $request, MessageTemplate $messageTemplate)
    {
        $validated = $request->validate([
            'guru_id' => 'required|exists:pembimbings,id',
            'message' => 'nullable|string|max:1000',
        ]);

        $guru = Pembimbing::findOrFail($validated['guru_id']);

        if (!$guru->no_hp_pembimbing) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Nomor HP guru tidak tersedia.'], 400);
            }
            return back()->with('error', 'Nomor HP guru tidak tersedia.');
        }

        $pesan = !empty($validated['message']) ? $validated['message'] : $messageTemplate->isi_template;
        $response = $this->sendViaffonnte($guru->no_hp_pembimbing, $pesan);
        $status = $response['success'] ? 'terkirim' : 'gagal';

        MessageLog::create([
            'template_id' => $messageTemplate->id,
            'nomor_penerima' => $guru->no_hp_pembimbing,
            'isi_pesan' => $pesan,
            'tipe_pengiriman' => 'personal',
            'status_pengiriman' => $status,
            'response_fonnte' => json_encode($response),
            'dikirim_oleh' => auth()->id(),
        ]);

        $message = $response['success'] 
            ? 'Pesan berhasil dikirim ke ' . $guru->nama_pembimbing . ' (' . $guru->no_hp_pembimbing . ')'
            : 'Gagal mengirim pesan ke ' . $guru->nama_pembimbing . '.';

        if ($request->expectsJson()) {
            return response()->json(['success' => $response['success'], 'message' => $message], $response['success'] ? 200 : 400);
        }
        return back()->with($response['success'] ? 'success' : 'error', $message);
    }

    private function sendToOrangtua(Request $request, MessageTemplate $messageTemplate)
    {
        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'message' => 'nullable|string|max:1000',
        ]);

        $siswa = Siswa::findOrFail($validated['siswa_id']);

        if (!$siswa->no_hp_ortu) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Nomor HP orangtua tidak tersedia.'], 400);
            }
            return back()->with('error', 'Nomor HP orangtua tidak tersedia.');
        }

        $pesan = !empty($validated['message']) ? $validated['message'] : $messageTemplate->isi_template;
        $response = $this->sendViaffonnte($siswa->no_hp_ortu, $pesan);
        $status = $response['success'] ? 'terkirim' : 'gagal';

        MessageLog::create([
            'template_id' => $messageTemplate->id,
            'nomor_penerima' => $siswa->no_hp_ortu,
            'isi_pesan' => $pesan,
            'tipe_pengiriman' => 'personal',
            'status_pengiriman' => $status,
            'response_fonnte' => json_encode($response),
            'dikirim_oleh' => auth()->id(),
        ]);

        $message = $response['success'] 
            ? 'Pesan berhasil dikirim ke ' . $siswa->nama_ortu . ' (' . $siswa->no_hp_ortu . ')'
            : 'Gagal mengirim pesan ke ' . $siswa->nama_ortu . '.';

        if ($request->expectsJson()) {
            return response()->json(['success' => $response['success'], 'message' => $message], $response['success'] ? 200 : 400);
        }
        return back()->with($response['success'] ? 'success' : 'error', $message);
    }

    public function sendMass(Request $request, MessageTemplate $messageTemplate)
    {
        $recipientType = $request->input('recipient_type', 'siswa');
        
        if ($recipientType === 'guru') {
            return $this->sendMassToGuru($request, $messageTemplate);
        } elseif ($recipientType === 'orangtua') {
            return $this->sendMassToOrangtua($request, $messageTemplate);
        } else {
            return $this->sendMassToSiswa($request, $messageTemplate);
        }
    }

    private function sendMassToSiswa(Request $request, MessageTemplate $messageTemplate)
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

        $pesan = !empty($validated['message']) ? $validated['message'] : $messageTemplate->isi_template;
        $successCount = 0;
        $failCount = 0;

        foreach ($siswas as $siswa) {
            $response = $this->sendViaffonnte($siswa->no_hp_siswa, $pesan);
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

            if ($response['success']) $successCount++;
            else $failCount++;
        }

        $message = "Pengiriman ke siswa selesai. Berhasil: {$successCount}, Gagal: {$failCount}";
        
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }
        return back()->with('success', $message);
    }

    private function sendMassToGuru(Request $request, MessageTemplate $messageTemplate)
    {
        $validated = $request->validate([
            'guru_ids' => 'required|array|min:1',
            'guru_ids.*' => 'exists:pembimbings,id',
            'message' => 'nullable|string|max:1000',
        ]);

        $gurus = Pembimbing::whereIn('id', $validated['guru_ids'])
            ->whereNotNull('no_hp_pembimbing')
            ->where('no_hp_pembimbing', '!=', '')
            ->get();

        if ($gurus->isEmpty()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Tidak ada guru dengan nomor HP yang valid.'], 400);
            }
            return back()->with('error', 'Tidak ada guru dengan nomor HP yang valid.');
        }

        $pesan = !empty($validated['message']) ? $validated['message'] : $messageTemplate->isi_template;
        $successCount = 0;
        $failCount = 0;

        foreach ($gurus as $guru) {
            $response = $this->sendViaffonnte($guru->no_hp_pembimbing, $pesan);
            $status = $response['success'] ? 'terkirim' : 'gagal';

            MessageLog::create([
                'template_id' => $messageTemplate->id,
                'nomor_penerima' => $guru->no_hp_pembimbing,
                'isi_pesan' => $pesan,
                'tipe_pengiriman' => 'masal',
                'status_pengiriman' => $status,
                'response_fonnte' => json_encode($response),
                'dikirim_oleh' => auth()->id(),
            ]);

            if ($response['success']) $successCount++;
            else $failCount++;
        }

        $message = "Pengiriman ke guru selesai. Berhasil: {$successCount}, Gagal: {$failCount}";
        
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }
        return back()->with('success', $message);
    }

    private function sendMassToOrangtua(Request $request, MessageTemplate $messageTemplate)
    {
        $validated = $request->validate([
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'exists:siswa,id',
            'message' => 'nullable|string|max:1000',
        ]);

        $orangtuas = Siswa::whereIn('id', $validated['siswa_ids'])
            ->whereNotNull('no_hp_ortu')
            ->where('no_hp_ortu', '!=', '')
            ->get();

        if ($orangtuas->isEmpty()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Tidak ada orangtua dengan nomor HP yang valid.'], 400);
            }
            return back()->with('error', 'Tidak ada orangtua dengan nomor HP yang valid.');
        }

        $pesan = !empty($validated['message']) ? $validated['message'] : $messageTemplate->isi_template;
        $successCount = 0;
        $failCount = 0;

        foreach ($orangtuas as $siswa) {
            $response = $this->sendViaffonnte($siswa->no_hp_ortu, $pesan);
            $status = $response['success'] ? 'terkirim' : 'gagal';

            MessageLog::create([
                'template_id' => $messageTemplate->id,
                'nomor_penerima' => $siswa->no_hp_ortu,
                'isi_pesan' => $pesan,
                'tipe_pengiriman' => 'masal',
                'status_pengiriman' => $status,
                'response_fonnte' => json_encode($response),
                'dikirim_oleh' => auth()->id(),
            ]);

            if ($response['success']) $successCount++;
            else $failCount++;
        }

        $message = "Pengiriman ke orangtua selesai. Berhasil: {$successCount}, Gagal: {$failCount}";
        
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
