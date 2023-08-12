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
        Schema::create('karyawans', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nickname');
            $table->foreignId('jabatan_id')->constrained('jabatans')->onDelete('cascade');
            $table->string('nik');
            $table->string('npwp');
            $table->string('bpjs_tk');
            $table->string('bpjs_kesehatan');
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->text('alamat');
            $table->string('no_hp');
            $table->string('no_wa');
            $table->string('bank');
            $table->string('no_rekening');
            $table->string('nama_rekening');
            $table->date('mulai_bekerja');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->string('foto_ktp')->nullable();
            $table->string('foto_diri')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawans');
    }
};
