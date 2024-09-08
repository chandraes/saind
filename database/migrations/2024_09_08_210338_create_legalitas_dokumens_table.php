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
        Schema::create('legalitas_dokumens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legalitas_kategori_id')->constrained()->cascadeOnDelete();
            $table->string('nama');
            $table->text('file');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legalitas_dokumens');
    }
};
