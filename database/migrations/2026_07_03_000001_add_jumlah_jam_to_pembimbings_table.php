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
            $table->unsignedInteger('jumlah_jam')->default(0)->after('no_hp_pembimbing');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembimbings', function (Blueprint $table) {
            $table->dropColumn('jumlah_jam');
        });
    }
};
