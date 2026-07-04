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
        Schema::create('nilai_tugas_pembekalans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jawaban_tugas_siswa_id')->constrained('jawaban_tugas_siswas')->cascadeOnDelete();
            $table->foreignId('pembimbing_id')->constrained('pembimbings')->cascadeOnDelete();
            $table->decimal('nilai', 5, 2);
            $table->text('catatan')->nullable();
            $table->timestamp('dinilai_at')->nullable();
            $table->timestamps();

            $table->unique('jawaban_tugas_siswa_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_tugas_pembekalans');
    }
};
