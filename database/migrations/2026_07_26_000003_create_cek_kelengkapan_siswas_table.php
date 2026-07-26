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
        Schema::create('cek_kelengkapan_siswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembimbing_id')->constrained('pembimbings')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->date('tanggal_cek');
            $table->enum('sesi_cek', ['datang', 'pulang'])->default('datang');
            $table->json('item_checks');
            $table->boolean('is_lengkap')->default(false);
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['siswa_id', 'tanggal_cek', 'sesi_cek'], 'cek_kelengkapan_siswas_siswa_tanggal_sesi_unique');
            $table->index(['pembimbing_id', 'tanggal_cek']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cek_kelengkapan_siswas');
    }
};