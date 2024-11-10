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
        Schema::create('ppn_masukans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_bayar_id')->nullable()->constrained('invoice_bayars')->nullOnDelete();
            $table->string('uraian')->nullable();
            $table->bigInteger('nominal')->default(0);
            $table->bigInteger('nominal_faktur')->default(0);
            $table->string('no_faktur')->nullable();
            $table->boolean('onhold')->default(1);
            $table->boolean('keranjang')->default(0);
            $table->boolean('selesai')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppn_masukans');
    }
};
