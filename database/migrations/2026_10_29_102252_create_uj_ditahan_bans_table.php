<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uj_ditahan_bans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->unsignedTinyInteger('bulan');
            $table->unsignedSmallInteger('tahun');
            $table->bigInteger('total_masuk')->default(0);
            $table->bigInteger('total_keluar')->default(0);
            $table->bigInteger('saldo')->default(0);
            $table->timestamps();
            $table->unique(['vehicle_id', 'tahun', 'bulan'], 'uj_ban_vehicle_period_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uj_ditahan_bans');
    }
};
