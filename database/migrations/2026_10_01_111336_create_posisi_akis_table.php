<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posisi_akis', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // Aki 1, Aki 2
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });

        $data = [
            ['nama' => 'Aki 1', 'keterangan' => 'Posisi Aki 1', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Aki 2', 'keterangan' => 'Posisi Aki 2', 'created_at' => now(), 'updated_at' => now()],
        ];
        
        DB::table('posisi_akis')->insert($data);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posisi_akis');
    }
};
