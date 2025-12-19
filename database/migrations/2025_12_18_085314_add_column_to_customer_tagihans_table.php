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
        Schema::table('customer_tagihans', function (Blueprint $table) {
            $table->integer('titipan_khusus')->default(0)->after('titipan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_tagihans', function (Blueprint $table) {
            $table->dropColumn('titipan_khusus');
        });
    }
};
