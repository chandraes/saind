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
        Schema::table('invoice_tagihans', function (Blueprint $table) {
            $table->bigInteger('penalty_akhir')->default(0)->after('penalty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_tagihans', function (Blueprint $table) {
            $table->dropColumn('penalty_akhir');
        });
    }
};
