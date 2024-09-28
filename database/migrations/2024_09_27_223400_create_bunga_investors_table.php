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
        Schema::create('bunga_investors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kreditor_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('nominal');
            $table->bigInteger('pph')->default(0);
            $table->bigInteger('total');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bunga_investors');
    }
};
