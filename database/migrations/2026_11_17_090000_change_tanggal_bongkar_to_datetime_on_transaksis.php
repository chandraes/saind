<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tanggal bongkar kini menyimpan jam (waktu nota bongkar disimpan) untuk dashboard
 * pengingat. Data lama tetap ada dan otomatis berjam 00:00:00.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksis', function (Blueprint $table): void {
            $table->dateTime('tanggal_bongkar')->nullable()->change();
            $table->index(['status', 'void', 'tanggal_bongkar'], 'transaksis_status_void_tanggal_bongkar_index');
        });
    }

    /** Kembali ke DATE: jam bongkar yang sudah terekam akan terpotong. */
    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table): void {
            $table->dropIndex('transaksis_status_void_tanggal_bongkar_index');
            $table->date('tanggal_bongkar')->nullable()->change();
        });
    }
};
