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
        Schema::table('rekenings', function (Blueprint $table) {
            $table->enum('untuk', ['kas-besar','kas-kecil','kas-uang-jalan', 'withdraw', 'mekanik'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rekenings', function (Blueprint $table) {
            $table->enum('untuk', ['kas-besar','kas-kecil','kas-uang-jalan', 'mekanik'])->change();
        });
    }
};
