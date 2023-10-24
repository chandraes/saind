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
        Schema::table('transaksis', function (Blueprint $table) {
            $table->index(['status', 'bayar', 'void']);
            $table->index(['status','bonus', 'void']);
            $table->index(['kas_uang_jalan_id', 'status', 'bayar', 'void']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropIndex(['status', 'bayar', 'void']);
            $table->dropIndex(['status','bonus', 'void']);
            $table->dropIndex(['kas_uang_jalan_id', 'status', 'bayar', 'void']);
        });
    }
};
