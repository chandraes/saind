<?php

namespace Database\Seeders;

use App\Models\KategoriFilterOliMesin;
use Illuminate\Database\Seeder;

class KategoriFilterOliMesinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            'Filter Solar: JAF20' => 10,
            'Filter Solar: JAF40' => 20,
            'Filter Solar: JAE51' => 30,
            'Filter Oli' => 34,
            'Ganti Oli' => 17,
        ] as $nama => $limitRitase) {
            KategoriFilterOliMesin::firstOrCreate(
                ['nama' => $nama],
                ['limit_ritase' => $limitRitase],
            );
        }
    }
}
