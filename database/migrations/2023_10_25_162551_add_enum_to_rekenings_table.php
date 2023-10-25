<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rekenings', function (Blueprint $table) {
            // $table->enum('jenis', ['kas', 'bank'])->default('kas')->after('nama');
            $table->enum('untuk', ['kas-besar', 'kas-kecil', 'kas-uang-jalan', 'mekanik'])->change();

        });

        DB::table('rekenings')->insert([
            'untuk' => 'mekanik',
            'nama_bank' => 'BCA',
            'nomor_rekening' => '123123123',
            'nama_rekening' => 'Mekanik',
        ]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('rekenings')->where('untuk', 'mekanik')->delete();
        
        Schema::table('rekenings', function (Blueprint $table) {
            // $table->dropColumn('jenis');
            $table->enum('untuk', ['kas-besar', 'kas-kecil', 'kas-uang-jalan'])->change();

        });
    }
};
