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
        Schema::table('vendors', function (Blueprint $table) {
            // add nickname column after nama
            $table->string('nickname')->after('nama');
            // change perusahaan column to nullable
            $table->string('perusahaan')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            // drop nickname column
            $table->dropColumn('nickname');
            // change perusahaan column to not nullable
            $table->string('perusahaan')->nullable(false)->change();
        });
    }
};
