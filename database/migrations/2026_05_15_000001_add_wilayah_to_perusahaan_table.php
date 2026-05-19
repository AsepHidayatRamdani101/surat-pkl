<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perusahaan', function (Blueprint $table) {
            $table->string('provinsi_id')->nullable()->after('alamat');
            $table->string('kabupaten_kota_id')->nullable()->after('provinsi_id');
            $table->string('kecamatan_id')->nullable()->after('kabupaten_kota_id');
            $table->string('desa_id')->nullable()->after('kecamatan_id');
        });
    }

    public function down(): void
    {
        Schema::table('perusahaan', function (Blueprint $table) {
            $table->dropColumn(['provinsi_id', 'kabupaten_kota_id', 'kecamatan_id', 'desa_id']);
        });
    }
};