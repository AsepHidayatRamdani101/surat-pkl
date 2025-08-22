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
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->string('nama_siswa');
            $table->string('nis')->unique();
            $table->unsignedBigInteger('kelas_id');
            $table->string('jk')->nullable();
            $table->string('nama_ortu')->nullable();
            $table->string('alamat_ortu')->nullable();
            $table->string('no_hp_ortu')->nullable();
            $table->string('no_hp_siswa')->nullable();
            $table->string('foto')->nullable();
            $table->string('status')->default('belum_terdaftar');
            $table->timestamps();

            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};
