<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kas_besars', function (Blueprint $table): void {
            $table->uuid('deposit_request_token')->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('kas_besars', function (Blueprint $table): void {
            $table->dropUnique(['deposit_request_token']);
            $table->dropColumn('deposit_request_token');
        });
    }
};
