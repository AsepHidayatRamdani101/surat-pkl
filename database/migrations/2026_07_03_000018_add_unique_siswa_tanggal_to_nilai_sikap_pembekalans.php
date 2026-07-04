<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Keep latest record for each siswa+tanggal pair, remove older duplicates.
        $duplicateGroups = DB::table('nilai_sikap_pembekalans')
            ->select('siswa_id', 'tanggal_penilaian', DB::raw('MAX(id) as keep_id'), DB::raw('COUNT(*) as total'))
            ->groupBy('siswa_id', 'tanggal_penilaian')
            ->having('total', '>', 1)
            ->get();

        foreach ($duplicateGroups as $group) {
            DB::table('nilai_sikap_pembekalans')
                ->where('siswa_id', $group->siswa_id)
                ->whereDate('tanggal_penilaian', $group->tanggal_penilaian)
                ->where('id', '!=', $group->keep_id)
                ->delete();
        }

        Schema::table('nilai_sikap_pembekalans', function (Blueprint $table) {
            $table->unique(['siswa_id', 'tanggal_penilaian'], 'nilai_sikap_siswa_tanggal_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nilai_sikap_pembekalans', function (Blueprint $table) {
            $table->dropUnique('nilai_sikap_siswa_tanggal_unique');
        });
    }
};
