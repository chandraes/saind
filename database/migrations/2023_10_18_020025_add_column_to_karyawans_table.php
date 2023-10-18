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
        Schema::table('karyawans', function (Blueprint $table) {
            $table->integer('gaji_pokok')->after('jabatan_id')->default(0);
            $table->integer('tunjangan_jabatan')->after('gaji_pokok')->default(0);
            $table->integer('tunjangan_keluarga')->after('tunjangan_jabatan')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $table->dropColumn('gaji_pokok');
            $table->dropColumn('tunjangan_jabatan');
            $table->dropColumn('tunjangan_keluarga');
        });
    }
};
