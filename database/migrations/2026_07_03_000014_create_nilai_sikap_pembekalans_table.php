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
        Schema::create('nilai_sikap_pembekalans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembimbing_id')->constrained('pembimbings')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->date('tanggal_penilaian');
            $table->enum('nilai_sikap', ['sangat_baik', 'baik', 'cukup', 'perlu_bimbingan']);
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['pembimbing_id', 'siswa_id']);
            $table->index('tanggal_penilaian');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_sikap_pembekalans');
    }
};
