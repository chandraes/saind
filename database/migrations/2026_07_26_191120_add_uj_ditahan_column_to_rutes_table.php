<?php

use App\Models\Rekening;
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
        Schema::table('rutes', function (Blueprint $table) {
            $table->bigInteger('uj_ditahan')->default(0)->after('uang_jalan');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->boolean('uj_ditahan')->default(0)->after('status');
            $table->foreignId('driver_id')->nullable()->unique()->after('uj_ditahan')->constrained('drivers')->onDelete('set null');
        });

        Schema::table('rekenings', function (Blueprint $table) {
            // ganti kolom 'untuk' dari enum menjadi string
            $table->string('untuk')->change();
        });

        Rekening::updateOrCreate(
            ['untuk' => 'uang-jalan-ditahan'],
            [
                'nama_bank' => 'BCA',
                'nomor_rekening' => '1234567890',
                'nama_rekening' => 'PT. SAIND',
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rutes', function (Blueprint $table) {
            $table->dropColumn('uj_ditahan');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);
            $table->dropColumn('driver_id');
            $table->dropColumn('uj_ditahan');
        });
    }
};
