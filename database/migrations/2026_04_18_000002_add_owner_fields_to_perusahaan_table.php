<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perusahaan', function (Blueprint $table) {
            $table->string('nama_pemilik_perusahaan')->nullable()->after('nama_perusahaan');
            $table->string('telepon_pemilik_perusahaan')->nullable()->after('nama_pemilik_perusahaan');
        });
    }

    public function down(): void
    {
        Schema::table('perusahaan', function (Blueprint $table) {
            $table->dropColumn(['nama_pemilik_perusahaan', 'telepon_pemilik_perusahaan']);
        });
    }
};
