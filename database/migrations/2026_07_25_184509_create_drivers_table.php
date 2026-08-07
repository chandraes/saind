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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('no_sim')->unique();
            $table->date('masa_berlaku_sim');
            $table->string('no_hp');
            $table->string('no_rek');
            $table->string('nama_rek');
            $table->string('bank');
            $table->text('alamat');
            $table->string('foto_sim')->nullable();
            $table->enum('status', ['aktif', 'non_aktif'])->default('aktif');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
