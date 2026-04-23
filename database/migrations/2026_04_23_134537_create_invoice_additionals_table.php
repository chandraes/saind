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
        Schema::create('invoice_additionals', function (Blueprint $table) {
            $table->id();
            $table->integer('status')->default(0)->comment("0: active, 1: selesai");
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
        Schema::dropIfExists('invoice_additionals');
    }
};
