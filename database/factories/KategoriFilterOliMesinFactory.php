<?php

namespace Database\Factories;

use App\Models\KategoriFilterOliMesin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KategoriFilterOliMesin>
 */
class KategoriFilterOliMesinFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->unique()->words(3, true),
            'limit_ritase' => fake()->numberBetween(1, 100),
        ];
    }
}
