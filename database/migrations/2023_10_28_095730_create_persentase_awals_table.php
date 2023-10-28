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
        Schema::create('persentase_awals', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->integer('persentase');
            $table->timestamps();
        });

        Schema::table('pemegang_sahams', function (Blueprint $table) {
            $table->foreignId('persentase_awal_id')->constrained('persentase_awals')->onDelete('cascade')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pemegang_sahams', function (Blueprint $table) {
            $table->dropForeign(['persentase_awal_id']);
            $table->dropColumn('persentase_awal_id');
        });

        Schema::dropIfExists('persentase_awals');


    }
};
