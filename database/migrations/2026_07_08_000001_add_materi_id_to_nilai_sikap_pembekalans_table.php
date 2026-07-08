<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nilai_sikap_pembekalans', function (Blueprint $table) {
            $table->foreignId('materi_id')
                ->nullable()
                ->after('siswa_id')
                ->constrained('materis')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('nilai_sikap_pembekalans', function (Blueprint $table) {
            $table->dropForeign(['materi_id']);
            $table->dropColumn('materi_id');
        });
    }
};
