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
        Schema::create('rekap_barang_maintenances', function (Blueprint $table) {
            $table->id();
            $table->boolean('jenis_transaksi');
            $table->foreignId('barang_maintenance_id')->nullable()->constrained('barang_maintenances')->onDelete('SET NULL');
            $table->string('nama_barang')->nullable();
            $table->integer('jumlah');
            $table->integer('harga_satuan');
            $table->integer('total');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekap_barang_maintenances');
    }
};
