<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

abstract class KasBesarDepositTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        foreach (['2014_10_12_000000_create_users_table.php', '2023_08_26_113058_create_jenis_transaksis_table.php', '2023_08_26_113809_create_tipe_transaksis_table.php', '2023_08_26_140300_create_kas_besars_table.php', '2023_09_03_061547_drop_tipe_transaksi_table_and_column.php', '2023_10_21_092404_change_uraian_column_in_kas_besars_table.php', '2023_09_06_160726_create_group_was_table.php', '2024_08_30_215313_create_pesan_was_table.php', '2024_09_08_210338_create_legalitas_dokumens_table.php', '2024_09_11_132433_add_tanggal_expired_column_to_legalitas_dokumens_table.php'] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        foreach (glob(database_path('migrations/*create_rekenings_table.php')) as $migration) {
            (require $migration)->up();
        }
        DB::table('jenis_transaksis')->insert(['id' => 1, 'nama' => 'Masuk']);
        DB::table('rekenings')->insert(['untuk' => 'kas-besar', 'nama_rekening' => 'Rekening Deposit', 'nama_bank' => 'BCA', 'nomor_rekening' => '12345']);
        DB::table('group_was')->insert(['untuk' => 'kas-besar', 'nama_group' => 'deposit-test']);
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => 'user', 'password' => 'password']));
    }

    protected function migrateToken(): void
    {
        (require database_path('migrations/2026_10_31_190549_add_deposit_request_token_to_kas_besars_table.php'))->up();
    }
}
