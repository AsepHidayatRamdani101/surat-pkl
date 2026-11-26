<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_sidangs', function (Blueprint $table) {
            $table->boolean('terkunci')->default(false)->after('jenis');
            $table->unsignedSmallInteger('jeda_menit')->default(0)->after('terkunci');
            $table->index(['jenis', 'terkunci']);
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_sidangs', function (Blueprint $table) {
            $table->dropIndex(['jenis', 'terkunci']);
            $table->dropColumn(['terkunci', 'jeda_menit']);
        });
    }
};
