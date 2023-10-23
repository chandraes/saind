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
        Schema::create('kas_vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('uraian');
            $table->bigInteger('pinjaman')->default(0);
            $table->bigInteger('bayar')->default(0);
            $table->bigInteger('sisa')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kas_vendors');
    }
};
