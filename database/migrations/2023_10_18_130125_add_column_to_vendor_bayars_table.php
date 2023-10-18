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
        Schema::table('vendor_bayars', function (Blueprint $table) {
            // drop harga_kesepakatan column
            $table->dropColumn('harga_kesepakatan');
            // add hk_opname column
            $table->integer('hk_opname')->default(0);
            // add hk_titipan column
            $table->integer('hk_titipan')->default(0);
            $table->foreignId('rute_id')->nullable()->constrained('rutes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_bayars', function (Blueprint $table) {
            // drop hk_opname column
            $table->dropColumn('hk_opname');
            // drop hk_titipan column
            $table->dropColumn('hk_titipan');
            // add harga_kesepakatan column
            $table->integer('harga_kesepakatan')->default(0);
            $table->dropForeign(['rute_id']);
            $table->dropColumn('rute_id');
        });
    }
};
