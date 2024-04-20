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
        Schema::table('konfigurasis', function (Blueprint $table) {
            $table->integer('waktu_aktif')->default(1)->after('nama');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('konfigurasis', function (Blueprint $table) {
            $table->dropColumn('waktu_aktif');
        });
    }
};
