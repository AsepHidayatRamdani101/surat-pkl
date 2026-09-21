<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi_controls', function (Blueprint $table) {
            $table->date('periode_dimulai_pada')->nullable()->after('is_active');
            $table->unsignedSmallInteger('interval_hari')->default(30)->after('periode_dimulai_pada');
        });

        Schema::create('absensi_pkls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('pembimbing_id')->constrained('pembimbings')->cascadeOnDelete();
            $table->date('periode_mulai');
            $table->date('periode_selesai');
            $table->string('status', 20);
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->unique(['siswa_id', 'periode_mulai']);
        });

        Schema::create('resume_pkl_bulanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->date('bulan');
            $table->string('judul', 150);
            $table->text('isi_resume');
            $table->string('file_path')->nullable();
            $table->string('status', 20)->default('dikirim');
            $table->timestamps();
            $table->unique(['siswa_id', 'bulan']);
        });

        Schema::create('upload_absensi_jurnal_pkls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('judul', 150);
            $table->text('keterangan')->nullable();
            $table->string('file_path')->nullable();
            $table->string('status', 20)->default('dikirim');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upload_absensi_jurnal_pkls');
        Schema::dropIfExists('resume_pkl_bulanans');
        Schema::dropIfExists('absensi_pkls');
        Schema::table('absensi_controls', function (Blueprint $table) {
            $table->dropColumn(['periode_dimulai_pada', 'interval_hari']);
        });
    }
};