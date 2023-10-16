<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->bigInteger('nominal_tagihan')->after('status')->nullable();
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->enum('pembayaran', ['opname', 'titipan'])->nullable();
        });

        Schema::table('vendor_bayars', function (Blueprint $table) {
            $table->dropColumn('pembayaran');
        });

        // truncate vendor_bayars
        DB::table('vendor_bayars')->truncate();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropColumn('nominal_tagihan');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('pembayaran');
        });

        Schema::table('vendor_bayars', function (Blueprint $table) {
            $table->enum('pembayaran', ['opname', 'titipan'])->nullable();
        });
    }
};
