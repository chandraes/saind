<?php

namespace Database\Factories;

use App\Models\UjDitahanBan;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<UjDitahanBan> */
class UjDitahanBanFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return ['bulan' => now()->month, 'tahun' => now()->year, 'total_masuk' => 0, 'total_keluar' => 0, 'saldo' => 0];
    }
}
