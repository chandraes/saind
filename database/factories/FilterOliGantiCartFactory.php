<?php

namespace Database\Factories;

use App\Models\FilterOliGantiCart;
use App\Models\KategoriFilterOliMesin;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FilterOliGantiCart>
 */
class FilterOliGantiCartFactory extends Factory
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
            'user_id' => User::factory()->state(['password' => 'password']),
        ];
    }
}
