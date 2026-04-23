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
        Schema::create('invoice_additional_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_additional_id')->nullable()->constrained('invoice_additionals')->onDelete('set null');
            $table->foreignId('transaksi_additional_id')->nullable()->constrained('transaksi_additionals')->onDelete('set null');
            $table->foreignId('transaksi_id')->nullable()->constrained('transaksis')->onDelete('set null');
            $table->string('jenis')->comment('kompensasi_jr, penyesuaian_bbm, achievement');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_additional_details');
    }
};
