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
        Schema::create('uj_ditahan_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uj_ditahan_id')->constrained('uj_ditahans')->onDelete('cascade');
            $table->foreignId('transaksi_id')->nullable()->constrained('transaksis')->onDelete('set null');

            // PENAMBAHAN: Rekam jejak sopir pada saat pemotongan/pencairan
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->onDelete('set null');

            $table->enum('jenis', ['masuk', 'keluar'])->comment('masuk = potongan dari UJ, keluar = pencairan');
            $table->bigInteger('nominal');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uj_ditahan_details');
    }
};
