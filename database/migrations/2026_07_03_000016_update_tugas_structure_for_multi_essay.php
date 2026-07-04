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
            $table->json('soal_essay')->nullable()->after('judul_tugas');

            if (Schema::hasColumn('tugas_pembekalans', 'pembimbing_id')) {
                $table->dropConstrainedForeignId('pembimbing_id');
            }

            if (Schema::hasColumn('tugas_pembekalans', 'siswa_id')) {
                $table->dropConstrainedForeignId('siswa_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tugas_pembekalans', function (Blueprint $table) {
            if (Schema::hasColumn('tugas_pembekalans', 'soal_essay')) {
                $table->dropColumn('soal_essay');
            }

            if (!Schema::hasColumn('tugas_pembekalans', 'pembimbing_id')) {
                $table->foreignId('pembimbing_id')->after('materi_id')->constrained('pembimbings')->cascadeOnDelete();
            }

            if (!Schema::hasColumn('tugas_pembekalans', 'siswa_id')) {
                $table->foreignId('siswa_id')->after('pembimbing_id')->constrained('siswa')->cascadeOnDelete();
            }
        });
    }
};
