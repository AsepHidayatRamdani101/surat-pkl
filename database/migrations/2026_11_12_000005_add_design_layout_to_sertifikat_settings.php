<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sertifikat_settings', function (Blueprint $table) {
            $table->json('design_layout')->nullable()->after('custom_font_name');
        });
    }

    public function down(): void
    {
        Schema::table('sertifikat_settings', fn (Blueprint $table) => $table->dropColumn('design_layout'));
    }
};
