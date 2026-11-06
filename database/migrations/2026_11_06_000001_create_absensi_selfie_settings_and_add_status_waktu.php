<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi_selfie_settings', function (Blueprint $table) {
            $table->id();
            $table->time('jam_datang_mulai')->nullable();
            $table->time('jam_datang_tepat_waktu')->nullable();
            $table->time('jam_datang_selesai')->nullable();
            $table->time('jam_pulang_mulai')->nullable();
            $table->time('jam_pulang_selesai')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::table('absensi_selfie_pkls', function (Blueprint $table) {
            $table->string('status_waktu', 20)->nullable()->after('persentase_lokasi'); // tepat_waktu | terlambat
        });
    }

    public function down(): void
    {
        Schema::table('absensi_selfie_pkls', function (Blueprint $table) {
            $table->dropColumn('status_waktu');
        });
        Schema::dropIfExists('absensi_selfie_settings');
    }
};
