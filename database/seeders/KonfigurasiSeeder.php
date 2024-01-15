<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Konfigurasi;

class KonfigurasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['kode' => 'nota-muat', 'nama' => 'Nota Muat'],
            ['kode' => 'nota-bongkar', 'nama' => 'Nota Bongkar'],
        ];

        foreach ($data as $key => $value) {
            Konfigurasi::create($value);
        }
    }
}
