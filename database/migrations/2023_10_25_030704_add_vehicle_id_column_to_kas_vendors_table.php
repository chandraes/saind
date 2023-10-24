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
            $table->foreignId('vehicle_id')->after('vendor_id')->nullable()->constrained('vehicles');
            $table->integer('quantity')->nullable()->after('vehicle_id');
            $table->bigInteger('harga_satuan')->nullable()->after('quantity');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kas_vendors', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->dropColumn('vehicle_id');
            $table->dropColumn('quantity');
            $table->dropColumn('harga_satuan');
        });
    }
};
