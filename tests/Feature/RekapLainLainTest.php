<?php

namespace Tests\Feature;

use App\Models\KasBesar;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class RekapLainLainTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        DB::connection()->getPdo()->sqliteCreateFunction('YEAR', fn ($date) => (int) substr($date, 0, 4), 1);
        foreach (['2023_08_26_113058_create_jenis_transaksis_table.php', '2023_08_26_113809_create_tipe_transaksis_table.php', '2023_08_26_140300_create_kas_besars_table.php', '2023_09_03_061547_drop_tipe_transaksi_table_and_column.php', '2023_10_21_092404_change_uraian_column_in_kas_besars_table.php', '2025_09_18_202115_add_lain-lain_column_to_kas_besars_table.php'] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        DB::table('jenis_transaksis')->insert([['id' => 1, 'nama' => 'Masuk'], ['id' => 2, 'nama' => 'Keluar']]);
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => 'admin', 'password' => 'password']));
        View::share(['global_app_name' => 'SAIND', 'global_app_favicon' => '', 'global_app_nav_bg' => '#212529', 'global_app_logo' => '']);
        $this->withoutVite();
        $this->travelTo(Carbon::parse('2026-10-22 10:00:00'));
    }

    private function entry(array $attributes = []): KasBesar
    {
        return KasBesar::create(['tanggal' => '2026-10-22', 'uraian' => 'Transaksi uji', 'jenis_transaksi_id' => 1, 'nominal_transaksi' => 100000, 'saldo' => 100000, 'transfer_ke' => 'Penerima', 'bank' => 'BCA', 'no_rekening' => '123', 'lain_lain' => true, ...$attributes]);
    }

    private function rows(array $parameters = []): TestResponse
    {
        return $this->getJson(route('rekap.lain-lain', $parameters), ['X-Requested-With' => 'XMLHttpRequest']);
    }

    public function test_current_period_includes_only_other_form_transactions_and_correct_directions(): void
    {
        $this->entry(['uraian' => 'Penerimaan', 'nominal_transaksi' => 300000]);
        $this->entry(['uraian' => 'Pengeluaran', 'jenis_transaksi_id' => 2, 'nominal_transaksi' => 125000]);
        $this->entry(['lain_lain' => false, 'nominal_transaksi' => 900000]);
        $this->entry(['tanggal' => '2026-09-30', 'nominal_transaksi' => 800000]);
        $this->entry(['tanggal' => '2026-11-01', 'nominal_transaksi' => 700000]);
        $this->get(route('rekap.lain-lain'))->assertOk()->assertViewHas('bulan', 10)->assertViewHas('tahun', 2026)->assertViewHas('totals', ['count' => 2, 'masuk' => 300000, 'keluar' => 125000])->assertSee('Grand Total')->assertSee('table-success');
        $this->rows()->assertOk()->assertJsonCount(2, 'data')->assertJsonPath('recordsTotal', 2)->assertJsonPath('data.0.tanggal', '22-10-2026')->assertJsonPath('data.0.masuk', 0)->assertJsonPath('data.0.keluar', 125000)->assertJsonPath('data.1.masuk', 300000)->assertJsonPath('data.1.keluar', 0)->assertJsonPath('totals.masuk', 300000)->assertJsonPath('totals.keluar', 125000);
    }

    public function test_month_year_and_empty_periods_are_supported(): void
    {
        $this->entry(['tanggal' => '2025-12-31']);
        $this->entry(['tanggal' => '2026-01-01', 'jenis_transaksi_id' => 2]);
        $this->rows(['bulan' => 12, 'tahun' => 2025])->assertJsonCount(1, 'data')->assertJsonPath('totals.masuk', 100000)->assertJsonPath('totals.keluar', 0);
        $this->rows(['bulan' => 2, 'tahun' => 2026])->assertJsonCount(0, 'data')->assertJsonPath('totals.count', 0)->assertJsonPath('totals.masuk', 0)->assertJsonPath('totals.keluar', 0);
        $this->get(route('rekap.lain-lain'))->assertViewHas('years', fn ($years) => $years->map(fn ($year) => (int) $year)->all() === [2026, 2025]);
    }

    public function test_lazy_batches_keep_grand_totals_for_all_matching_rows(): void
    {
        for ($index = 0; $index < 105; $index++) {
            $this->entry();
        }
        $this->rows(['length' => 500])->assertOk()->assertJsonCount(100, 'data')->assertJsonPath('totals.count', 105)->assertJsonPath('totals.masuk', 10500000);
        $this->rows(['start' => 100, 'length' => 25])->assertJsonCount(5, 'data')->assertJsonPath('totals.masuk', 10500000);
    }

    #[TestWith(['Bantuan', 100000, 0])]
    #[TestWith(['Andi', 0, 250000])]
    #[TestWith(['BRI', 0, 250000])]
    public function test_datatables_searches_description_recipient_and_bank_with_matching_totals(string $search, int $incoming, int $outgoing): void
    {
        $this->entry(['uraian' => 'Bantuan kantor']);
        $this->entry(['uraian' => 'Pembelian', 'transfer_ke' => 'Andi', 'bank' => 'BRI', 'jenis_transaksi_id' => 2, 'nominal_transaksi' => 250000]);
        $this->rows(['search' => ['value' => $search, 'regex' => false]])->assertOk()->assertJsonPath('recordsTotal', 2)->assertJsonPath('recordsFiltered', 1)->assertJsonCount(1, 'data')->assertJsonPath('totals.masuk', $incoming)->assertJsonPath('totals.keluar', $outgoing);
    }

    #[TestWith([['bulan' => 13], 'bulan'])]
    #[TestWith([['tahun' => 0], 'tahun'])]
    public function test_invalid_period_is_rejected(array $parameters, string $field): void
    {
        $this->rows($parameters)->assertUnprocessable()->assertJsonValidationErrors($field);
    }

    public function test_report_requires_authentication_and_permitted_role(): void
    {
        auth()->forgetGuards();
        $this->get(route('rekap.lain-lain'))->assertRedirect('/login');
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => 'operasional', 'password' => 'password']));
        $this->get(route('rekap.lain-lain'))->assertForbidden();
    }
}
