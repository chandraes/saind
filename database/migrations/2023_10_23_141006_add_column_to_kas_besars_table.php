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
        Schema::table('kas_besars', function (Blueprint $table) {
            $table->string('kode_tagihan', 1)->default('T')->after('nomor_kode_kas_uang_jalan');
            $table->integer('nomor_kode_tagihan')->nullable()->after('kode_tagihan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kas_besars', function (Blueprint $table) {
            $table->dropColumn('kode_tagihan');
            $table->dropColumn('nomor_kode_tagihan');
        });
    }
};
