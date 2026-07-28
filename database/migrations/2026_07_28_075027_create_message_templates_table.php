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
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->string('nama_template');
            $table->text('isi_template');
            $table->enum('tipe_template', ['informasi', 'pengumuman', 'undangan', 'lainnya'])->default('informasi');
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('message_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->string('nomor_penerima');
            $table->text('isi_pesan');
            $table->enum('tipe_pengiriman', ['personal', 'masal'])->default('personal');
            $table->enum('status_pengiriman', ['pending', 'terkirim', 'gagal'])->default('pending');
            $table->text('response_fonnte')->nullable();
            $table->integer('dikirim_oleh')->nullable();
            $table->timestamps();
            $table->foreign('template_id')->references('id')->on('message_templates')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_logs');
        Schema::dropIfExists('message_templates');
    }
};
