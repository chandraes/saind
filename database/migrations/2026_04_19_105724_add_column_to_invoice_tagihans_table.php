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
        Schema::table('invoice_tagihans', function (Blueprint $table) {
            $table->bigInteger('kompensasi_jr')->default(0)->after('penalty_akhir');
            $table->bigInteger('penyesuaian_bbm')->default(0)->after('kompensasi_jr');
            $table->bigInteger('achievement')->default(0)->after('penyesuaian_bbm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_tagihans', function (Blueprint $table) {
            $table->dropColumn('kompensasi_jr');
            $table->dropColumn('penyesuaian_bbm');
            $table->dropColumn('achievement');
        });
    }
};
