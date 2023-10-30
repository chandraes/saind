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
        Schema::table('vehicles', function (Blueprint $table) {
            // make nopol, no_rangka, and no_mesin to unique
            $table->string('nopol')->unique()->change();
            $table->string('no_rangka')->unique()->change();
            $table->string('no_mesin')->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // remove unique index from nopol, no_rangka, and no_mesin
            $table->dropUnique('vehicles_nopol_unique');
            $table->dropUnique('vehicles_no_rangka_unique');
            $table->dropUnique('vehicles_no_mesin_unique');
        });
    }
};
