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
        Schema::create('kategori_filter_oli_mesins', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->unsignedInteger('limit_ritase');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori_filter_oli_mesins');
    }
};
