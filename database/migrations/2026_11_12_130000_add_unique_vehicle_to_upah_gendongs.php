<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Satu data upah gendong per kendaraan, ditegakkan database agar dua request
 * bersamaan (web atau mobile) tidak membuat data ganda.
 */
return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('upah_gendongs')->select('vehicle_id')->groupBy('vehicle_id')
            ->havingRaw('COUNT(*) > 1')->pluck('vehicle_id');
        // Jangan memilih atau menghapus data otomatis; pemilik data yang memutuskan.
        if ($duplicates->isNotEmpty()) {
            throw new RuntimeException('upah_gendongs memiliki data ganda untuk vehicle_id: '
                .$duplicates->implode(', ').'. Tentukan record yang dipertahankan sebelum menjalankan migrasi ini.');
        }

        Schema::table('upah_gendongs', function (Blueprint $table): void {
            $table->unique('vehicle_id');
        });
    }

    public function down(): void
    {
        Schema::table('upah_gendongs', function (Blueprint $table): void {
            $table->dropUnique(['vehicle_id']);
        });
    }
};
