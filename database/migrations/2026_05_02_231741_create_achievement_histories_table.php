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
        Schema::create('achievement_histories', function (Blueprint $table) {
            $table->id();
            $table->boolean('jenis');
            $table->string('uraian');
            $table->bigInteger('nominal');
            $table->timestamps();
        });

          Schema::table('kas_besars', function (Blueprint $table) {
            $table->boolean('achievement')->default(0)->after('form_pph');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievement_histories');

        Schema::table('kas_besars', function (Blueprint $table) {
            $table->dropColumn('achievement');
        });
    }
};
