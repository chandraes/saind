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
            $table->index('tanggal');
        });

        Schema::table('kas_kecils', function (Blueprint $table) {
            $table->index('tanggal');
        });

        Schema::table('kas_uang_jalans', function (Blueprint $table) {
            $table->index('tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kas_besars', function (Blueprint $table) {
            $table->dropIndex(['tanggal']);
        });

        Schema::table('kas_kecils', function (Blueprint $table) {
            $table->dropIndex(['tanggal']);
        });

        Schema::table('kas_uang_jalans', function (Blueprint $table) {
            $table->dropIndex(['tanggal']);
        });
    }
};
