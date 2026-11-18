<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sertifikat_settings', function (Blueprint $table) {
            $table->string('background_front_pembekalan_path')->nullable()->after('background_front_path');
        });
    }

    public function down(): void
    {
        Schema::table('sertifikat_settings', function (Blueprint $table) {
            $table->dropColumn('background_front_pembekalan_path');
        });
    }
};
