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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->date('tanggal_pajak_stnk')->nullable()->after('gps');
            $table->date('tanggal_kir')->nullable()->after('tanggal_pajak_stnk');
            $table->date('tanggal_kimper')->nullable()->after('tanggal_kir');
            $table->date('tanggal_sim')->nullable()->after('tanggal_kimper');
            $table->boolean('lock_uj')->default(0)->after('tanggal_sim');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('tanggal_pajak_stnk');
            $table->dropColumn('tanggal_kir');
            $table->dropColumn('tanggal_kimper');
            $table->dropColumn('tanggal_sim');
            $table->dropColumn('lock_uj');
        });
    }
};
