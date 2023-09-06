<?php

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
        Schema::create('sponsors', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 1)->default('S');
            $table->integer('nomor_kode_sponsor');
            $table->string('nama');
            $table->string('nomor_wa');
            $table->string('transfer_ke');
            $table->string('nama_bank');
            $table->string('nomor_rekening');
            $table->timestamps();
        });

        Schema::table('vendors', function (Blueprint $table) {
            // add transfer_ke column after no_kartu_gps
            $table->foreignId('sponsor_id')->nullable()->after('status')->constrained('sponsors');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            // drop column transfer_ke
            $table->dropForeign(['sponsor_id']);
            $table->dropColumn('sponsor_id');
        });

        Schema::dropIfExists('sponsors');


    }
};
