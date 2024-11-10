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
        Schema::create('rekap_keluaran_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('keluaran_id');
            $table->foreignId('ppn_keluaran_id')->constrained('ppn_keluarans')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekap_keluaran_details');
    }
};
