<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('filter_oli_logs', function (Blueprint $table): void {
            $table->dropColumn('limit_ritase');
        });
    }

    /** Rollback restores the current category limits; historical snapshots cannot be recovered. */
    public function down(): void
    {
        Schema::table('filter_oli_logs', function (Blueprint $table): void {
            $table->unsignedInteger('limit_ritase')->default(0);
        });
        DB::table('filter_oli_logs')->update([
            'limit_ritase' => DB::raw('(SELECT limit_ritase FROM kategori_filter_oli_mesins WHERE kategori_filter_oli_mesins.id = filter_oli_logs.kategori_filter_oli_mesin_id)'),
        ]);
    }
};
