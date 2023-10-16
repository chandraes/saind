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
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('tanggal_muat')->default(true);
            $table->boolean('nota_muat')->default(true);
            $table->boolean('tonase')->default(true);
            $table->boolean('tanggal_bongkar')->default(true);
            $table->boolean('selisih')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('tanggal_muat');
            $table->dropColumn('nota_muat');
            $table->dropColumn('tonase');
            $table->dropColumn('tanggal_bongkar');
            $table->dropColumn('selisih');
        });
    }
};
