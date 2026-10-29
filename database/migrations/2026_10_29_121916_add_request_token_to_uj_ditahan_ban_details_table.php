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
        Schema::table('uj_ditahan_ban_details', function (Blueprint $table): void {
            $table->uuid('request_token')->nullable()->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('uj_ditahan_ban_details', function (Blueprint $table): void {
            $table->dropUnique(['request_token']);
            $table->dropColumn('request_token');
        });
    }
};
