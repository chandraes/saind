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
        Schema::table('ban_ganti_invoice_details', function (Blueprint $table) {
            $table->integer('ritase')->default(0)->after('kondisi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ban_ganti_invoice_details', function (Blueprint $table) {
            $table->dropColumn('ritase');
        });
    }
};
