<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perusahaan', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('desa_id');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('maps_url', 500)->nullable()->after('longitude');
        });

        Schema::create('absensi_selfie_pkls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('perusahaan_id')->nullable()->constrained('perusahaan')->nullOnDelete();
            $table->date('tanggal');
            $table->string('jenis', 10); // datang | pulang
            $table->time('waktu');
            $table->string('foto_path');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->unsignedInteger('akurasi_gps')->nullable(); // akurasi perangkat (meter)
            $table->unsignedInteger('jarak_meter')->nullable();
            $table->unsignedTinyInteger('persentase_lokasi')->nullable(); // 0-100, mendekati perusahaan
            $table->timestamps();
            $table->unique(['siswa_id', 'tanggal', 'jenis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_selfie_pkls');
        Schema::table('perusahaan', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'maps_url']);
        });
    }
};
