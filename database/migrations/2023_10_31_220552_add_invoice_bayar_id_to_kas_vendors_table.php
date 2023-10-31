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
            $table->foreignId('invoice_bayar_id')->nullable()->constrained('invoice_bayars')->onDelete('set null')->after('vendor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kas_vendors', function (Blueprint $table) {
            $table->dropForeign(['invoice_bayar_id']);
            $table->dropColumn('invoice_bayar_id');
        });
    }
};
