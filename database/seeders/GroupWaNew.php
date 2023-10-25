<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\GroupWa;

class GroupWaNew extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        GroupWa::create([
            'untuk' => 'team',
            'nama_group' => 'Test Group'
            ]);
    }
}
