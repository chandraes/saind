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
        Schema::table('odo_logs', function (Blueprint $table) {
            $table->boolean('filter_strainer')->default(0)->after('odometer');
            $table->boolean('filter_udara')->default(0)->after('filter_strainer');
            $table->integer('baut')->default(0)->after('filter_udara');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('odo_logs', function (Blueprint $table) {
            $table->dropColumn('filter_strainer');
            $table->dropColumn('filter_udara');
            $table->dropColumn('baut');
        });
    }
};
