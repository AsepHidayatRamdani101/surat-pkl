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
        Schema::table('kelompok_bimbingan', function (Blueprint $table) {
            $table->dropForeign(['pembimbing_id']);
        });

        DB::statement('ALTER TABLE kelompok_bimbingan MODIFY pembimbing_id BIGINT UNSIGNED NULL');

        Schema::table('kelompok_bimbingan', function (Blueprint $table) {
            $table->foreign('pembimbing_id')
                ->references('id')
                ->on('pembimbings')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelompok_bimbingan', function (Blueprint $table) {
            $table->dropForeign(['pembimbing_id']);
        });

        $fallbackPembimbingId = DB::table('pembimbings')->orderBy('id')->value('id');
        if ($fallbackPembimbingId) {
            DB::table('kelompok_bimbingan')
                ->whereNull('pembimbing_id')
                ->update(['pembimbing_id' => (int) $fallbackPembimbingId]);
        } else {
            DB::table('kelompok_bimbingan')
                ->whereNull('pembimbing_id')
                ->delete();
        }

        DB::statement('ALTER TABLE kelompok_bimbingan MODIFY pembimbing_id BIGINT UNSIGNED NOT NULL');

        Schema::table('kelompok_bimbingan', function (Blueprint $table) {
            $table->foreign('pembimbing_id')
                ->references('id')
                ->on('pembimbings')
                ->cascadeOnDelete();
        });
    }
};
