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
        Schema::table('kas_bons', function (Blueprint $table) {
            $table->boolean('cicilan')->default(0)->after('karyawan_id');
            $table->bigInteger('total_bayar')->default(0)->after('nominal');
            $table->bigInteger('sisa_kas')->default(0)->after('total_bayar');
            $table->integer('cicil_kali')->nullable()->after('void');
            $table->bigInteger('cicilan_nominal')->nullable()->after('cicil_kali');
            $table->integer('mulai_bulan')->nullable()->after('cicilan_nominal');
            $table->year('mulai_tahun')->nullable()->after('mulai_bulan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kas_bons', function (Blueprint $table) {
            $table->dropColumn('cicilan');
            $table->dropColumn('total_bayar');
            $table->dropColumn('sisa_kas');
            $table->dropColumn('cicil_kali');
            $table->dropColumn('cicilan_nominal');
            $table->dropColumn('mulai_bulan');
            $table->dropColumn('mulai_tahun');
        });
    }
};
