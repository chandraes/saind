<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cost_operationals', function (Blueprint $table) {
            $table->bigInteger('nominal')->default(0)->after('nama');
            $table->string('periode')->default('mingguan')->after('nominal'); // mingguan / bulanan
            $table->integer('jumlah_limit')->default(1)->after('periode');
        });

        Schema::table('kas_besars', function (Blueprint $table) {
            $table->foreignId('cost_operational_id')->nullable()->constrained('cost_operationals')->nullOnDelete()->after('cost_operational');
        });
    }

    public function down(): void
    {
        Schema::table('cost_operationals', function (Blueprint $table) {
            $table->dropColumn(['nominal', 'periode', 'jumlah_limit']);
        });

        Schema::table('kas_besars', function (Blueprint $table) {
            $table->dropForeign(['cost_operational_id']);
            $table->dropColumn('cost_operational_id');
        });
    }
};
