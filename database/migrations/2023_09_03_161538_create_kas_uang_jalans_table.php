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
        Schema::create('kas_uang_jalans', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('nama_vendor');
            $table->integer('nomor_lambung');
            $table->string('kode_kas_uang_jalan', 3)->default('KUJ');
            $table->integer('nomor_kas_uang_jalan')->nullable();
            $table->string('kode_uang_jalan', 2)->default('UJ');
            $table->integer('nomor_uang_jalan')->nullable();
            $table->foreignId('jenis_transaksi_id')->constrained('jenis_transaksis');
            $table->bigInteger('nominal_transaksi');
            $table->bigInteger('saldo');
            $table->string('tambang')->nullable();
            $table->string('rute')->nullable();
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
        Schema::dropIfExists('kas_uang_jalans');
    }
};
