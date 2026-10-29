<?php

namespace Database\Factories;

use App\Models\UjDitahanBanDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<UjDitahanBanDetail> */
class UjDitahanBanDetailFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return ['jenis' => 'masuk', 'nominal' => 10000, 'keterangan' => 'UJ Ditahan Ban'];
    }
}
