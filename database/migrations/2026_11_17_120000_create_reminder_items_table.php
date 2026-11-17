<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rekap Dashboard Pengingat. Diisi command reminders:refresh (terjadwal); halaman
 * pengingat hanya membaca tabel ini, tidak memindai transaksi saat dibuka.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminder_items', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 30);
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('subject_id');
            // Titik waktu pemicu (bongkar terakhir, UJ keluar, jatuh tempo, tanggal alpa); durasi & ambang dihitung saat dibaca.
            $table->dateTime('started_at')->nullable();
            // Filter & oli: nolam yang diaktifkan admin (pembatasan_filter_oli) untuk role yang memerlukannya.
            $table->boolean('activated')->default(true);
            $table->json('payload');
            $table->timestamps();
            $table->index(['type', 'vendor_id', 'started_at']);
        });
        Schema::create('reminder_refreshes', function (Blueprint $table): void {
            $table->string('type', 30)->primary();
            $table->dateTime('refreshed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_refreshes');
        Schema::dropIfExists('reminder_items');
    }
};
