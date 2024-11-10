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
        Schema::create('rekap_ppns', function (Blueprint $table) {
            $table->id();
            $table->string('uraian');
            $table->bigInteger('masukan_id')->nullable()->unique();
            $table->bigInteger('keluaran_id')->nullable()->unique();
            $table->boolean('jenis')->comment('0: keluaran, 1: masukan');
            $table->bigInteger('nominal');
            $table->bigInteger('saldo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekap_ppns');
    }
};
