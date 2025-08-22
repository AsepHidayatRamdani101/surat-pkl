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
        Schema::create('tempat_pkl', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedBigInteger('perusahaan_id');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('surat_kesediaan_path')->nullable();
            $table->string('surat_izin_path')->nullable();
            $table->string('nama_pembimbing')->nullable();
            $table->string('jabatan_pembimbing')->nullable();
            $table->string('no_hp_pembimbing')->nullable();
            $table->string('nip_pembimbing')->nullable();
            $table->string('tugas_siswa')->nullable();
            $table->unsignedBigInteger('created_by'); // id dari kepala program
            $table->timestamps();

            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
            $table->foreign('perusahaan_id')->references('id')->on('perusahaan')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('pembimbing_id')->references('id')->on('pembimbings')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tempat_pkl');
    }
};
