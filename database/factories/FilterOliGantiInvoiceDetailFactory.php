<?php

namespace Database\Factories;

use App\Models\FilterOliGantiInvoice;
use App\Models\FilterOliGantiInvoiceDetail;
use App\Models\KategoriFilterOliMesin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FilterOliGantiInvoiceDetail>
 */
class FilterOliGantiInvoiceDetailFactory extends Factory
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
            'limit_ritase' => 10,
            'filter_oli_ganti_invoice_id' => FilterOliGantiInvoice::factory(),
        ];
    }
}
