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
        Schema::table('upah_gendongs', function (Blueprint $table) {
            $table->float('tonase_min')->default(0)->after('nominal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('upah_gendongs', function (Blueprint $table) {
            $table->dropColumn('tonase_min');
        });
    }
};
