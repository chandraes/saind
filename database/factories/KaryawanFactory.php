<?php

namespace Database\Factories;

use App\Models\Jabatan;
use Illuminate\Database\Eloquent\Factories\Factory;

class KaryawanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama' => fake()->name(), 'nickname' => fake()->firstName(),
            'jabatan_id' => fn (): int => Jabatan::create(['nama' => 'Staff'])->id,
            'kode' => '180620-', 'nomor' => fake()->unique()->numberBetween(1, 100000),
            'nik' => fake()->numerify('################'), 'npwp' => '-', 'bpjs_tk' => '-', 'bpjs_kesehatan' => '-',
            'tempat_lahir' => 'Palembang', 'tanggal_lahir' => '1990-01-01', 'alamat' => 'Alamat uji',
            'no_hp' => '0800000000', 'no_wa' => '0800000000', 'bank' => 'Bank uji', 'no_rekening' => '000000', 'nama_rekening' => 'Rekening uji',
            'mulai_bekerja' => '2026-01-01', 'status' => 'aktif',
        ];
    }
}
