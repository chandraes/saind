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
        Schema::table('kas_vendors', function (Blueprint $table) {
            $table->foreignId('bbm_storing_id')->nullable()->after('vehicle_id')->constrained('bbm_storings')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kas_vendors', function (Blueprint $table) {
            $table->dropForeign(['bbm_storing_id']);
            $table->dropColumn('bbm_storing_id');
        });
    }
};
