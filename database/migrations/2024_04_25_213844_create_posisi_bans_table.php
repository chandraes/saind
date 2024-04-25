<?php

use App\Models\PosisiBan;
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
        Schema::create('posisi_bans', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('jenis');
            $table->timestamps();
        });

        $data = [
            ['nama' => 'Kanan Depan', 'jenis' => 'BAN BENANG'],
            ['nama' => 'Kiri Depan', 'jenis' => 'BAN BENANG'],
            ['nama' => 'Kanan Tengah Luar', 'jenis' => 'BAN KAWAT'],
            ['nama' => 'Kanan Tengah Dalam', 'jenis' => 'BAN KAWAT'],
            ['nama' => 'Kanan Belakang Luar', 'jenis' => 'BAN KAWAT'],
            ['nama' => 'Kanan Belakang Dalam', 'jenis' => 'BAN KAWAT'],
            ['nama' => 'Kiri Belakang Luar', 'jenis' => 'BAN KAWAT'],
            ['nama' => 'Kiri Belakang Dalam', 'jenis' => 'BAN KAWAT'],
            ['nama' => 'Kiri Tengah Luar', 'jenis' => 'BAN KAWAT'],
            ['nama' => 'Kiri Tengah Dalam', 'jenis' => 'BAN KAWAT'],
            ['nama' => 'Ban Serep', 'jenis' => 'BAN KAWAT']
        ];

        foreach ($data as $d) {
            PosisiBan::create($d);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posisi_bans');
    }
};
