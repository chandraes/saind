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
        Schema::create('aki_ganti_invoice_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aki_ganti_invoice_id')->constrained('aki_ganti_invoices')->onDelete('cascade');
            $table->foreignId('posisi_aki_id')->constrained('posisi_akis')->onDelete('cascade');
            $table->foreignId('aki_log_id')->nullable()->constrained('aki_logs')->onDelete('set null');

            // Data Aki Lama
            $table->string('merk_lama')->nullable();
            $table->string('no_seri_lama')->nullable();
            $table->string('keterangan_lama')->nullable();

            // Data Aki Baru
            $table->string('merk');
            $table->string('no_seri');
            $table->integer('kondisi')->default(100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aki_ganti_invoice_details');
    }
};
