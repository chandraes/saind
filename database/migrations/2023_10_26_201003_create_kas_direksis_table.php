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
        Schema::create('kas_direksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('direksi_id')->constrained('direksis');
            $table->date('tanggal');
            $table->string('uraian');
            $table->bigInteger('total_kas');
            $table->bigInteger('total_bayar');
            $table->bigInteger('sisa_kas');
            $table->boolean('lunas')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kas_direksis');
    }
};
