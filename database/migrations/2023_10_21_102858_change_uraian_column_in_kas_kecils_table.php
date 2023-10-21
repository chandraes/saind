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
        Schema::table('kas_kecils', function (Blueprint $table) {
            // change uraian column to nullable
            $table->string('uraian')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kas_kecils', function (Blueprint $table) {
            // change uraian column to not nullable
            $table->string('uraian')->nullable(false)->change();
        });
    }
};
