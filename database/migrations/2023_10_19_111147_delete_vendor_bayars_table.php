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
        Schema::dropIfExists('vendor_bayars');
        // add new column to customer tagihan
        Schema::table('customer_tagihans', function (Blueprint $table) {
            $table->integer('opname')->nullable()->after('harga_tagihan');
            $table->integer('titipan')->nullable()->after('opname');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('vendor_bayars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('rute_id')->constrained('rutes')->onDelete('cascade');
            $table->integer('hk_opname');
            $table->integer('hk_titipan');
            $table->timestamps();
        });
        // drop new column to customer tagihan
        Schema::table('customer_tagihans', function (Blueprint $table) {
            $table->dropColumn('opname');
            $table->dropColumn('titipan');
        });
    }
};
