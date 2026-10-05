<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filter_oli_ganti_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->restrictOnDelete();
            $table->foreignId('kategori_filter_oli_mesin_id')->constrained('kategori_filter_oli_mesins')->restrictOnDelete();
            $table->string('merk', 100);
            $table->unsignedTinyInteger('kondisi')->default(100);
            $table->decimal('ritase', 10, 1)->default(0);
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->unique(['user_id', 'kategori_filter_oli_mesin_id'], 'filter_oli_cart_user_category_unique');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filter_oli_ganti_carts');
    }
};
