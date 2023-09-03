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
        Schema::create('kas_kecils', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('kode',2)->default('KK');
            $table->bigInteger('nomor_kode_kas_kecil')->nullable();
            $table->string('uraian', 20);
            $table->foreignId('jenis_transaksi_id')->constrained('jenis_transaksis');
            $table->bigInteger('nominal_transaksi');
            $table->bigInteger('saldo');
            $table->string('transfer_ke', 15);
            $table->string('bank', 10)->nullable();
            $table->string('no_rekening')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kas_kecils');
    }
};
