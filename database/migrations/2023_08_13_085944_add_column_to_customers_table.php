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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('kode_customer')->nullable()->after('id');
            $table->text('alamat')->nullable()->after('singkatan');
            $table->string('npwp')->nullable()->after('alamat');
            $table->string('jabatan')->nullable()->after('contact_person');
            $table->string('no_hp')->nullable()->after('jabatan');
            $table->string('no_wa')->nullable()->after('no_hp');
            $table->string('email')->nullable()->after('no_wa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'kode_customer',
                'alamat',
                'npwp',
                'jabatan',
                'no_hp',
                'no_wa',
                'email',
            ]);
        });
    }
};
