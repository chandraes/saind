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
        Schema::create('invoice_bonuses', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            // index tanggal
            $table->index('tanggal');
            $table->string('periode');
            $table->bigInteger('no_invoice');
            $table->foreignId('sponsor_id')->constrained('sponsors')->cascadeOnDelete();
            // unique combination of customer_id and no_invoice
            $table->unique(['sponsor_id', 'no_invoice']);
            $table->bigInteger('total_bonus');
            $table->bigInteger('total_bayar')->default(0);
            $table->bigInteger('sisa_bonus')->default(0);
            $table->boolean('lunas')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_bonuses');
    }
};
