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
        Schema::table('pembimbings', function (Blueprint $table) {
            $table->enum('jenis_guru', ['adaptif_normatif', 'guru_produktif'])
                ->default('adaptif_normatif')
                ->after('jumlah_jam');
            $table->unsignedBigInteger('jurusan_id')->nullable()->after('jenis_guru');
            $table->foreign('jurusan_id')->references('id')->on('jurusan')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembimbings', function (Blueprint $table) {
            $table->dropForeign(['jurusan_id']);
            $table->dropColumn(['jenis_guru', 'jurusan_id']);
        });
    }
};
