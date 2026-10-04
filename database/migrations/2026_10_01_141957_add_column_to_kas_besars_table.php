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
        Schema::table('kas_besars', function (Blueprint $table) {
            $table->foreignId('aki_ganti_invoice_id')->nullable()->constrained('aki_ganti_invoices')->onDelete('set null');
        });

         Schema::table('kas_vendors', function (Blueprint $table) {
            $table->foreignId('aki_ganti_invoice_id')->nullable()->constrained('aki_ganti_invoices')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kas_besars', function (Blueprint $table) {
            $table->dropForeign(['aki_ganti_invoice_id']);
            $table->dropColumn('aki_ganti_invoice_id');
        });

        Schema::table('kas_vendors', function (Blueprint $table) {
            $table->dropForeign(['aki_ganti_invoice_id']);
            $table->dropColumn('aki_ganti_invoice_id');
        });
    }
};
