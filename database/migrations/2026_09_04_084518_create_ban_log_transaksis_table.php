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
        Schema::create('ban_log_transaksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ban_log_id')->constrained('ban_logs')->onDelete('cascade');
            $table->foreignId('transaksi_id')->nullable()->constrained('transaksis')->onDelete('set null'); // Sesuaikan 'transaksis'

            // Gunakan decimal untuk mendukung angka 0.5
            $table->decimal('nilai_ritase', 4, 1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ban_log_transaksis');
    }
};
