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
        Schema::table('rekap_gajis', function (Blueprint $table) {
            $table->dropColumn('tanggal');
            $table->integer('bulan')->after('id');
            $table->year('tahun')->after('bulan');

        });

        // drop rekap_gajis table if exists
        Schema::dropIfExists('rekap_gaji_details');

        Schema::create('rekap_gaji_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekap_gaji_id')->constrained('rekap_gajis')->onDelete('cascade');
            $table->string('nik');
            $table->string('nama');
            $table->string('jabatan');
            $table->integer('gaji_pokok');
            $table->integer('tunjangan_jabatan');
            $table->integer('tunjangan_keluarga');
            $table->integer('bpjs_tk');
            $table->integer('bpjs_k');
            $table->integer('potongan_bpjs_tk');
            $table->integer('potongan_bpjs_kesehatan');
            $table->integer('pendapatan_kotor');
            $table->integer('pendapatan_bersih');
            $table->integer('kasbon')->default(0);
            $table->integer('sisa_gaji_dibayar');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rekap_gajis', function (Blueprint $table) {
            $table->dropColumn('bulan');
            $table->dropColumn('tahun');
            $table->date('tanggal')->after('id');
        });
    }
};
