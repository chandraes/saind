<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TipeTransaksi;

class TipeTransaksiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'nama' => 'Deposit',
            ],
            [
                'nama' => 'Kas Kecil',
            ],
            [
                'nama' => 'Kas Uang Jalan',
            ],
            [
                'nama' => 'Tagihan',
            ],
        ];

        foreach ($data as $key => $value) {
            TipeTransaksi::create($value);
        }
    }
}
