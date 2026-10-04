<?php

namespace Database\Factories;

use App\Models\FilterOliGantiInvoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<FilterOliGantiInvoice>
 */
class FilterOliGantiInvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'no_invoice' => 'INV-FO-'.Str::ulid(),
            'user_id' => User::factory()->state(['password' => 'password']),
            'pembayaran' => 'dibayar_sendiri',
        ];
    }
}
