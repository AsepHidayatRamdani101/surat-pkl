<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tempat_pkl', function (Blueprint $table) {
            if (!Schema::hasColumn('tempat_pkl', 'nip_pemilik_instansi')) {
                $table->string('nip_pemilik_instansi')->nullable()->after('nama_pemilik_instansi');
            }
            if (!Schema::hasColumn('tempat_pkl', 'nip_pembimbing_instansi')) {
                $table->string('nip_pembimbing_instansi')->nullable()->after('nama_pembimbing_instansi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tempat_pkl', function (Blueprint $table) {
            if (Schema::hasColumn('tempat_pkl', 'nip_pembimbing_instansi')) {
                $table->dropColumn('nip_pembimbing_instansi');
            }
            if (Schema::hasColumn('tempat_pkl', 'nip_pemilik_instansi')) {
                $table->dropColumn('nip_pemilik_instansi');
            }
        });
    }
};
