<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rute;

class RuteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'nama' => 'BP - IS 36',
                'jarak' => 78.6,
                'uang_jalan' => 1000000,
                'user_id' => 1,
            ],
            [
                'nama' => 'BP - Port',
                'jarak' => 114,
                'uang_jalan' => 1500000,
                'user_id' => 1,
            ],
            [
                'nama' => 'IS 107 - IS 36',
                'jarak' => 71,
                'uang_jalan' => 900000,
                'user_id' => 1,
            ],
            [
                'nama' => 'IS 107 - Port',
                'jarak' => 107,
                'uang_jalan' => 1300000,
                'user_id' => 1,
            ],
            [
                'nama' => 'MIP - 36',
                'jarak' => 97.85,
                'uang_jalan' => 1500000,
                'user_id' => 1,
            ],
            [
                'nama' => 'MIP - Port',
                'jarak' => 133.85,
                'uang_jalan' => 1800000,
                'user_id' => 1,
            ],
            [
                'nama' => 'MIP - 107',
                'jarak' => 26.85,
                'uang_jalan' => 350000,
                'user_id' => 1,
            ],
        ];

        Rute::insert($data);
    }
}
