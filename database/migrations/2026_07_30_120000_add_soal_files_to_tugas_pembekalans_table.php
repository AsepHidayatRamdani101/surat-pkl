<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tugas_pembekalans', function (Blueprint $table) {
            if (!Schema::hasColumn('tugas_pembekalans', 'soal_files')) {
                $table->json('soal_files')->nullable()->after('soal_essay');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tugas_pembekalans', function (Blueprint $table) {
            if (Schema::hasColumn('tugas_pembekalans', 'soal_files')) {
                $table->dropColumn('soal_files');
            }
        });
    }
};
