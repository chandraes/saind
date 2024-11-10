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
        Schema::create('pph_simpans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_bayar_id')->nullable()->constrained('invoice_bayars')->nullOnDelete();
            $table->string('uraian')->nullable();
            $table->bigInteger('nominal')->default(0);
            $table->boolean('onhold')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pph_simpans');
    }
};
