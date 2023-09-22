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
        Schema::table('kas_uang_jalans', function (Blueprint $table) {
            // remove column tambang and rute
            $table->dropColumn('tambang');
            $table->dropColumn('rute');
            $table->dropColumn('nama_vendor');
            $table->dropColumn('nomor_lambung');
            $table->foreignId('vendor_id')->after('tanggal')->nullable()->constrained('vendors');
            $table->foreignId('vehicle_id')->after('vendor_id')->nullable()->constrained('vehicles');
            $table->foreignId('customer_id')->after('saldo')->nullable()->constrained('customers');
            $table->foreignId('rute_id')->after('customer_id')->nullable()->constrained('rutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kas_uang_jalans', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['rute_id']);
            $table->dropForeign(['vendor_id']);
            $table->dropForeign(['vehicle_id']);
            $table->dropColumn('customer_id');
            $table->dropColumn('rute_id');
            $table->dropColumn('vendor_id');
            $table->dropColumn('vehicle_id');
            $table->string('nama_vendor')->nullable();
            $table->string('nomor_lambung')->nullable();
            $table->string('tambang')->nullable();
            $table->string('rute')->nullable();
        });
    }
};
