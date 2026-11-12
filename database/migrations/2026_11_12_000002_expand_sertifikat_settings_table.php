<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sertifikat_settings', function (Blueprint $table) {
            $table->string('background_front_path')->nullable()->after('background_path');
            $table->string('background_back_path')->nullable()->after('background_front_path');
            $table->string('custom_font_path')->nullable()->after('font_family');
            $table->string('custom_font_name')->nullable()->after('custom_font_path');
        });
        DB::table('sertifikat_settings')->whereNotNull('background_path')->update(['background_front_path' => DB::raw('background_path')]);
    }

    public function down(): void
    {
        Schema::table('sertifikat_settings', function (Blueprint $table) {
            $table->dropColumn(['background_front_path', 'background_back_path', 'custom_font_path', 'custom_font_name']);
        });
    }
};
