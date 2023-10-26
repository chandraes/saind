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
        Schema::create('rekap_gaji_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekap_gaji_id')->constrained('rekap_gajis')->cascadeOnDelete();
            $table->foreignId('karyawan_id')->constrained('karyawans')->cascadeOnDelete();
            $table->bigInteger('gaji_pokok');
            $table->bigInteger('tunjangan');
            $table->bigInteger('potongan');
            $table->bigInteger('total');
            $table->bigInteger('kas_bon')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekap_gaji_details');
    }
};
