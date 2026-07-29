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
        DB::statement("ALTER TABLE absensi_pembekalans MODIFY COLUMN status ENUM('hadir','izin','alpa','sakit','terlambat') NOT NULL DEFAULT 'hadir'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Map statuses not available in the old enum before rollback.
        DB::table('absensi_pembekalans')
            ->where('status', 'sakit')
            ->update(['status' => 'izin']);

        DB::table('absensi_pembekalans')
            ->where('status', 'terlambat')
            ->update(['status' => 'hadir']);

        DB::statement("ALTER TABLE absensi_pembekalans MODIFY COLUMN status ENUM('hadir','izin','alpa') NOT NULL DEFAULT 'hadir'");
    }
};
