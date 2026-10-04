<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filter_oli_ganti_invoice_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_filter_oli_mesin_id')->constrained('kategori_filter_oli_mesins', indexName: 'filter_oli_details_category_fk')->restrictOnDelete();
            $table->string('merk', 100);
            $table->unsignedTinyInteger('kondisi')->default(100);
            $table->decimal('ritase', 10, 1)->default(0);
            $table->foreignId('filter_oli_ganti_invoice_id')->constrained('filter_oli_ganti_invoices', indexName: 'filter_oli_details_invoice_fk')->restrictOnDelete();
            $table->foreignId('filter_oli_log_id')->nullable()->unique()->constrained()->restrictOnDelete();
            $table->unsignedInteger('limit_ritase');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filter_oli_ganti_invoice_details');
    }
};
