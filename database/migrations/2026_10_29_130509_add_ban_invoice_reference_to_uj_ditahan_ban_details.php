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
        Schema::table('uj_ditahan_ban_details', function (Blueprint $table): void {
            $table->foreignId('ban_ganti_invoice_id')->nullable()->constrained('ban_ganti_invoices')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('uj_ditahan_ban_details', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('ban_ganti_invoice_id');
        });
    }
};
