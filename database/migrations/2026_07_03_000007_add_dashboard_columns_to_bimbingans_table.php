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
        Schema::table('bimbingans', function (Blueprint $table) {
            if (!Schema::hasColumn('bimbingans', 'tanggal_bimbingan')) {
                $table->date('tanggal_bimbingan')->nullable()->after('siswa_id');
            }
            if (!Schema::hasColumn('bimbingans', 'topik_pembekalan')) {
                $table->string('topik_pembekalan')->nullable()->after('tanggal_bimbingan');
            }
            if (!Schema::hasColumn('bimbingans', 'status_absensi')) {
                $table->enum('status_absensi', ['hadir', 'izin', 'alpa'])->default('hadir')->after('topik_pembekalan');
            }
            if (!Schema::hasColumn('bimbingans', 'tugas')) {
                $table->text('tugas')->nullable()->after('status_absensi');
            }
            if (!Schema::hasColumn('bimbingans', 'tugas_siswa')) {
                $table->text('tugas_siswa')->nullable()->after('tugas');
            }
            if (!Schema::hasColumn('bimbingans', 'nilai_tugas')) {
                $table->decimal('nilai_tugas', 5, 2)->nullable()->after('tugas_siswa');
            }
            if (!Schema::hasColumn('bimbingans', 'penilaian_sikap')) {
                $table->enum('penilaian_sikap', ['sangat_baik', 'baik', 'cukup', 'perlu_bimbingan'])->nullable()->after('nilai_tugas');
            }
            if (!Schema::hasColumn('bimbingans', 'catatan')) {
                $table->text('catatan')->nullable()->after('penilaian_sikap');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bimbingans', function (Blueprint $table) {
            $columns = [
                'tanggal_bimbingan',
                'topik_pembekalan',
                'status_absensi',
                'tugas',
                'tugas_siswa',
                'nilai_tugas',
                'penilaian_sikap',
                'catatan',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('bimbingans', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
