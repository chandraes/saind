<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

abstract class MobileApiTestCase extends TestCase
{
    protected const WORKSPACE = 'b1bca759-224f-4f48-bbe7-76904c3434fa';

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'mobile.workspace_id' => self::WORKSPACE, 'mobile.workspace_name' => 'SAIND Hauling',
            'mobile.company_name' => 'DS Groups', 'mobile.token_expiration_minutes' => 60]);
        DB::purge('sqlite');
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('username')->unique();
            $table->string('role');
            $table->string('name');
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->timestamps();
        });
        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
        Schema::create('vendors', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            foreach (['nickname', 'perusahaan', 'status', 'tipe', 'jabatan', 'npwp', 'alamat', 'no_hp', 'email', 'bank', 'no_rekening', 'nama_rekening', 'pembayaran'] as $field) {
                $table->string($field)->nullable();
            }
        });
        Schema::create('vehicles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->string('nomor_lambung');
            $table->string('status')->default('aktif');
            $table->unsignedInteger('do_count')->default(0);
            $table->string('no_rekening')->nullable();
            foreach (['nopol', 'tipe', 'tahun', 'nama_stnk', 'no_rangka', 'no_mesin', 'tanggal_pajak_stnk', 'tanggal_kir', 'tanggal_kimper', 'tanggal_sim'] as $field) {
                $table->string($field)->nullable();
            }
            $table->timestamps();
        });
        Schema::create('konfigurasis', function (Blueprint $table): void {
            $table->id();
            $table->string('kode');
            $table->boolean('status')->default(false);
        });
        (require database_path('migrations/2019_12_14_000001_create_personal_access_tokens_table.php'))->up();
    }
}
