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
            $table->dropColumn(['ppn', 'pph']);
        });

        Schema::table('invoice_tagihans', function (Blueprint $table) {
            $table->bigInteger('penalty')->default(0)->after('penyesuaian');
            $table->bigInteger('ppn')->default(0)->after('penalty');
            $table->bigInteger('pph')->default(0)->after('ppn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('invoice_tagihans', function (Blueprint $table) {
            $table->dropColumn(['penalty', 'ppn', 'pph']);
        });

        Schema::table('invoice_tagihans', function (Blueprint $table) {
            $table->bigInteger('ppn')->default(0)->after('customer_id');
            $table->bigInteger('pph')->default(0)->after('ppn');
        });


    }
};
