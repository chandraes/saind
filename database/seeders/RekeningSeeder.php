<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rekening;

class RekeningSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'untuk' => 'kas-besar',
                'nama_bank' => 'BCA',
                'nomor_rekening' => '1234567890',
                'nama_rekening' => 'PT. SAIND',
            ],
            [
                'untuk' => 'kas-kecil',
                'nama_bank' => 'BCA',
                'nomor_rekening' => '1234567890',
                'nama_rekening' => 'PT. SAIND - KAS KECIL',
            ],
            [
                'untuk' => 'kas-uang-jalan',
                'nama_bank' => 'BCA',
                'nomor_rekening' => '1234567890',
                'nama_rekening' => 'PT. SAIND - KAS UANG JALAN',
            ],
        ];

        foreach ($data as $key => $value) {
            Rekening::create($value);
        }
    }
}
