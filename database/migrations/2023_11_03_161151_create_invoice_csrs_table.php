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
        Schema::create('invoice_csrs', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->index('tanggal');
            $table->string('periode');
            $table->integer('no_invoice');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->integer('total_csr');
            $table->boolean('lunas')->default(false);
            $table->index('lunas');
            $table->unique(['customer_id', 'no_invoice']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_csrs');
    }
};
