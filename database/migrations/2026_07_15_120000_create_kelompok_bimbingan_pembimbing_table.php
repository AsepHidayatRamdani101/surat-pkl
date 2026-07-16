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
        Schema::create('kelompok_bimbingan_pembimbing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelompok_bimbingan_id')->constrained('kelompok_bimbingan')->cascadeOnDelete();
            $table->foreignId('pembimbing_id')->constrained('pembimbings')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['kelompok_bimbingan_id', 'pembimbing_id'], 'kbp_unique_kelompok_pembimbing');
        });

        DB::table('kelompok_bimbingan')
            ->whereNotNull('pembimbing_id')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                $now = now();
                $inserts = [];

                foreach ($rows as $row) {
                    $inserts[] = [
                        'kelompok_bimbingan_id' => $row->id,
                        'pembimbing_id' => $row->pembimbing_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if (!empty($inserts)) {
                    DB::table('kelompok_bimbingan_pembimbing')->upsert(
                        $inserts,
                        ['kelompok_bimbingan_id', 'pembimbing_id'],
                        ['updated_at']
                    );
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelompok_bimbingan_pembimbing');
    }
};
