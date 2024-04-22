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
        Schema::table('upah_gendongs', function (Blueprint $table) {
            $table->date('tanggal_masuk_driver')->nullable()->after('nama_driver');
            $table->date('tanggal_masuk_pengurus')->nullable()->after('nama_pengurus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('upah_gendongs', function (Blueprint $table) {
            $table->dropColumn('tanggal_masuk_driver');
            $table->dropColumn('tanggal_masuk_pengurus');
        });
    }
};
