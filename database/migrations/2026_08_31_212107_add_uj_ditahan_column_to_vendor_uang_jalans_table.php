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
        Schema::table('vendor_uang_jalans', function (Blueprint $table) {
            $table->bigInteger('uj_ditahan')->default(0)->after('hk_uang_jalan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_uang_jalans', function (Blueprint $table) {
            $table->dropColumn('uj_ditahan');
        });
    }
};
