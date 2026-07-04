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
            if (!Schema::hasColumn('bimbingans', 'materi_tipe')) {
                $table->enum('materi_tipe', ['text', 'pdf', 'video'])->nullable()->after('topik_pembekalan');
            }
            if (!Schema::hasColumn('bimbingans', 'materi_isi')) {
                $table->text('materi_isi')->nullable()->after('materi_tipe');
            }
            if (!Schema::hasColumn('bimbingans', 'materi_file_path')) {
                $table->string('materi_file_path')->nullable()->after('materi_isi');
            }
            if (!Schema::hasColumn('bimbingans', 'materi_video_url')) {
                $table->string('materi_video_url')->nullable()->after('materi_file_path');
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
                'materi_tipe',
                'materi_isi',
                'materi_file_path',
                'materi_video_url',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('bimbingans', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
