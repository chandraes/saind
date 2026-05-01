<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
    {
        // 1. Buat Kolomnya dulu
        Schema::table('transaksi_additionals', function (Blueprint $table) {
            $table->foreignId('customer_id')
                  ->after('id')
                  ->nullable()
                  ->constrained('customers')
                  ->onDelete('set null');
        });

        // 2. Isi datanya (Backfill) menggunakan SQL Join (Jauh lebih cepat & aman)
        // Cara ini tidak memakan memori RAM server karena diproses langsung oleh Database
        DB::table('transaksi_additionals')
            ->join('transaksis', 'transaksi_additionals.transaksi_id', '=', 'transaksis.id')
            ->join('kas_uang_jalans', 'transaksis.kas_uang_jalan_id', '=', 'kas_uang_jalans.id')
            ->update([
                'transaksi_additionals.customer_id' => DB::raw('kas_uang_jalans.customer_id')
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_additionals', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
        });
    }
};
