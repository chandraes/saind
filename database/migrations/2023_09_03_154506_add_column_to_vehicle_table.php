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
        Schema::table('vehicles', function (Blueprint $table) {
            // add transfer_ke column after no_kartu_gps
            $table->string('transfer_ke')->after('no_kartu_gps')->nullable();
            $table->string('bank')->after('transfer_ke')->nullable();
            $table->string('no_rekening')->after('bank')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // drop column transfer_ke
            $table->dropColumn('transfer_ke');
            $table->dropColumn('bank');
            $table->dropColumn('no_rekening');
        });
    }
};
