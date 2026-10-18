<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class NotaBonusTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        DB::connection()->getPdo()->sqliteCreateFunction('YEAR', fn ($date) => (int) substr($date, 0, 4), 1);
        Schema::create('sponsors', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
        });
        Schema::create('vendors', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('sponsor_id');
        });
        Schema::create('kas_uang_jalans', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
        });
        Schema::create('transaksis', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('kas_uang_jalan_id');
            $table->date('tanggal_bongkar');
            $table->date('tanggal_muat');
            $table->integer('status')->default(3);
            $table->boolean('void')->default(false);
            $table->boolean('bonus')->default(false);
            $table->boolean('nota_fisik')->default(false);
            $table->string('nota_muat')->nullable();
            $table->string('nota_bongkar')->nullable();
            $table->string('alasan')->nullable();
            $table->timestamps();
        });
        foreach (['2023_10_23_201037_create_invoice_bonuses_table.php', '2023_10_23_201051_create_invoice_bonus_details_table.php'] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        View::share(['global_app_name' => 'SAIND', 'global_app_favicon' => '', 'global_app_nav_bg' => '#212529', 'global_app_logo' => '']);
        $this->withoutVite();
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => 'admin', 'password' => 'password']));
    }

    public function test_success_redirects_to_a_loadable_bonus_page_and_retry_creates_no_empty_invoice(): void
    {
        $sponsor = DB::table('sponsors')->insertGetId(['nama' => 'Sponsor Uji']);
        $vendor = DB::table('vendors')->insertGetId(['sponsor_id' => $sponsor]);
        $cash = DB::table('kas_uang_jalans')->insertGetId(['vendor_id' => $vendor]);
        $transaction = DB::table('transaksis')->insertGetId(['kas_uang_jalan_id' => $cash, 'tanggal_bongkar' => '2026-09-30', 'tanggal_muat' => '2026-09-29']);
        $endpoint = route('transaksi.nota-bonus.lanjut', $sponsor);
        $destination = route('transaksi.nota-bonus', ['sponsor_id' => $sponsor, 'bulan' => 9, 'tahun' => 2026]);
        $payload = ['total_bonus' => 150000, 'bulan' => 9, 'tahun' => 2026];

        $response = $this->post($endpoint, $payload)->assertRedirect($destination)->assertSessionHas('success', 'Nota bonus berhasil disimpan.');
        $this->assertDatabaseHas('invoice_bonuses', ['sponsor_id' => $sponsor, 'total_bonus' => 150000, 'sisa_bonus' => 150000]);
        $this->assertDatabaseHas('invoice_bonus_details', ['transaksi_id' => $transaction]);
        $this->assertDatabaseHas('transaksis', ['id' => $transaction, 'bonus' => 1]);
        $this->get($response->headers->get('Location'))->assertOk()->assertSee('Sponsor Uji')->assertSee('Nota bonus berhasil disimpan.');

        $this->post($endpoint, $payload)->assertRedirect($destination)->assertSessionHas('error', 'Tidak ada transaksi bonus yang belum diproses pada periode ini.');
        $this->assertDatabaseCount('invoice_bonuses', 1);
        $this->assertDatabaseCount('invoice_bonus_details', 1);
        $this->assertSame(0, DB::transactionLevel());
    }

    #[TestWith([0, 2026, 'bulan'])]
    #[TestWith([13, 2026, 'bulan'])]
    #[TestWith([9, 0, 'tahun'])]
    public function test_invalid_period_does_not_create_invoice(int $month, int $year, string $field): void
    {
        $sponsor = DB::table('sponsors')->insertGetId(['nama' => 'Sponsor Uji']);
        $this->post(route('transaksi.nota-bonus.lanjut', $sponsor), ['total_bonus' => 150000, 'bulan' => $month, 'tahun' => $year])->assertSessionHasErrors($field);
        $this->assertDatabaseCount('invoice_bonuses', 0);
    }
}
