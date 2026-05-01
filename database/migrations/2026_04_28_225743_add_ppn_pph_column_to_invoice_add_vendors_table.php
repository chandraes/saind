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
        Schema::table('invoice_add_vendors', function (Blueprint $table) {
            $table->bigInteger('ppn')->after('nominal')->default(0);
            $table->bigInteger('pph')->after('ppn')->default(0);
            $table->bigInteger('total')->after('pph')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_add_vendors', function (Blueprint $table) {
            $table->dropColumn(['ppn','pph','total']);
        });
    }
};
