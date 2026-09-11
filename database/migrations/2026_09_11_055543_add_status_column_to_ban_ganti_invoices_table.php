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
        Schema::table('ban_ganti_invoices', function (Blueprint $table) {
            $table->string('status')->default(\App\Models\BanGantiInvoice::STATUS_PENDING)->after('tanggal');

            // Tambahan kolom untuk menyimpan data sementara Kas Besar
            $table->string('nama_bank')->nullable();
            $table->string('nomor_rekening')->nullable();
            $table->string('nama_rekening')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ban_ganti_invoices', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('nama_bank');
            $table->dropColumn('nomor_rekening');
            $table->dropColumn('nama_rekening');
        });
    }
};
