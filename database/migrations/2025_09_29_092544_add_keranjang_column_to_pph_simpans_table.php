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
        Schema::table('pph_simpans', function (Blueprint $table) {
            $table->boolean('is_faktur')->default(0)->after('onhold');
            $table->string('no_faktur')->nullable()->after('nominal');
            $table->boolean('keranjang')->default(0)->after('onhold');
            $table->boolean('selesai')->default(0)->after('keranjang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pph_simpans', function (Blueprint $table) {
            $table->dropColumn(['is_faktur', 'no_faktur', 'keranjang', 'selesai']);
        });
    }
};
