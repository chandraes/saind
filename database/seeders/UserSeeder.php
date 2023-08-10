<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'username' => 'admin',
            'role' => 'admin',
            'name' => 'Administrator',
            'email' => 'saind@gmail.com',
            'password' => bcrypt('admin'),
        ]);
    }
}
