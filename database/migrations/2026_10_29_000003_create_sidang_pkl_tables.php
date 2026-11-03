<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ruangan_sidangs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_ruangan');
            $table->string('lokasi')->nullable();
            $table->unsignedInteger('kapasitas')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('kriteria_sidangs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kriteria');
            $table->unsignedInteger('bobot')->default(0); // persen
            $table->text('deskripsi')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('rubrik_kriteria_sidangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kriteria_sidang_id')->constrained('kriteria_sidangs')->cascadeOnDelete();
            $table->string('label', 100); // contoh: Sangat Baik
            $table->unsignedInteger('nilai_min');
            $table->unsignedInteger('nilai_max');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::create('jadwal_sidangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('pembimbing_id')->nullable()->constrained('pembimbings')->nullOnDelete();
            $table->foreignId('ruangan_sidang_id')->nullable()->constrained('ruangan_sidangs')->nullOnDelete();
            $table->string('jenis', 15); // bimbingan | sidang
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->index(['jenis', 'tanggal']);
        });

        Schema::create('laporan_sidangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->string('judul');
            $table->string('file_path');
            $table->string('tipe_file', 10); // pdf | ppt
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->unique('siswa_id');
        });

        Schema::create('nilai_sidangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('kriteria_sidang_id')->constrained('kriteria_sidangs')->cascadeOnDelete();
            $table->foreignId('pembimbing_id')->nullable()->constrained('pembimbings')->nullOnDelete();
            $table->decimal('nilai', 5, 2);
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->unique(['siswa_id', 'kriteria_sidang_id']);
        });

        Schema::create('sertifikat_sidangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->string('nomor_sertifikat')->nullable();
            $table->decimal('nilai_akhir', 5, 2);
            $table->string('predikat', 50);
            $table->timestamp('generated_at');
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique('siswa_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sertifikat_sidangs');
        Schema::dropIfExists('nilai_sidangs');
        Schema::dropIfExists('laporan_sidangs');
        Schema::dropIfExists('jadwal_sidangs');
        Schema::dropIfExists('rubrik_kriteria_sidangs');
        Schema::dropIfExists('kriteria_sidangs');
        Schema::dropIfExists('ruangan_sidangs');
    }
};
