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
        Schema::create('uj_ditahans', function (Blueprint $table) {
            $table->id();
            $table->integer('bulan');
            $table->integer('tahun');
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');

            // Kumulatif total per kendaraan
            $table->bigInteger('total_masuk')->default(0);
            $table->bigInteger('total_keluar')->default(0);
            $table->bigInteger('saldo')->default(0);

            // Unique key diubah: 1 Kendaraan = 1 Baris per Bulan
            $table->unique(['bulan', 'tahun', 'vehicle_id'], 'saldo_uj_unique');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uj_ditahans');
    }
};
