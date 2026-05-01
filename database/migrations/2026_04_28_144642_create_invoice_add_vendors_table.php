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
        Schema::create('invoice_add_vendors', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('periode');
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->onDelete('set null');
            $table->string('jenis')->comment('kompensasi_jr, penyesuaian_bbm, achievement');
            $table->index(['vendor_id', 'jenis'], 'idx_vendor_jenis');
            $table->index(['vendor_id', 'jenis', 'status'], 'idx_ven_jenis_stat');
            $table->integer('status')->default(0)->comment("0: keranjang, 1: active");
            $table->integer('dpp');
            $table->bigInteger('nominal');
            $table->boolean('is_finished')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_add_vendors');
    }
};
