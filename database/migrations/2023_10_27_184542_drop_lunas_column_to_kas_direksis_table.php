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
        Schema::table('kas_direksis', function (Blueprint $table) {
            $table->dropColumn('lunas');
            // make table total_bayar default 0
            $table->bigInteger('total_bayar')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kas_direksis', function (Blueprint $table) {
            $table->boolean('lunas')->default(0)->after('sisa_kas');

        });
    }
};
