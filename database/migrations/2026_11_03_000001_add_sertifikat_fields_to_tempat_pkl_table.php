<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tempat_pkl', function (Blueprint $table) {
            if (!Schema::hasColumn('tempat_pkl', 'nama_pemilik_instansi')) {
                $table->string('nama_pemilik_instansi')->nullable()->after('tugas_siswa');
            }
            if (!Schema::hasColumn('tempat_pkl', 'nama_pembimbing_instansi')) {
                $table->string('nama_pembimbing_instansi')->nullable()->after('nama_pemilik_instansi');
            }
            if (!Schema::hasColumn('tempat_pkl', 'pemilik_sama_pembimbing')) {
                $table->boolean('pemilik_sama_pembimbing')->default(false)->after('nama_pembimbing_instansi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tempat_pkl', function (Blueprint $table) {
            if (Schema::hasColumn('tempat_pkl', 'pemilik_sama_pembimbing')) {
                $table->dropColumn('pemilik_sama_pembimbing');
            }
            if (Schema::hasColumn('tempat_pkl', 'nama_pembimbing_instansi')) {
                $table->dropColumn('nama_pembimbing_instansi');
            }
            if (Schema::hasColumn('tempat_pkl', 'nama_pemilik_instansi')) {
                $table->dropColumn('nama_pemilik_instansi');
            }
        });
    }
};
