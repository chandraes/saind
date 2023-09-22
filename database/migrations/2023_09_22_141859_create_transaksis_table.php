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
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kas_uang_jalan_id')->constrained('kas_uang_jalans');
            $table->date('tanggal_muat')->nullable();
            $table->date('tanggal_bongkar')->nullable();
            $table->string('nota_muat')->nullable();
            $table->float('tonase')->nullable();
            $table->float('timbangan')->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
