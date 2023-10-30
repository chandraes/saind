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
        Schema::create('kas_bon_cicilans', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->foreignId('karyawan_id')->constrained('karyawans');
            $table->integer('nominal');
            $table->integer('total_bayar')->default(0);
            $table->integer('sisa_kas');
            $table->integer('cicil_kali');
            $table->integer('cicilan_nominal');
            $table->integer('mulai_bulan');
            $table->integer('mulai_tahun');
            $table->boolean('lunas')->default(0);
            $table->timestamps();
        });

        Schema::table('kas_bons', function (Blueprint $table) {
            $table->dropColumn('cicil_kali');
            $table->dropColumn('cicilan_nominal');
            $table->dropColumn('mulai_bulan');
            $table->dropColumn('mulai_tahun');
            $table->dropColumn('cicilan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kas_bon_cicilans');
        Schema::table('kas_bons', function (Blueprint $table) {
            $table->integer('cicil_kali');
            $table->integer('cicilan_nominal');
            $table->integer('mulai_bulan');
            $table->integer('mulai_tahun');
            $table->boolean('cicilan');
        });
    }
};
