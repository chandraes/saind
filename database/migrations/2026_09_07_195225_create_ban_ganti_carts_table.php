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
        Schema::create('ban_ganti_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->foreignId('posisi_ban_id')->constrained('posisi_bans')->onDelete('cascade');
            $table->string('sumber_ban', 20)->default('baru');
            $table->string('merk', 100);
            $table->string('no_seri', 100);
            $table->integer('kondisi')->default(100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ban_ganti_carts');
    }
};
