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
        Schema::create('aki_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->foreignId('posisi_aki_id')->constrained('posisi_akis')->onDelete('cascade');
            $table->string('merk');
            $table->string('no_seri');
            $table->string('ampere')->nullable();
            $table->integer('kondisi')->default(100);
            $table->integer('kondisi_saat_ganti')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aki_logs');
    }
};
