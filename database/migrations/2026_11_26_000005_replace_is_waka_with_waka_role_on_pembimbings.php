<?php

use App\Support\RoleRegistry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembimbings', function (Blueprint $table) {
            $table->string('waka_role', 50)->nullable()->after('jabatan_pembimbing');
        });

        // Pindahkan waka yang sudah aktif: bidangnya diambil dari jabatan.
        foreach (RoleRegistry::JABATAN_WAKA as $jabatan => $role) {
            DB::table('pembimbings')
                ->where('is_waka', true)
                ->where('jabatan_pembimbing', $jabatan)
                ->update(['waka_role' => $role]);
        }

        Schema::table('pembimbings', function (Blueprint $table) {
            $table->dropColumn('is_waka');
        });
    }

    public function down(): void
    {
        Schema::table('pembimbings', function (Blueprint $table) {
            $table->boolean('is_waka')->default(false)->after('jabatan_pembimbing');
        });

        DB::table('pembimbings')->whereNotNull('waka_role')->update(['is_waka' => true]);

        Schema::table('pembimbings', function (Blueprint $table) {
            $table->dropColumn('waka_role');
        });
    }
};
