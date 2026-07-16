<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembinaan_pembekalan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');
            $table->foreignId('pembimbing_id')->constrained('pembimbings')->onDelete('cascade');
            $table->date('tanggal_formulir');
            $table->string('waktu_formulir', 20)->nullable();
            $table->string('tempat', 191)->nullable();
            $table->text('kronologi')->nullable();
            $table->text('komitmen_peserta')->nullable();
            $table->text('catatan_guru')->nullable();
            $table->json('jenis_pembinaan')->nullable();
            $table->string('jenis_pembinaan_lainnya', 255)->nullable();
            $table->json('tindakan_pembinaan')->nullable();
            $table->string('tindakan_pembinaan_lainnya', 255)->nullable();
            $table->json('hasil_pembinaan')->nullable();
            $table->string('tingkat_pembinaan', 32)->nullable();
            $table->timestamps();

            $table->index(['tanggal_formulir', 'pembimbing_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembinaan_pembekalan');
    }
};
