<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_uang_jalans', function (Blueprint $table): void {
            $table->bigInteger('uj_ditahan_ban')->default(0)->after('uj_ditahan');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_uang_jalans', function (Blueprint $table): void {
            $table->dropColumn('uj_ditahan_ban');
        });
    }
};
