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
        Schema::create('kas_besars', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('uraian', 20);
            $table->string('kode_deposit', 1)->default('D');
            $table->integer('nomor_kode_deposit')->nullable();
            $table->string('kode_kas_kecil', 2)->default('KK');
            $table->integer('nomor_kode_kas_kecil')->nullable();
            $table->string('kode_kas_uang_jalan',3)->default('KUJ');
            $table->integer('nomor_kode_kas_uang_jalan')->nullable();
            $table->foreignId('tipe_transaksi_id')->constrained('tipe_transaksis')->nullable();
            $table->foreignId('jenis_transaksi_id')->constrained('jenis_transaksis');
            $table->bigInteger('nominal_transaksi');
            $table->bigInteger('saldo');
            $table->string('transfer_ke', 15);
            $table->string('bank', 10);
            $table->string('no_rekening');
            // bigInteger table that can store negative value
            $table->bigInteger('modal_investor')->nullable();
            $table->bigInteger('modal_investor_terakhir')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kas_besars');
    }
};
