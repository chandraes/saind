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
            ['kode' => 'nota-muat', 'nama' => 'Limit Nota Muat'],
        ];

        foreach ($data as $key => $value) {
            Konfigurasi::create($value);
        }
    }
}
