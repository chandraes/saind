<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uj_ditahan_ban_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('uj_ditahan_ban_id')->constrained('uj_ditahan_bans')->cascadeOnDelete();
            $table->foreignId('transaksi_id')->nullable()->constrained('transaksis')->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->enum('jenis', ['masuk', 'keluar']);
            $table->bigInteger('nominal');
            $table->string('keterangan')->nullable();
            $table->string('bank')->nullable();
            $table->string('no_rekening')->nullable();
            $table->string('nama_rekening')->nullable();
            $table->string('file_pdf')->nullable();
            $table->timestamps();
            $table->index(['uj_ditahan_ban_id', 'created_at', 'id'], 'uj_ban_details_chronology_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uj_ditahan_ban_details');
    }
};
