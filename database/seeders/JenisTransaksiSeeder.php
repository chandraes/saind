<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\JenisTransaksi;

class JenisTransaksiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'nama' => 'Masuk',
            ],
            [
                'nama' => 'Keluar',
            ],
        ];

        foreach ($data as $key => $value) {
            JenisTransaksi::create($value);
        }
    }
}
