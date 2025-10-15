<?php

use App\Models\Pengaturan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $data = [
            [
                'untuk' => 'kas-uang-jalan',
                'nilai' => 100000,
            ]
        ];

        foreach ($data as $d) {
            Pengaturan::create($d);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Pengaturan::where('untuk', 'kas-uang-jalan')->delete();
    }
};
