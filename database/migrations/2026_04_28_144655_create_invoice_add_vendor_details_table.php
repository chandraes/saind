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
        Schema::create('invoice_add_vendor_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_add_vendor_id')->constrained()->onDelete('cascade');
            $table->foreignId('transaksi_additional_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('transaksi_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_add_vendor_details');
    }
};
