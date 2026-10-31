<?php

namespace Tests\Feature;

use App\Models\AkiGantiCart;
use App\Models\FilterOliGantiCart;
use App\Models\GroupWa;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class AkiMaintenanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        foreach (['users', 'vendors', 'vehicles', 'posisi_akis', 'drivers', 'transaksis'] as $name) {
            Schema::create($name, function (Blueprint $table) use ($name): void {
                $table->id();
                if ($name === 'users') {
                    $table->string('name')->default('Tester');
                    $table->string('email')->nullable();
                    $table->string('password')->default('');
                    $table->string('role');
                    $table->timestamp('email_verified_at')->nullable();
                }
                if ($name === 'vehicles') {
                    $table->string('nomor_lambung')->default('A01');
                    $table->string('status')->default('aktif');
                    $table->boolean('pembatasan_filter_oli')->default(true);
                    $table->unsignedBigInteger('vendor_id')->nullable();
                    $table->unsignedBigInteger('driver_id')->nullable();
                }
                if (in_array($name, ['vendors', 'posisi_akis'])) {
                    $table->string('nama')->default('Posisi 1');
                }
                if ($name === 'drivers') {
                    $table->string('no_hp')->nullable();
                }
                if ($name === 'transaksis') {
                    $table->unsignedBigInteger('kas_uang_jalan_id');
                    $table->boolean('void')->default(false);
                }
                $table->timestamps();
            });
        }
        Schema::create('rutes', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->integer('jarak');
        });
        Schema::create('kas_uang_jalans', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('rute_id');
            $table->integer('nomor_uang_jalan');
        });
        Schema::create('aki_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('posisi_aki_id');
            $table->string('merk');
            $table->string('no_seri');
            $table->integer('kondisi');
            $table->timestamps();
        });
        foreach (['2026_10_01_111414_create_aki_ganti_invoices_table.php', '2026_10_01_111426_create_aki_ganti_invoice_details_table.php', '2026_10_01_123942_create_aki_ganti_carts_table.php', '2026_08_07_071658_create_uj_ditahans_table.php', '2026_08_07_071708_create_uj_ditahan_details_table.php', '2026_08_07_153001_add_rekening_to_uj_ditahan_details_table.php', '2026_10_04_094812_create_kategori_filter_oli_mesins_table.php', '2026_10_04_103058_create_filter_oli_logs_table.php', '2026_10_04_103100_create_filter_oli_ganti_carts_table.php', '2026_10_04_103101_create_filter_oli_ganti_invoices_table.php', '2026_10_04_103102_create_filter_oli_ganti_invoice_details_table.php', '2026_10_04_113741_create_filter_oli_log_transaksis_table.php', '2026_10_07_191623_drop_limit_ritase_from_filter_oli_logs_table.php'] as $file) {
            (require database_path('migrations/'.$file))->up();
        }
        foreach (glob(database_path('migrations/*add_maintenance_ownership_and_uj_ditahan_references.php')) as $file) {
            (require $file)->up();
        }
        Schema::create('kas_besars', function (Blueprint $table): void {
            $table->id();
            foreach (['tanggal', 'uraian', 'bank', 'no_rekening', 'transfer_ke'] as $column) {
                $table->string($column)->nullable();
            }
            foreach (['saldo', 'modal_investor_terakhir', 'nominal_transaksi'] as $column) {
                $table->bigInteger($column)->default(0);
            }
            foreach (['aki_ganti_invoice_id', 'filter_oli_ganti_invoice_id', 'ban_ganti_invoice_id', 'jenis_transaksi_id'] as $column) {
                $table->unsignedBigInteger($column)->nullable();
            }
            $table->timestamps();
        });
        Schema::create('upah_gendongs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->string('nama_driver')->nullable();
            $table->string('nama_pengurus')->nullable();
        });
        Schema::create('kas_vendors', function (Blueprint $table): void {
            $table->id();
        });
        (require database_path('migrations/2026_10_29_102252_create_uj_ditahan_bans_table.php'))->up();
        (require database_path('migrations/2023_09_06_160726_create_group_was_table.php'))->up();
        GroupWa::create(['untuk' => 'kas-uj-ditahan', 'nama_group' => 'uj-ditahan-test']);
        $messenger = new class extends GroupWa
        {
            public array $sent = [];

            public bool $fail = false;

            public array $failTargets = [];

            public function sendWa($tujuan, $pesan)
            {
                if ($this->fail || in_array($tujuan, $this->failTargets, true)) {
                    throw new \RuntimeException('Simulated WA failure');
                }
                $this->sent[] = ['to' => $tujuan, 'text' => $pesan, 'transaction_level' => DB::transactionLevel()];
            }
        };
        $this->app->instance(GroupWa::class, $messenger);
        DB::table('vehicles')->insert(['id' => 1]);
        DB::table('posisi_akis')->insert(['id' => 1]);
        DB::table('kategori_filter_oli_mesins')->insert(['id' => 1, 'nama' => 'Filter Oli', 'limit_ritase' => 34]);
        View::share(['global_app_name' => 'SAIND', 'global_app_favicon' => '', 'global_app_nav_bg' => '', 'global_app_logo' => '']);
        $this->withoutVite();
    }

    private function actor(string $role = 'user'): User
    {
        $user = User::forceCreate(['name' => 'Tester', 'email' => uniqid().'@example.com', 'password' => 'secret', 'role' => $role]);
        $this->actingAs($user);

        return $user;
    }

    private function balance(int $month, int $amount): int
    {
        return DB::table('uj_ditahans')->insertGetId(['vehicle_id' => 1, 'bulan' => $month, 'tahun' => 2026, 'saldo' => $amount, 'total_masuk' => $amount, 'total_keluar' => 0]);
    }

    private function cart(User $user, string $type = 'aki'): int
    {
        if ($type === 'aki') {
            return AkiGantiCart::create(['user_id' => $user->id, 'vehicle_id' => 1, 'posisi_aki_id' => 1, 'merk' => 'GS', 'no_seri' => '-', 'kondisi' => 100])->id;
        }

        return FilterOliGantiCart::create(['user_id' => $user->id, 'vehicle_id' => 1, 'kategori_filter_oli_mesin_id' => 1, 'merk' => 'FILTER', 'kondisi' => 100, 'ritase' => 0])->id;
    }

    private function payload(int $id, mixed $nominal = 150000): array
    {
        return ['vehicle_id' => 1, 'items' => [$id => ['tanggal_ganti' => today()->subDay()->toDateString()]], 'pembayaran' => 'uj_ditahan', 'total_nominal' => $nominal, 'nama_bank' => 'BCA', 'nomor_rekening' => '123456', 'nama_rekening' => 'Bengkel'];
    }

    #[TestWith(['aki'])]
    #[TestWith(['filter-oli'])]
    public function test_checkout_checks_balance_without_debiting_it(string $type): void
    {
        $user = $this->actor();
        $id = $this->cart($user, $type);
        $table = $type === 'aki' ? 'aki_ganti_invoices' : 'filter_oli_ganti_invoices';
        $cartTable = $type === 'aki' ? 'aki_ganti_carts' : 'filter_oli_ganti_carts';
        $this->post(route('billing.form-maintenance.'.$type.'.checkout'), $this->payload($id))->assertSessionHasErrors('total_nominal');
        $this->assertDatabaseCount($table, 0);
        $this->assertDatabaseHas($cartTable, ['id' => $id, 'user_id' => $user->id]);
        $this->balance(8, 100000);
        $this->post(route('billing.form-maintenance.'.$type.'.checkout'), $this->payload($id))->assertSessionHasErrors('total_nominal');
        $this->assertDatabaseCount($table, 0);
        $this->assertDatabaseHas($cartTable, ['id' => $id, 'user_id' => $user->id]);
        $this->assertEquals(100000, DB::table('uj_ditahans')->sum('saldo'));
        $this->balance(9, 50000);
        $this->post(route('billing.form-maintenance.'.$type.'.checkout'), $this->payload($id))->assertSessionHasNoErrors();
        $this->assertDatabaseHas($table, ['pembayaran' => 'uj_ditahan', 'total_nominal' => 150000, 'status' => 'pending']);
        $this->assertEquals(150000, DB::table('uj_ditahans')->sum('saldo'));
        $this->assertDatabaseCount('uj_ditahan_details', 0);
    }

    #[TestWith(['aki'])]
    #[TestWith(['filter-oli'])]
    public function test_approval_debits_oldest_months_once_and_does_not_touch_cash(string $type): void
    {
        $user = $this->actor('admin');
        $oldest = $this->balance(8, 100000);
        $newer = $this->balance(9, 100000);
        $id = $this->cart($user, $type);
        $this->post(route('billing.form-maintenance.'.$type.'.checkout'), $this->payload($id))->assertSessionHasNoErrors();
        $url = route('billing.otorisasi-maintenance.'.$type.'.approve', 1);
        $this->post($url)->assertSessionHas('success');
        $this->assertDatabaseHas('uj_ditahans', ['id' => $oldest, 'saldo' => 0, 'total_keluar' => 100000]);
        $this->assertDatabaseHas('uj_ditahans', ['id' => $newer, 'saldo' => 50000, 'total_keluar' => 50000]);
        $reference = $type === 'aki' ? 'aki_ganti_invoice_id' : 'filter_oli_ganti_invoice_id';
        $this->assertDatabaseHas('uj_ditahan_details', [$reference => 1, 'uj_ditahan_id' => $oldest, 'jenis' => 'keluar', 'nominal' => 100000, 'bank' => 'BCA']);
        $this->post($url)->assertSessionHas('error');
        $this->post(route('billing.otorisasi-maintenance.'.$type.'.reject', 1))->assertSessionHas('error');
        $this->assertDatabaseCount('uj_ditahan_details', 2);
        $this->assertDatabaseCount('kas_besars', 0);
        $this->assertDatabaseCount('kas_vendors', 0);
    }

    #[TestWith(['aki'])]
    #[TestWith(['filter-oli'])]
    public function test_balance_spent_after_checkout_prevents_approval_and_preserves_pending(string $type): void
    {
        $user = $this->actor('admin');
        $this->balance(8, 200000);
        $id = $this->cart($user, $type);
        $this->post(route('billing.form-maintenance.'.$type.'.checkout'), $this->payload($id))->assertSessionHasNoErrors();
        DB::table('uj_ditahans')->update(['saldo' => 10000]);
        $this->post(route('billing.otorisasi-maintenance.'.$type.'.approve', 1))->assertSessionHas('error');
        $this->assertDatabaseCount('aki_logs', 0);
        $this->assertDatabaseCount('filter_oli_logs', 0);
        $this->assertDatabaseCount('uj_ditahan_details', 0);
        $table = $type === 'aki' ? 'aki_ganti_invoices' : 'filter_oli_ganti_invoices';
        $this->assertDatabaseHas($table, ['id' => 1, 'status' => 'pending']);
    }

    public function test_user_cannot_authorize_aki_and_cannot_delete_other_users_cart(): void
    {
        $first = $this->actor();
        $id = $this->cart($first);
        $this->actor();
        $this->delete(route('billing.form-maintenance.aki.cart.delete', $id))->assertNotFound();
        $this->post(route('billing.otorisasi-maintenance.aki.approve', 1))->assertForbidden();
        $this->post(route('billing.otorisasi-maintenance.aki.reject', 1))->assertForbidden();
        $this->assertDatabaseCount('aki_ganti_carts', 1);
    }

    #[TestWith([-100000])]
    #[TestWith(['1.5'])]
    #[TestWith(['100abc'])]
    #[TestWith([0])]
    public function test_aki_rejects_invalid_nominal_without_coercion(mixed $nominal): void
    {
        $user = $this->actor();
        $id = $this->cart($user);
        $this->post(route('billing.form-maintenance.aki.checkout'), $this->payload($id, $nominal))->assertSessionHasErrors('total_nominal');
        $this->assertDatabaseCount('aki_ganti_invoices', 0);
    }

    public function test_aki_requires_exact_cart_dates_and_active_vehicle(): void
    {
        $user = $this->actor();
        $id = $this->cart($user);
        $this->balance(8, 200000);
        $data = $this->payload($id);
        $data['items'] = [999 => ['tanggal_ganti' => today()->toDateString()]];
        $this->post(route('billing.form-maintenance.aki.checkout'), $data)->assertSessionHasErrors('items');
        DB::table('vehicles')->where('id', 1)->update(['status' => 'nonaktif']);
        $this->post(route('billing.form-maintenance.aki.checkout'), $this->payload($id))->assertSessionHasErrors('vehicle_id');
    }

    #[TestWith(['aki'])]
    #[TestWith(['filter-oli'])]
    public function test_failure_after_debit_rolls_back_saldo_history_and_authorization(string $type): void
    {
        $user = $this->actor('admin');
        $this->balance(8, 200000);
        $id = $this->cart($user, $type);
        $this->post(route('billing.form-maintenance.'.$type.'.checkout'), $this->payload($id))->assertSessionHasNoErrors();
        $logs = $type === 'aki' ? 'aki_logs' : 'filter_oli_logs';
        DB::unprepared('CREATE TRIGGER fail_maintenance_log BEFORE INSERT ON '.$logs." BEGIN SELECT RAISE(ABORT, 'simulated log failure'); END");
        $this->post(route('billing.otorisasi-maintenance.'.$type.'.approve', 1))->assertSessionHas('error');
        $this->assertDatabaseHas('uj_ditahans', ['saldo' => 200000, 'total_keluar' => 0]);
        $this->assertDatabaseCount('uj_ditahan_details', 0);
        $this->assertDatabaseCount($logs, 0);
        $table = $type === 'aki' ? 'aki_ganti_invoices' : 'filter_oli_ganti_invoices';
        $this->assertDatabaseHas($table, ['status' => 'pending', 'authorized_by' => null]);
    }

    #[TestWith(['aki'])]
    #[TestWith(['filter-oli'])]
    public function test_bank_is_required_and_checkout_shows_balance_without_self_payment(string $type): void
    {
        $user = $this->actor();
        $this->balance(8, 200000);
        $id = $this->cart($user, $type);

        $this->get(route('billing.form-maintenance.'.$type.'.confirm'))->assertOk()->assertSee('200.000')->assertDontSee('value="dibayar_sendiri"', false)->assertSee('data-maintenance-money-value', false);

        $data = $this->payload($id);
        unset($data['nama_bank'], $data['nomor_rekening'], $data['nama_rekening']);
        $this->post(route('billing.form-maintenance.'.$type.'.checkout'), $data)->assertSessionHasErrors(['nama_bank', 'nomor_rekening', 'nama_rekening']);
    }

    #[TestWith(['aki'])]
    #[TestWith(['filter-oli'])]
    public function test_authorization_table_loads_balance_search_and_bounded_rows(string $type): void
    {
        $user = $this->actor('admin');
        $this->balance(8, 200000);
        $id = $this->cart($user, $type);
        $this->post(route('billing.form-maintenance.'.$type.'.checkout'), $this->payload($id))->assertSessionHasNoErrors();
        $url = route('billing.otorisasi-maintenance.'.$type);
        $this->get($url)->assertOk()->assertSee('Saldo UJ Ditahan');
        $this->getJson($url.'?length=1&draw=2', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertJsonPath('draw', 2)->assertJsonPath('recordsTotal', 1)->assertJsonCount(1, 'data')->assertJsonPath('data.0.saldo_uj', 200000)->assertJsonPath('data.0.pembayaran', 'UJ Ditahan Sparepart');
        $this->getJson($url.'?search[value]=DOES-NOT-EXIST', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertJsonPath('recordsFiltered', 0)->assertJsonCount(0, 'data');
    }

    public function test_aki_cart_isolated_and_duplicate_positions_and_inactive_vehicles_rejected(): void
    {
        $first = $this->actor();
        $this->cart($first);
        $second = $this->actor();
        $data = ['vehicle_id' => 1, 'posisi_aki_id' => 1, 'merk' => 'GS', 'kondisi' => 100];
        $this->postJson(route('billing.form-maintenance.aki.cart.add'), $data)->assertOk();
        $this->postJson(route('billing.form-maintenance.aki.cart.add'), $data)->assertUnprocessable()->assertJsonValidationErrors('posisi_aki_id');
        $this->assertDatabaseCount('aki_ganti_carts', 2);
        $this->delete(route('billing.form-maintenance.aki.cart.clear', 1))->assertOk();
        $this->assertDatabaseCount('aki_ganti_carts', 1);
        $this->assertDatabaseHas('aki_ganti_carts', ['user_id' => $first->id]);
        DB::table('vehicles')->update(['status' => 'nonaktif']);
        $this->postJson(route('billing.form-maintenance.aki.cart.add'), $data)->assertUnprocessable()->assertJsonValidationErrors('vehicle_id');
    }

    public function test_admin_self_payment_remains_supported_for_existing_flow(): void
    {
        $user = $this->actor('admin');
        $id = $this->cart($user);
        $data = $this->payload($id);
        $data['pembayaran'] = 'dibayar_sendiri';
        $this->post(route('billing.form-maintenance.aki.checkout'), $data)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('aki_ganti_invoices', ['pembayaran' => 'dibayar_sendiri', 'total_nominal' => 0]);
        $this->actor('admin');
        $this->post(route('billing.otorisasi-maintenance.aki.approve', 1))->assertSessionHas('success');
        $this->assertDatabaseCount('aki_logs', 1);
        $this->assertDatabaseCount('uj_ditahan_details', 0);
    }

    public function test_aki_pending_date_edit_rejects_future_and_locks_after_approval(): void
    {
        $user = $this->actor('admin');
        $id = $this->cart($user);
        $data = $this->payload($id);
        $data['pembayaran'] = 'dibayar_sendiri';
        $this->post(route('billing.form-maintenance.aki.checkout'), $data)->assertSessionHasNoErrors();
        $url = route('rekap.maintenance.aki.detail.update-tanggal', 1);
        $this->post($url, ['tanggal_ganti' => today()->addDay()->toDateString()])->assertSessionHasErrors('tanggal_ganti');
        $date = today()->subDays(5)->toDateString();
        $this->post($url, ['tanggal_ganti' => $date])->assertSessionHas('success');
        $this->post(route('billing.otorisasi-maintenance.aki.approve', 1))->assertSessionHas('success');
        $this->assertDatabaseHas('aki_logs', ['created_at' => $date.' 00:00:00']);
        $this->post($url, ['tanggal_ganti' => today()->toDateString()])->assertSessionHas('error');
        $this->post(route('rekap.maintenance.aki.detail.update', 1), ['merk' => 'CHANGED', 'kondisi' => 90])->assertSessionHas('error');
        $this->assertDatabaseHas('aki_ganti_invoice_details', ['merk' => 'GS', 'created_at' => $date.' 00:00:00']);
    }

    public function test_kas_besar_still_debits_cash_once_without_uj_mutations(): void
    {
        $user = $this->actor('admin');
        $id = $this->cart($user);
        DB::table('kas_besars')->insert(['saldo' => 200000]);
        $data = $this->payload($id);
        $data['pembayaran'] = 'kas_besar';
        $this->post(route('billing.form-maintenance.aki.checkout'), $data)->assertSessionHasNoErrors();
        $url = route('billing.otorisasi-maintenance.aki.approve', 1);
        $this->post($url)->assertSessionHas('success');
        $this->assertDatabaseHas('kas_besars', ['aki_ganti_invoice_id' => 1, 'saldo' => 50000, 'nominal_transaksi' => 150000]);
        $this->post($url)->assertSessionHas('error');
        $this->assertDatabaseCount('kas_besars', 2);
        $this->assertDatabaseCount('uj_ditahan_details', 0);
    }

    #[TestWith(['aki'])]
    #[TestWith(['filter-oli'])]
    public function test_rejection_preserves_uj_balance_and_detail_displays_current_balance(string $type): void
    {
        $user = $this->actor('admin');
        $id = $this->cart($user, $type);
        $this->balance(8, 200000);
        $this->post(route('billing.form-maintenance.'.$type.'.checkout'), $this->payload($id))->assertSessionHasNoErrors();
        $url = $type === 'aki' ? route('rekap.maintenance.aki.show', 1) : route('billing.otorisasi-maintenance.filter-oli.show', 1);
        $this->get($url)->assertOk()->assertSee('Saldo kendaraan saat ini:')->assertSee('200.000');
        $this->post(route('billing.otorisasi-maintenance.'.$type.'.reject', 1))->assertSessionHas('success');
        $this->assertDatabaseHas('uj_ditahans', ['saldo' => 200000]);
        $this->assertDatabaseCount('uj_ditahan_details', 0);
        $this->post(route('billing.otorisasi-maintenance.'.$type.'.approve', 1))->assertSessionHas('error');
    }

    public function test_migration_removes_legacy_cart_and_preserves_invoice_history(): void
    {
        $user = $this->actor('admin');
        $id = $this->cart($user);
        $data = $this->payload($id);
        $data['pembayaran'] = 'dibayar_sendiri';
        $this->post(route('billing.form-maintenance.aki.checkout'), $data)->assertSessionHasNoErrors();
        $this->post(route('billing.otorisasi-maintenance.aki.approve', 1))->assertSessionHas('success');
        $migration = require glob(database_path('migrations/*add_maintenance_ownership_and_uj_ditahan_references.php'))[0];
        $migration->down();
        DB::table('aki_ganti_carts')->insert(['vehicle_id' => 1, 'posisi_aki_id' => 1, 'merk' => 'OLD', 'no_seri' => '-', 'kondisi' => 100]);
        $migration->up();
        $this->assertDatabaseCount('aki_ganti_carts', 0);
        $this->assertDatabaseCount('aki_ganti_invoices', 1);
        $this->assertDatabaseCount('aki_ganti_invoice_details', 1);
        $this->assertDatabaseCount('aki_logs', 1);
    }

    #[TestWith(['aki'])]
    #[TestWith(['filter-oli'])]
    public function test_checkout_versions_maintenance_script_to_refresh_cached_browser_assets(string $type): void
    {
        $user = $this->actor();
        $this->cart($user, $type);
        $html = $this->get(route('billing.form-maintenance.'.$type.'.confirm'))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/filter-oli-maintenance\\.js\\?v=[0-9a-f]+/', $html);
    }

    #[TestWith(['aki'])]
    #[TestWith(['filter-oli'])]
    public function test_checkout_renders_valid_hidden_attributes_before_payment_selection(string $type): void
    {
        $user = $this->actor();
        $this->cart($user, $type);
        $html = $this->get(route('billing.form-maintenance.'.$type.'.confirm'))->assertOk()->getContent();
        $document = new \DOMDocument;
        $document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new \DOMXPath($document);
        foreach (['data-bank-fields', 'data-uj-balance'] as $attribute) {
            $element = $xpath->query('//*[@'.$attribute.']')->item(0);
            $this->assertTrue($element->hasAttribute('hidden'));
        }
        $this->assertStringNotContainsString('@hidden(', $html);
    }

    #[TestWith(['aki'])]
    #[TestWith(['filter-oli'])]
    public function test_uj_sparepart_approval_notifies_group_after_commit_with_vehicle_wallet_totals(string $type): void
    {
        $user = $this->actor('admin');
        $this->balance(8, 100000);
        $this->balance(9, 100000);
        DB::table('uj_ditahan_bans')->insert(['vehicle_id' => 1, 'bulan' => 8, 'tahun' => 2026, 'saldo' => 70000]);
        DB::table('vehicles')->insert(['id' => 2, 'nomor_lambung' => 'OTHER']);
        DB::table('uj_ditahans')->insert(['vehicle_id' => 2, 'bulan' => 8, 'tahun' => 2026, 'saldo' => 999000]);
        DB::table('uj_ditahan_bans')->insert(['vehicle_id' => 2, 'bulan' => 8, 'tahun' => 2026, 'saldo' => 999000]);
        $id = $this->cart($user, $type);
        $this->post(route('billing.form-maintenance.'.$type.'.checkout'), $this->payload($id))->assertSessionHasNoErrors();
        $messenger = app(GroupWa::class);
        $this->assertCount(0, $messenger->sent);
        $url = route('billing.otorisasi-maintenance.'.$type.'.approve', 1);
        $this->post($url)->assertSessionHas('success')->assertSessionMissing('warning');
        $this->assertCount(1, $messenger->sent);
        $notification = $messenger->sent[0];
        $this->assertSame('uj-ditahan-test', $notification['to']);
        $this->assertSame(0, $notification['transaction_level']);
        $this->assertStringContainsString($type === 'aki' ? '*FORM PENGGANTIAN AKI*' : '*FORM PENGGANTIAN FILTER & OLI MESIN*', $notification['text']);
        $this->assertStringContainsString('Rp. 150.000', $notification['text']);
        $this->assertStringContainsString('BCA', $notification['text']);
        $this->assertStringContainsString('123456', $notification['text']);
        $this->assertStringContainsString('Bengkel', $notification['text']);
        $this->assertStringContainsString("Total UJ Ditahan Sparepart: A01\nRp. 50.000", $notification['text']);
        $this->assertStringContainsString("Total UJ Ditahan Ban: A01\nRp. 70.000", $notification['text']);
        $this->assertStringContainsString("Grand Total UJ Ditahan: A01\nRp. 120.000", $notification['text']);
        $this->assertStringNotContainsString('Modal Investor', $notification['text']);
        $this->assertStringNotContainsString('999.000', $notification['text']);
        $this->post($url)->assertSessionHas('error');
        $this->assertCount(1, $messenger->sent);
    }

    #[TestWith(['aki'])]
    #[TestWith(['filter-oli'])]
    public function test_notification_failure_keeps_approved_sparepart_payment_and_warns_against_retry(string $type): void
    {
        $user = $this->actor('admin');
        $this->balance(8, 200000);
        $id = $this->cart($user, $type);
        $this->post(route('billing.form-maintenance.'.$type.'.checkout'), $this->payload($id))->assertSessionHasNoErrors();
        app(GroupWa::class)->fail = true;
        $this->post(route('billing.otorisasi-maintenance.'.$type.'.approve', 1))->assertSessionHas('success')->assertSessionHas('warning');
        $this->assertEquals(50000, DB::table('uj_ditahans')->sum('saldo'));
        $table = $type === 'aki' ? 'aki_ganti_invoices' : 'filter_oli_ganti_invoices';
        $this->assertDatabaseHas($table, ['id' => 1, 'status' => 'approved']);
    }

    #[TestWith(['aki'])]
    #[TestWith(['filter-oli'])]
    public function test_uj_sparepart_notification_is_also_sent_to_vehicle_driver(string $type): void
    {
        DB::table('drivers')->insert(['id' => 1, 'no_hp' => '0812-3456-7890']);
        DB::table('vehicles')->where('id', 1)->update(['driver_id' => 1]);
        $user = $this->actor('admin');
        $this->balance(8, 200000);
        $id = $this->cart($user, $type);
        $this->post(route('billing.form-maintenance.'.$type.'.checkout'), $this->payload($id))->assertSessionHasNoErrors();
        $this->post(route('billing.otorisasi-maintenance.'.$type.'.approve', 1))->assertSessionHas('success')->assertSessionMissing('warning');
        $sent = app(GroupWa::class)->sent;
        $this->assertCount(2, $sent);
        $this->assertSame(['uj-ditahan-test', '081234567890'], array_column($sent, 'to'));
        $this->assertSame($sent[0]['text'], $sent[1]['text']);
        $this->assertSame(0, $sent[1]['transaction_level']);
    }

    #[TestWith([null])]
    #[TestWith([''])]
    #[TestWith(['-'])]
    #[TestWith(['0'])]
    #[TestWith(['abcdefghij'])]
    #[TestWith(['1234567890'])]
    #[TestWith(['0812ABC567890'])]
    #[TestWith(['021234567890'])]
    #[TestWith(['081234567890999999'])]
    public function test_invalid_driver_phone_is_skipped_but_group_still_notified(?string $phone): void
    {
        DB::table('drivers')->insert(['id' => 1, 'no_hp' => $phone]);
        DB::table('vehicles')->where('id', 1)->update(['driver_id' => 1]);
        $user = $this->actor('admin');
        $this->balance(8, 200000);
        $id = $this->cart($user);
        $this->post(route('billing.form-maintenance.aki.checkout'), $this->payload($id))->assertSessionHasNoErrors();
        $this->post(route('billing.otorisasi-maintenance.aki.approve', 1))->assertSessionHas('success')->assertSessionMissing('warning');
        $sent = app(GroupWa::class)->sent;
        $this->assertCount(1, $sent);
        $this->assertSame('uj-ditahan-test', $sent[0]['to']);
    }

    #[TestWith(['+62 812-3456-7890'])]
    #[TestWith(['6281234567890'])]
    public function test_group_failure_does_not_prevent_sending_to_valid_driver_phone(string $phone): void
    {
        DB::table('drivers')->insert(['id' => 1, 'no_hp' => $phone]);
        DB::table('vehicles')->where('id', 1)->update(['driver_id' => 1]);
        $user = $this->actor('admin');
        $this->balance(8, 200000);
        $id = $this->cart($user);
        $this->post(route('billing.form-maintenance.aki.checkout'), $this->payload($id))->assertSessionHasNoErrors();
        app(GroupWa::class)->failTargets = ['uj-ditahan-test'];
        $this->post(route('billing.otorisasi-maintenance.aki.approve', 1))->assertSessionHas('success')->assertSessionHas('warning');
        $sent = app(GroupWa::class)->sent;
        $this->assertCount(1, $sent);
        $this->assertSame('6281234567890', $sent[0]['to']);
        $this->assertDatabaseHas('aki_ganti_invoices', ['id' => 1, 'status' => 'approved']);
    }

    #[TestWith(['aki', 'user', 'uj_ditahan'])]
    #[TestWith(['aki', 'user', 'kas_besar'])]
    #[TestWith(['aki', 'user', 'dibayar_sendiri'])]
    #[TestWith(['aki', 'admin', 'uj_ditahan'])]
    #[TestWith(['aki', 'admin', 'kas_besar'])]
    #[TestWith(['aki', 'admin', 'dibayar_sendiri'])]
    #[TestWith(['aki', 'su', 'uj_ditahan'])]
    #[TestWith(['aki', 'su', 'kas_besar'])]
    #[TestWith(['aki', 'su', 'dibayar_sendiri'])]
    #[TestWith(['aki', 'user', 'uj_ditahan_ban'])]
    #[TestWith(['filter-oli', 'user', 'uj_ditahan'])]
    #[TestWith(['filter-oli', 'user', 'kas_besar'])]
    #[TestWith(['filter-oli', 'user', 'dibayar_sendiri'])]
    #[TestWith(['filter-oli', 'admin', 'uj_ditahan'])]
    #[TestWith(['filter-oli', 'admin', 'kas_besar'])]
    #[TestWith(['filter-oli', 'admin', 'dibayar_sendiri'])]
    #[TestWith(['filter-oli', 'su', 'uj_ditahan'])]
    #[TestWith(['filter-oli', 'su', 'kas_besar'])]
    #[TestWith(['filter-oli', 'su', 'dibayar_sendiri'])]
    #[TestWith(['filter-oli', 'user', 'uj_ditahan_ban'])]
    public function test_payment_methods_follow_role_in_both_form_and_checkout(string $type, string $role, string $payment): void
    {
        $actor = $this->actor($role);
        $this->balance(10, 200000);
        $id = $this->cart($actor, $type);
        $payload = array_replace($this->payload($id), ['pembayaran' => $payment]);
        $cartTable = $type === 'aki' ? 'aki_ganti_carts' : 'filter_oli_ganti_carts';
        $invoiceTable = $type === 'aki' ? 'aki_ganti_invoices' : 'filter_oli_ganti_invoices';
        $expected = $role === 'user' ? ['uj_ditahan'] : ['uj_ditahan', 'kas_besar', 'dibayar_sendiri'];

        $html = $this->get(route('billing.form-maintenance.'.$type.'.confirm'))->assertOk()->getContent();
        $document = new \DOMDocument;
        $document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $options = (new \DOMXPath($document))->query('//select[@name="pembayaran"]/option[not(@disabled)]/@value');
        $visible = [];
        foreach ($options as $option) {
            if ($option->nodeValue !== '') {
                $visible[] = $option->nodeValue;
            }
        }
        sort($visible);
        $sortedExpected = $expected;
        sort($sortedExpected);
        $this->assertSame($sortedExpected, $visible);
        $response = $this->post(route('billing.form-maintenance.'.$type.'.checkout'), $payload);
        if (! in_array($payment, $expected, true)) {
            $response->assertSessionHasErrors('pembayaran');
            $this->assertDatabaseCount($invoiceTable, 0);
            $this->assertDatabaseCount($cartTable, 1);
        } else {
            $response->assertSessionHasNoErrors()->assertSessionHas('success');
            $this->assertDatabaseHas($invoiceTable, ['pembayaran' => $payment, 'status' => 'pending']);
        }
    }
}
