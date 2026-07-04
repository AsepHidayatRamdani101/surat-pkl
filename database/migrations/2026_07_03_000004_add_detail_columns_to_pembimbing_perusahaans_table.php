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
        Schema::table('pembimbing_perusahaans', function (Blueprint $table) {
            if (!Schema::hasColumn('pembimbing_perusahaans', 'NIP')) {
                $table->string('NIP')->nullable()->after('perusahaan_id');
            }
            if (!Schema::hasColumn('pembimbing_perusahaans', 'jabatan')) {
                $table->string('jabatan')->nullable()->after('NIP');
            }
            if (!Schema::hasColumn('pembimbing_perusahaans', 'jenis_kelamin')) {
                $table->enum('jenis_kelamin', ['Laki-Laki', 'Perempuan'])->nullable()->after('jabatan');
            }
            if (!Schema::hasColumn('pembimbing_perusahaans', 'nohp')) {
                $table->string('nohp')->nullable()->after('jenis_kelamin');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembimbing_perusahaans', function (Blueprint $table) {
            $dropColumns = [];

            if (Schema::hasColumn('pembimbing_perusahaans', 'NIP')) {
                $dropColumns[] = 'NIP';
            }
            if (Schema::hasColumn('pembimbing_perusahaans', 'jabatan')) {
                $dropColumns[] = 'jabatan';
            }
            if (Schema::hasColumn('pembimbing_perusahaans', 'jenis_kelamin')) {
                $dropColumns[] = 'jenis_kelamin';
            }
            if (Schema::hasColumn('pembimbing_perusahaans', 'nohp')) {
                $dropColumns[] = 'nohp';
            }

            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
