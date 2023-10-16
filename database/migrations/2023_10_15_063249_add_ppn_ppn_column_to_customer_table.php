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
            $table->boolean('ppn')->default(true);
            $table->boolean('pph')->default(true);
            $table->tinyInteger('tagihan_dari')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('ppn');
            $table->dropColumn('pph');
            $table->dropColumn('tagihan_dari');
        });
    }
};
