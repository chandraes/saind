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
        Schema::table('invoice_bayars', function (Blueprint $table) {
            $table->date('tempo')->nullable()->after('tanggal');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->integer('jatuh_tempo_hari')->default(14)->after('pph_val');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_bayars', function (Blueprint $table) {
            $table->dropColumn('tempo');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('jatuh_tempo_hari');
        });
    }
};
