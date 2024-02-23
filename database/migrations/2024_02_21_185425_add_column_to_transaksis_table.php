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
            $table->float('gross_muat')->default(0)->after('nota_muat');
            $table->float('tarra_muat')->default(0)->after('gross_muat');
            $table->float('gross_bongkar')->default(0)->after('nota_bongkar');
            $table->float('tarra_bongkar')->default(0)->after('gross_bongkar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropColumn('gross_muat');
            $table->dropColumn('tarra_muat');
            $table->dropColumn('gross_bongkar');
            $table->dropColumn('tarra_bongkar');
        });
    }
};
