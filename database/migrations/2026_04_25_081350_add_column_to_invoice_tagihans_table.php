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
       Schema::table('invoice_tagihans', function (Blueprint $table) {
            $table->foreignId('invoice_kompensasi_jr_id')->nullable()->after('kompensasi_jr')->constrained('invoice_additionals')->onDelete('set null');
            $table->foreignId('invoice_penyesuaian_bbm_id')->nullable()->after('penyesuaian_bbm')->constrained('invoice_additionals')->onDelete('set null');
            $table->foreignId('invoice_achievement_id')->nullable()->after('achievement')->constrained('invoice_additionals')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_tagihans', function (Blueprint $table) {
            $table->dropForeign(['invoice_kompensasi_jr_id']);
            $table->dropColumn('invoice_kompensasi_jr_id');
            $table->dropForeign(['invoice_penyesuaian_bbm_id']);
            $table->dropColumn('invoice_penyesuaian_bbm_id');
            $table->dropForeign(['invoice_achievement_id']);
            $table->dropColumn('invoice_achievement_id');
        });
    }
};
