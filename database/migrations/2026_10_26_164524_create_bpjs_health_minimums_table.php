<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bpjs_health_minimums', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('year')->unique();
            $table->unsignedBigInteger('minimum_wage');
            $table->string('basis', 3);
            $table->text('reference');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bpjs_health_minimums');
    }
};
