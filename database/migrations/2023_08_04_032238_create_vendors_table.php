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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('jabatan');
            $table->string('perusahaan');
            $table->string('npwp');
            $table->string('alamat');
            $table->string('no_hp');
            $table->string('email');
            $table->string('bank');
            $table->string('no_rekening');
            $table->string('nama_rekening');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->index('status');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
