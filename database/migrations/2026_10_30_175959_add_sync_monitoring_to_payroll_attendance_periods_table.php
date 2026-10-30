<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_attendance_periods', function (Blueprint $table): void {
            $table->json('snapshot')->nullable()->change();
            $table->timestamp('fetched_at')->nullable()->change();
            $table->timestamp('sync_attempted_at')->nullable();
            $table->timestamp('sync_failed_at')->nullable();
            $table->timestamp('next_sync_at')->nullable();
            $table->unsignedInteger('sync_failures')->default(0);
            $table->unsignedInteger('sync_duration_ms')->nullable();
            $table->string('sync_source', 16)->nullable();
            $table->string('sync_last_error', 500)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('payroll_attendance_periods', function (Blueprint $table): void {
            $table->dropColumn(['sync_attempted_at', 'sync_failed_at', 'next_sync_at', 'sync_failures', 'sync_duration_ms', 'sync_source', 'sync_last_error']);
        });
    }
};
