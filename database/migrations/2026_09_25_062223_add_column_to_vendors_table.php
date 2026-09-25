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
            $table->integer('jatuh_tempo_kompensasi_jr')->default(14)->after('jatuh_tempo_hari');
            $table->integer('jatuh_tempo_penyesuaian_bbm')->default(14)->after('jatuh_tempo_kompensasi_jr');
            $table->integer('jatuh_tempo_achievement')->default(14)->after('jatuh_tempo_penyesuaian_bbm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('jatuh_tempo_kompensasi_jr');
            $table->dropColumn('jatuh_tempo_penyesuaian_bbm');
            $table->dropColumn('jatuh_tempo_achievement');
        });
    }
};
