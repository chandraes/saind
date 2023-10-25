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
        Schema::table('kas_vendors', function (Blueprint $table) {
            $table->boolean('void')->default(0)->after('storing');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kas_vendors', function (Blueprint $table) {
            $table->dropColumn('void');
        });
    }
};
