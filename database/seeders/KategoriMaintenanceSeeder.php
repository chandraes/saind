<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KategoriMaintenanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['nama' => 'Cuci Unit'],
            ['nama' => 'Fat Gemuk'],
            ['nama' => 'Ganti Oli'],
            ['nama' => 'Filter Oli'],
            ['nama' => 'Cuci AC'],
            ['nama' => 'Karet Dingdong'],
            ['nama' => 'Ganti Per'],
            ['nama' => 'Filter JAF20'],
            ['nama' => 'Filter JAF40'],
            ['nama' => 'Filter JAE51'],
            ['nama' => 'Ganti Aki'],
            ['nama' => 'Ban Luar'],
            ['nama' => 'Ban Dalam'],
        ];

        foreach ($data as $d) {
            \App\Models\KategoriBarangMaintenance::create($d);
        }
    }
}
