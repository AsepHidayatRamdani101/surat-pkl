<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sertifikat_aspeks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jurusan_id')->constrained('jurusan')->cascadeOnDelete();
            $table->string('nama_aspek');
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });
        Schema::create('sertifikat_penilaians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('sertifikat_aspek_id')->constrained('sertifikat_aspeks')->cascadeOnDelete();
            $table->decimal('nilai', 5, 2)->nullable();
            $table->string('predikat', 30)->nullable();
            $table->timestamps();
            $table->unique(['siswa_id', 'sertifikat_aspek_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sertifikat_penilaians');
        Schema::dropIfExists('sertifikat_aspeks');
    }
};
