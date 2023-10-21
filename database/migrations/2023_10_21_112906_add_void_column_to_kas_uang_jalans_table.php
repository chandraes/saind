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
        Schema::table('kas_uang_jalans', function (Blueprint $table) {
            $table->boolean('void')->default(0)->after('nomor_uang_jalan');
            $table->string('kode_void')->nullable()->after('void');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kas_uang_jalans', function (Blueprint $table) {
            $table->dropColumn('void');
            $table->dropColumn('kode_void');
        });
    }
};
