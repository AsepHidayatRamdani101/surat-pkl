<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tugas_pembekalans', function (Blueprint $table) {
            $table->json('soal_parsed_prompts')->nullable()->after('soal_files');
            $table->timestamp('soal_parsed_at')->nullable()->after('soal_parsed_prompts');
        });
    }

    public function down(): void
    {
        Schema::table('tugas_pembekalans', function (Blueprint $table) {
            $table->dropColumn(['soal_parsed_prompts', 'soal_parsed_at']);
        });
    }
};
