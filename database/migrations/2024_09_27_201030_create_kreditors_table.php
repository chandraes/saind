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
        Schema::create('kreditors', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->float('persen', 5, 2);
            $table->string('npwp');
            $table->string('no_rek');
            $table->string('nama_rek');
            $table->string('bank');
            $table->boolean('apa_pph')->default(0);
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kreditors');
    }
};
