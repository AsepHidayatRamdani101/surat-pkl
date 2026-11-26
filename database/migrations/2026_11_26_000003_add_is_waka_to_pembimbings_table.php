<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembimbings', function (Blueprint $table) {
            $table->boolean('is_waka')->default(false)->after('jabatan_pembimbing');
        });
    }

    public function down(): void
    {
        Schema::table('pembimbings', function (Blueprint $table) {
            $table->dropColumn('is_waka');
        });
    }
};
