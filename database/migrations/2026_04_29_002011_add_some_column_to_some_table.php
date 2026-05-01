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
        Schema::table('kas_vendors', function (Blueprint $table) {
            $table->foreignId('invoice_add_vendor_id')->nullable()->after('tanggal')->constrained()->onDelete('set null');
        });

         Schema::table('ppn_masukans', function (Blueprint $table) {
            $table->foreignId('invoice_add_vendor_id')->nullable()->after('invoice_bayar_id')->constrained()->onDelete('set null');
        });

         Schema::table('pph_simpans', function (Blueprint $table) {
            $table->foreignId('invoice_add_vendor_id')->nullable()->after('invoice_bayar_id')->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kas_vendors', function (Blueprint $table) {
            $table->dropForeign(['invoice_add_vendor_id']);
            $table->dropColumn(['invoice_add_vendor_id']);
        });

         Schema::table('ppn_masukans', function (Blueprint $table) {
            $table->dropForeign(['invoice_add_vendor_id']);
            $table->dropColumn(['invoice_add_vendor_id']);
        });

           Schema::table('pph_simpans', function (Blueprint $table) {
            $table->dropForeign(['invoice_add_vendor_id']);
            $table->dropColumn(['invoice_add_vendor_id']);
        });
    }
};
