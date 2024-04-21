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
        Schema::dropIfExists('maintenance_logs');


        Schema::table('barang_maintenances', function (Blueprint $table) {
            $table->foreignId('kategori_barang_maintenance_id')->nullable()->constrained()->onDelete('set null')->after('id');
        });

        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('kategori_barang_maintenance_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('barang_maintenance_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('qty');
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_logs');
    }
};
