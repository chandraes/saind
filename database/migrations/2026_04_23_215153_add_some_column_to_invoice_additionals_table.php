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
        Schema::table('invoice_additionals', function (Blueprint $table) {
            $table->foreignId('customer_id')
                  ->after('id')
                  ->nullable()
                  ->constrained('customers')
                  ->onDelete('set null');
            $table->string('jenis')->after('customer_id')->nullable()->comment('kompensasi_jr, penyesuaian_bbm, achievement');

            $table->index(['customer_id', 'jenis'], 'idx_customer_id_jenis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_additionals', function (Blueprint $table) {
            $table->dropIndex('idx_customer_id_jenis');
            $table->dropColumn(['customer_id', 'jenis']);
        });
    }
};
