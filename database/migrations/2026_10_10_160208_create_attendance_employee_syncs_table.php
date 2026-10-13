<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_employee_syncs', function (Blueprint $table): void {
            $table->id();
            $table->string('source_employee_id', 100)->unique();
            $table->json('payload')->nullable();
            $table->string('payload_hash', 64)->nullable();
            $table->unsignedBigInteger('source_version')->default(0);
            $table->unsignedBigInteger('delivered_version')->default(0);
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('next_retry_at')->nullable()->index();
            $table->uuid('lease_token')->nullable();
            $table->timestamp('lease_until')->nullable();
            $table->string('last_error', 250)->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_employee_syncs');
    }
};
