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
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('is_kompensasi_jr')->default(0)->after('gt_bongkar');
            $table->boolean('is_penyesuaian_bbm')->default(0)->after('is_kompensasi_jr');
            $table->boolean('is_achievement')->default(0)->after('is_penyesuaian_bbm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('is_kompensasi_jr');
            $table->dropColumn('is_penyesuaian_bbm');
            $table->dropColumn('is_achievement');
        });
    }
};
