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
        Schema::create('dokumen_data', function (Blueprint $table) {
            $table->id();
            $table->integer('jenis_dokumen')->comment('1 kontrak-tambang, 2 kontrak-vendor, 3 sph, 4 dll');
            $table->string('nama');
            $table->string('file');
            $table->date('tanggal_expired')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen_data');
    }
};
