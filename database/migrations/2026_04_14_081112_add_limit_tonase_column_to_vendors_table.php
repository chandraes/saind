<?php

use App\Models\Pengaturan;
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
        Schema::table('vendors', function (Blueprint $table) {
            $table->boolean('limit_tonase')->default(false)->after('user_id');
        });

        Pengaturan::create([
            'untuk' => 'limit-tonase-muat',
            'nilai' => 0,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('limit_tonase');
        });

        Pengaturan::where('untuk', 'limit-tonase-muat')->delete();
    }
};
