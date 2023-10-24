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
        Schema::create('rekap_barangs', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->tinyInteger('jenis_transaksi');
            $table->foreignId('barang_id')->nullable()->constrained('barangs')->onDelete('set null');
            $table->string('nama_barang');
            $table->integer('jumlah');
            $table->bigInteger('harga_satuan');
            $table->bigInteger('total');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekap_barangs');
    }
};
