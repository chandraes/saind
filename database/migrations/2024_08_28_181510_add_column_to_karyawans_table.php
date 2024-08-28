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
            $table->boolean('apa_bpjs_tk')->default(0)->after('npwp');
            $table->boolean('apa_bpjs_kesehatan')->default(0)->after('bpjs_tk');
        });

        Schema::table('direksis', function (Blueprint $table) {
            $table->boolean('apa_bpjs_tk')->default(0)->after('npwp');
            $table->boolean('apa_bpjs_kesehatan')->default(0)->after('bpjs_tk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $table->dropColumn('apa_bpjs_tk');
            $table->dropColumn('apa_bpjs_kesehatan');
        });

        Schema::table('direksis', function (Blueprint $table) {
            $table->dropColumn('apa_bpjs_tk');
            $table->dropColumn('apa_bpjs_kesehatan');
        });
    }
};
