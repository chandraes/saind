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
        Schema::create('filter_oli_log_transaksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('filter_oli_log_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaksi_id')->constrained('transaksis')->restrictOnDelete();
            $table->decimal('nilai_ritase', 4, 1);
            $table->unique(['filter_oli_log_id', 'transaksi_id'], 'filter_oli_log_transaction_unique');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filter_oli_log_transaksis');
    }
};
