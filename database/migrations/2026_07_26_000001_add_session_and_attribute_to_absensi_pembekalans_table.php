<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('absensi_pembekalans', function (Blueprint $table) {
            if (!Schema::hasColumn('absensi_pembekalans', 'sesi_absensi')) {
                $table->enum('sesi_absensi', ['datang', 'pulang'])
                    ->default('datang')
                    ->after('tanggal_absensi');
            }

            if (!Schema::hasColumn('absensi_pembekalans', 'atribut_lengkap')) {
                $table->boolean('atribut_lengkap')
                    ->nullable()
                    ->after('status');
            }
        });

        if (!$this->indexExists('absensi_pembekalans', 'absensi_pembekalans_siswa_id_index')) {
            Schema::table('absensi_pembekalans', function (Blueprint $table) {
                $table->index('siswa_id', 'absensi_pembekalans_siswa_id_index');
            });
        }

        if ($this->indexExists('absensi_pembekalans', 'absensi_pembekalans_siswa_id_tanggal_absensi_unique')) {
            Schema::table('absensi_pembekalans', function (Blueprint $table) {
                $table->dropUnique('absensi_pembekalans_siswa_id_tanggal_absensi_unique');
            });
        }

        if (!$this->indexExists('absensi_pembekalans', 'absensi_pembekalans_siswa_tanggal_sesi_unique')) {
            Schema::table('absensi_pembekalans', function (Blueprint $table) {
                $table->unique(['siswa_id', 'tanggal_absensi', 'sesi_absensi'], 'absensi_pembekalans_siswa_tanggal_sesi_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($this->indexExists('absensi_pembekalans', 'absensi_pembekalans_siswa_tanggal_sesi_unique')) {
            Schema::table('absensi_pembekalans', function (Blueprint $table) {
                $table->dropUnique('absensi_pembekalans_siswa_tanggal_sesi_unique');
            });
        }

        if (!$this->indexExists('absensi_pembekalans', 'absensi_pembekalans_siswa_id_tanggal_absensi_unique')) {
            Schema::table('absensi_pembekalans', function (Blueprint $table) {
                $table->unique(['siswa_id', 'tanggal_absensi'], 'absensi_pembekalans_siswa_id_tanggal_absensi_unique');
            });
        }

        Schema::table('absensi_pembekalans', function (Blueprint $table) {
            if (Schema::hasColumn('absensi_pembekalans', 'sesi_absensi')) {
                $table->dropColumn('sesi_absensi');
            }

            if (Schema::hasColumn('absensi_pembekalans', 'atribut_lengkap')) {
                $table->dropColumn('atribut_lengkap');
            }
        });

        if ($this->indexExists('absensi_pembekalans', 'absensi_pembekalans_siswa_id_index')) {
            Schema::table('absensi_pembekalans', function (Blueprint $table) {
                $table->dropIndex('absensi_pembekalans_siswa_id_index');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $result = DB::select('SHOW INDEX FROM `' . $table . '` WHERE Key_name = ?', [$indexName]);

        return !empty($result);
    }
};