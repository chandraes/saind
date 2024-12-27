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
        Schema::table('rekap_ppns', function (Blueprint $table) {
            $table->bigInteger('penyesuaian')->default(0)->after('keluaran_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rekap_ppns', function (Blueprint $table) {
            $table->dropColumn('penyesuaian');
        });
    }
};
