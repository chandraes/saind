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
        Schema::create('rekap_pph_vendor_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekap_pph_vendor_id')->constrained('rekap_pph_vendors')->onDelete('cascade');
            $table->foreignId('pph_simpan_id')->constrained('pph_simpans')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekap_pph_vendor_details');
    }
};
