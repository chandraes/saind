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
        Schema::table('ban_logs', function (Blueprint $table) {
            $table->decimal('ritase', 8, 2)->default(0)->after('kondisi');
        });
    }

    public function down(): void
    {
        Schema::table('ban_logs', function (Blueprint $table) {
            $table->dropColumn('ritase');
        });
    }
};
