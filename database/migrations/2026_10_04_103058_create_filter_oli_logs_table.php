<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filter_oli_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->restrictOnDelete();
            $table->foreignId('kategori_filter_oli_mesin_id')->constrained('kategori_filter_oli_mesins')->restrictOnDelete();
            $table->string('merk', 100);
            $table->unsignedTinyInteger('kondisi')->default(100);
            $table->decimal('ritase', 10, 1)->default(0);
            $table->unsignedInteger('limit_ritase');
            $table->index(['vehicle_id', 'kategori_filter_oli_mesin_id', 'created_at'], 'filter_oli_logs_history_index');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filter_oli_logs');
    }
};
