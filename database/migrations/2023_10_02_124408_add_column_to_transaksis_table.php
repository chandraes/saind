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
        Schema::table('transaksis', function (Blueprint $table) {
            // drop column timbangan
            $table->dropColumn('timbangan');
            // add column nota_bongkar and timbangan bongkar
            $table->string('nota_bongkar')->nullable()->after('tonase');
            $table->string('timbangan_bongkar')->nullable()->after('nota_bongkar');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            // drop column nota_bongkar and timbangan bongkar
            $table->dropColumn('nota_bongkar');
            $table->dropColumn('timbangan_bongkar');
            // add column timbangan
            $table->string('timbangan')->nullable();
        });
    }
};
