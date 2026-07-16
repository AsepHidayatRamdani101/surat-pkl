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
        Schema::create('pembimbing_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembimbing_id')->constrained('pembimbings')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['pembimbing_id', 'siswa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembimbing_siswa');
    }
};
