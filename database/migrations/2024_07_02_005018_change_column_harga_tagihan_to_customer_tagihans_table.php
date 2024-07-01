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
        Schema::table('customer_tagihans', function (Blueprint $table) {
            $table->float('harga_tagihan', 15, 2)->change();
            // opname
            $table->float('opname', 15, 2)->nullable()->change();
            $table->float('titipan', 15, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_tagihans', function (Blueprint $table) {
            $table->integer('harga_tagihan')->change();
            // opname
            $table->integer('opname')->nullable()->change();
            $table->integer('titipan')->nullable()->change();
        });
    }
};
