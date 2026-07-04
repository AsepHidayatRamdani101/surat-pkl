<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('tugas_pembekalans')
            ->select(['id', 'judul_tugas', 'deskripsi_tugas', 'soal_essay'])
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $existing = json_decode((string) ($row->soal_essay ?? ''), true);

                    $questions = collect(is_array($existing) ? $existing : [])
                        ->map(fn($item) => trim((string) $item))
                        ->filter()
                        ->values()
                        ->all();

                    if (count($questions) < 2) {
                        $title = trim((string) ($row->judul_tugas ?? ''));
                        $description = trim((string) ($row->deskripsi_tugas ?? ''));

                        if (count($questions) === 0) {
                            $questions[] = $title !== ''
                                ? 'Jelaskan secara rinci topik: ' . $title
                                : 'Jelaskan materi utama pada tugas ini.';
                        }

                        if (count($questions) === 1) {
                            $questions[] = $description !== ''
                                ? 'Uraikan langkah, analisis, atau hasil berdasarkan deskripsi tugas berikut: ' . $description
                                : 'Tuliskan kesimpulan dan refleksi Anda terhadap tugas ini.';
                        }
                    }

                    DB::table('tugas_pembekalans')
                        ->where('id', $row->id)
                        ->update([
                            'soal_essay' => json_encode(array_values($questions), JSON_UNESCAPED_SLASHES),
                        ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Backfill data is intentionally kept.
    }
};
