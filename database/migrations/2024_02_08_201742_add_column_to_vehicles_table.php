<?php

use App\Models\Transaksi;
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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->integer('do_count')->default(0)->after('nomor_lambung');
        });

        $data = Transaksi::with('kas_uang_jalan', 'kas_uang_jalan.vehicle')->where('status', 3)->where('void', 0)->where('tagihan', 0)
                ->get();

        foreach ($data as $d) {
            $vehicle = $d->kas_uang_jalan->vehicle;
            $vehicle->do_count = $vehicle->do_count + 1;
            $vehicle->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('do_count');
        });
    }
};
