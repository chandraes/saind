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
        Schema::table('invoice_additional_details', function (Blueprint $table) {
            $table->unique(['transaksi_id', 'jenis'], 'unique_transaksi_id_jenis');
        });

        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_additional_details', function (Blueprint $table) {
            $table->dropUnique('unique_transaksi_id_jenis');
        });




    }
};
