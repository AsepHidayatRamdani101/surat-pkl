<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = DB::table('resume_pkl_bulanans')
            ->whereRaw('DAY(bulan) <> 1')
            ->get(['id', 'siswa_id', 'bulan']);

        foreach ($rows as $row) {
            $normalized = date('Y-m-01', strtotime($row->bulan));

            $conflict = DB::table('resume_pkl_bulanans')
                ->where('siswa_id', $row->siswa_id)
                ->where('bulan', $normalized)
                ->where('id', '<>', $row->id)
                ->exists();

            if (!$conflict) {
                DB::table('resume_pkl_bulanans')
                    ->where('id', $row->id)
                    ->update(['bulan' => $normalized]);
            }
        }
    }

    public function down(): void
    {
        // Normalisasi data tidak dapat dikembalikan.
    }
};
