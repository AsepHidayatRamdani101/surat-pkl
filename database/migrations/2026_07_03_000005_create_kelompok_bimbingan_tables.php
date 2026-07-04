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
        Schema::create('kelompok_bimbingan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kelompok');
            $table->foreignId('pembimbing_id')->constrained('pembimbings')->cascadeOnDelete();
            $table->enum('metode', ['otomatis', 'manual'])->default('manual');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('kelompok_bimbingan_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelompok_bimbingan_id')->constrained('kelompok_bimbingan')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->timestamps();

            $table->unique('siswa_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelompok_bimbingan_siswa');
        Schema::dropIfExists('kelompok_bimbingan');
    }
};
