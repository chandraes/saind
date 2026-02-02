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
        Schema::table('karyawans', function (Blueprint $table) {
            $table->boolean('status_menikah')->default(0)->after('tunjangan_keluarga');
            $table->integer('jumlah_anak')->default(0)->after('status_menikah');
        });

        Schema::table('direksis', function (Blueprint $table) {
            $table->boolean('status_menikah')->default(0)->after('tunjangan_keluarga');
            $table->integer('jumlah_anak')->default(0)->after('status_menikah');
        });

         Schema::table('rekap_gaji_details', function (Blueprint $table) {
            $table->integer('pph')->default(0)->after('pendapatan_kotor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $table->dropColumn('status_menikah');
            $table->dropColumn('jumlah_anak');
        });

        Schema::table('direksis', function (Blueprint $table) {
            $table->dropColumn('status_menikah');
            $table->dropColumn('jumlah_anak');
        });

        Schema::table('rekap_gaji_details', function (Blueprint $table) {
            $table->dropColumn('pph');
        });
    }
};
