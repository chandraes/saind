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
        Schema::table('uj_ditahan_details', function (Blueprint $table) {
            $table->string('bank')->nullable()->after('keterangan');
            $table->string('no_rekening')->nullable()->after('bank');
            $table->string('nama_rekening')->nullable()->after('no_rekening');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('uj_ditahan_details', function (Blueprint $table) {
            $table->dropColumn(['bank', 'no_rekening', 'nama_rekening']);
        });
    }
};
