<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['kas_besars', 'kas_vendors'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->foreignId('filter_oli_ganti_invoice_id')->nullable()->constrained('filter_oli_ganti_invoices')->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (['kas_besars', 'kas_vendors'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropConstrainedForeignId('filter_oli_ganti_invoice_id');
            });
        }
    }
};
