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
        Schema::create('materis', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_materi');
            $table->string('topik');
            $table->enum('tipe_materi', ['text', 'pdf', 'video'])->default('text');
            $table->text('isi_materi')->nullable();
            $table->string('file_pdf_path')->nullable();
            $table->string('video_url')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index('tanggal_materi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materis');
    }
};
