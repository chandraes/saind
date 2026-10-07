<?php

namespace Database\Factories;

use App\Models\FilterOliLog;
use App\Models\KategoriFilterOliMesin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FilterOliLog>
 */
class FilterOliLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kategori_filter_oli_mesin_id' => KategoriFilterOliMesin::factory(),
            'merk' => fake()->company(),
            'kondisi' => 100,
            'ritase' => 0,
        ];
    }
}
