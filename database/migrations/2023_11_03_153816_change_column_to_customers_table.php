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
            // drop column harga tagihan
            $table->dropColumn('harga_tagihan');
            $table->dropColumn('harga_titipan');
            $table->dropColumn('harga_opname');
            $table->boolean('csr')->default(false);
            $table->string('csr_transfer_ke')->nullable();
            $table->string('csr_bank')->nullable();
            $table->string('csr_no_rekening')->nullable();
        });
        Schema::table('transaksis', function (Blueprint $table) {
            // drop column harga tagihan
            $table->boolean('csr')->default(false)->after('bonus');
            $table->integer('nominal_csr')->default(0)->after('nominal_bonus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->integer('harga_tagihan')->default(0);
            $table->integer('harga_titipan')->default(0);
            $table->integer('harga_opname')->default(0);
            $table->dropColumn('csr');
            $table->dropColumn('csr_transfer_ke');
            $table->dropColumn('csr_bank');
            $table->dropColumn('csr_no_rekening');
        });
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropColumn('csr');
            $table->dropColumn('nominal_csr');
        });
    }
};
