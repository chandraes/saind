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
        Schema::table('ppn_masukans', function (Blueprint $table) {
            $table->boolean('is_faktur')->default(0)->after('onhold');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ppn_masukans', function (Blueprint $table) {
            $table->dropColumn('is_faktur');
        });
    }
};
