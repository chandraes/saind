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
        Schema::table('rekap_gaji_details', function (Blueprint $table) {
            $table->string('no_rekening')->nullable();
            $table->string('bank')->nullable();
            $table->string('transfer_ke')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rekap_gaji_details', function (Blueprint $table) {
            $table->dropColumn('no_rekening');
            $table->dropColumn('bank');
            $table->dropColumn('transfer_ke');
        });
    }
};
