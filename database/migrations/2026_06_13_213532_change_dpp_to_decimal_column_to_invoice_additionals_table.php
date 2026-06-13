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
        Schema::table('invoice_additionals', function (Blueprint $table) {
            $table->decimal('dpp', 15, 2)->change();
        });

        Schema::table('invoice_add_vendors', function (Blueprint $table) {
            $table->decimal('dpp', 15, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_additionals', function (Blueprint $table) {
            $table->integer('dpp')->change();
        });

        Schema::table('invoice_add_vendors', function (Blueprint $table) {
            $table->integer('dpp')->change();
        });
    }
};
