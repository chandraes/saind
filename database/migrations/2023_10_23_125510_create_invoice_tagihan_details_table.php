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
        Schema::create('invoice_tagihan_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_tagihan_id')->constrained('invoice_tagihans')->cascadeOnDelete();
            // foreigh to transaksis
            $table->foreignId('transaksi_id')->constrained('transaksis')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_tagihan_details');
    }
};
