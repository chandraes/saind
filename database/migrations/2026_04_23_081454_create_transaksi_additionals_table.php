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
        Schema::create('transaksi_additionals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_id')->nullable()->constrained('transaksis')->onDelete('set null');
            $table->string('jenis')->comment('kompensasi_jr, penyesuaian_bbm, achievement');
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->onDelete('set null');
            $table->foreignId('rute_id')->nullable()->constrained('rutes')->onDelete('set null');
            $table->decimal('jarak');
            $table->index(['jenis','rute_id'], 'jenis_rute_index');
            $table->index(['jenis','vendor_id'], 'jenis_vendor_index');
            $table->unique(['transaksi_id', 'jenis'], 'transaksi_jenis_unique');
            $table->index('jenis', 'jenis_index');
            $table->integer('status')->default(0)->comment("0: checklist, 1: keranjang, 2: selesai, 3: cutoff");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_additionals');
    }
};
