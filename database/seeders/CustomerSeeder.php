<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $data = [
            [
                'nama' => 'PT. MUSTIKA INDAH PERMAI',
                'singkatan' => 'MIP',
                'contact_person' => '0812378214',
                'harga_opname' => 1100,
                'harga_titipan' => 1050,
                'created_by' => 1,
            ],
            [
                'nama' => 'PT. BANJARSARI PRIBUMI',
                'singkatan' => 'BP',
                'contact_person' => '0812378214123',
                'harga_opname' => 1120,
                'harga_titipan' => 1100,
                'created_by' => 1,
            ],
        ];

        Customer::insert($data);
    }
}
