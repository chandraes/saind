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
        // drop column tipe_transaksi_id from table kas_besars
        Schema::table('kas_besars', function (Blueprint $table) {
            $table->dropForeign(['tipe_transaksi_id']);
            $table->dropColumn('tipe_transaksi_id');
        });

        Schema::dropIfExists('tipe_transaksis');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('tipe_transaksis', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->timestamps();
        });

        Schema::table('kas_besars', function (Blueprint $table) {
            $table->foreignId('tipe_transaksi_id')->nullable()->constrained('tipe_transaksis')->onDelete('cascade');
        });
    }
};
