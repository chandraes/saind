<?php

namespace Tests\Feature;

use App\Models\FilterOliGantiCart;
use App\Models\FilterOliGantiInvoice;
use App\Models\FilterOliGantiInvoiceDetail;
use App\Models\FilterOliLog;
use App\Models\KategoriFilterOliMesin;
use App\Models\Transaksi;
use App\Models\User;
use App\Services\FilterOliRitaseService;
use Carbon\CarbonInterface;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class FilterOliGantiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('role');
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('remember_token')->nullable();
            $table->timestamps();
        });
        Schema::create('vendors', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
        });
        Schema::create('vehicles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vendor_id')->nullable();
            $table->string('nomor_lambung');
            $table->string('status')->default('aktif');
        });
        Schema::create('rutes', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->decimal('jarak', 10, 1);
        });
        Schema::create('kas_uang_jalans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vehicle_id');
            $table->foreignId('rute_id');
            $table->integer('nomor_uang_jalan');
        });
        Schema::create('transaksis', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kas_uang_jalan_id');
            $table->boolean('void')->default(false);
            $table->integer('status')->default(1);
            $table->timestamps();
        });
        foreach (['kas_besars', 'kas_vendors'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->string('tanggal')->nullable();
                $table->string('uraian')->nullable();
                foreach (['vendor_id', 'vehicle_id', 'jenis_transaksi_id', 'ban_ganti_invoice_id', 'aki_ganti_invoice_id'] as $column) {
                    $table->unsignedBigInteger($column)->nullable();
                }
                foreach (['saldo', 'modal_investor_terakhir', 'nominal_transaksi', 'pinjaman', 'sisa'] as $column) {
                    $table->decimal($column, 15, 2)->default(0);
                }
                foreach (['transfer_ke', 'no_rekening', 'bank'] as $column) {
                    $table->string($column)->nullable();
                }
                $table->timestamps();
            });
        }
        (require database_path('migrations/2026_10_04_094812_create_kategori_filter_oli_mesins_table.php'))->up();
        foreach ([
            '2026_10_04_103058_create_filter_oli_logs_table.php',
            '2026_10_04_103100_create_filter_oli_ganti_carts_table.php',
            '2026_10_04_103101_create_filter_oli_ganti_invoices_table.php',
            '2026_10_04_103102_create_filter_oli_ganti_invoice_details_table.php',
            '2026_10_04_103103_add_filter_oli_ganti_invoice_id_to_maintenance_ledgers.php',
            '2026_10_04_113741_create_filter_oli_log_transaksis_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        View::share(['global_app_name' => 'SAIND', 'global_app_favicon' => '', 'global_app_nav_bg' => '#212529', 'global_app_logo' => '']);
        $this->withoutVite();
    }

    public function test_recap_shows_only_processed_invoices_and_summarizes_filtered_results(): void
    {
        $user = $this->loginAs();
        $vehicle = $this->vehicle();
        $approved = FilterOliGantiInvoice::factory()->create(['user_id' => $user->id, 'vehicle_id' => $vehicle, 'no_invoice' => 'FO-REPORT-YES', 'status' => 'approved', 'pembayaran' => 'kas_besar', 'total_nominal' => 125000]);
        FilterOliGantiInvoice::factory()->create(['user_id' => $user->id, 'vehicle_id' => $vehicle, 'no_invoice' => 'FO-REPORT-NO', 'status' => 'rejected']);
        FilterOliGantiInvoice::factory()->create(['user_id' => $user->id, 'vehicle_id' => $vehicle, 'no_invoice' => 'FO-REPORT-PENDING', 'status' => 'pending']);
        FilterOliGantiInvoice::factory()->create(['user_id' => $user->id, 'vehicle_id' => $vehicle, 'no_invoice' => 'FO-REPORT-OLD', 'status' => 'approved', 'created_at' => now()->subMonths(2)]);

        $this->get(route('rekap.maintenance.filter-oli'))->assertOk()->assertSee('FO-REPORT-YES')->assertSee('FO-REPORT-NO')->assertDontSee('FO-REPORT-PENDING')->assertDontSee('FO-REPORT-OLD')->assertViewHas('summary', fn (array $summary): bool => $summary['total'] === 2 && $summary['approved'] === 1 && $summary['rejected'] === 1 && (float) $summary['cash'] === 125000.0);
        $this->get(route('rekap.maintenance.filter-oli', ['status' => 'approved', 'pembayaran' => 'kas_besar']))->assertOk()->assertSee($approved->no_invoice)->assertDontSee('FO-REPORT-NO')->assertViewHas('summary', fn (array $summary): bool => $summary['total'] === 1);
        $this->get(route('rekap.maintenance.filter-oli.show', $approved->id))->assertOk()->assertSee(route('rekap.maintenance.filter-oli'), false);
    }

    public function test_recap_rejects_pending_details_invalid_filters_and_reversed_dates(): void
    {
        $user = $this->loginAs();
        $invoice = FilterOliGantiInvoice::factory()->create(['user_id' => $user->id, 'vehicle_id' => $this->vehicle()]);
        $this->get(route('rekap.maintenance.filter-oli.show', $invoice->id))->assertNotFound();
        $this->get(route('rekap.maintenance.filter-oli', ['start_date' => '2026-10-10', 'end_date' => '2026-10-01']))->assertSessionHasErrors('end_date');
        $this->get(route('rekap.maintenance.filter-oli', ['status' => 'pending', 'pembayaran' => 'invalid']))->assertSessionHasErrors(['status', 'pembayaran']);
    }

    public function test_statistics_uses_latest_log_per_category_and_snapshot_limit(): void
    {
        $this->loginAs();
        $vehicle = $this->vehicle();
        $category = KategoriFilterOliMesin::factory()->create(['nama' => 'Filter Statistik', 'limit_ritase' => 99]);
        FilterOliLog::factory()->create(['vehicle_id' => $vehicle, 'kategori_filter_oli_mesin_id' => $category->id, 'merk' => 'Merek Lama', 'created_at' => now()->subDays(10)]);
        $log = FilterOliLog::factory()->create(['vehicle_id' => $vehicle, 'kategori_filter_oli_mesin_id' => $category->id, 'merk' => '<script>alert(1)</script>', 'limit_ritase' => 10, 'ritase' => 10, 'created_at' => now()->subDay()]);
        FilterOliLog::factory()->create(['vehicle_id' => $this->vehicle(), 'kategori_filter_oli_mesin_id' => $category->id, 'merk' => 'Kendaraan Lain']);
        $response = $this->get(route('statistik.filter-oli', ['vehicle_id' => $vehicle]));
        $response->assertOk()->assertSee('Mencapai limit')->assertSee('10 rit')->assertSee('<script>alert(1)</script>')->assertDontSee('<script>alert(1)</script>', false)->assertDontSee('Merek Lama')->assertDontSee('Kendaraan Lain')->assertViewHas('dueCount', 1)->assertViewHas('logs', fn ($logs): bool => $logs->count() === 1 && $logs->first()->id === $log->id);
        $this->get(route('statistik.filter-oli'))->assertOk()->assertSee('Pilih kendaraan');
    }

    public function test_statistics_history_is_scoped_to_vehicle_and_category(): void
    {
        $this->loginAs();
        $vehicle = $this->vehicle();
        $category = KategoriFilterOliMesin::factory()->create();
        FilterOliLog::factory()->create(['vehicle_id' => $vehicle, 'kategori_filter_oli_mesin_id' => $category->id, 'merk' => 'Histori Tepat']);
        FilterOliLog::factory()->create(['vehicle_id' => $this->vehicle(), 'kategori_filter_oli_mesin_id' => $category->id, 'merk' => 'Histori Unit Lain']);
        FilterOliLog::factory()->create(['vehicle_id' => $vehicle, 'merk' => 'Histori Kategori Lain']);
        $this->get(route('statistik.filter-oli.histori', [$vehicle, $category->id]))->assertOk()->assertSee('Histori Tepat')->assertDontSee('Histori Unit Lain')->assertDontSee('Histori Kategori Lain');
        $this->get(route('statistik.filter-oli', ['vehicle_id' => 999999]))->assertSessionHasErrors('vehicle_id');
        $this->get(route('statistik.filter-oli.histori', [$vehicle, 999999]))->assertNotFound();
    }

    public function test_reports_require_authentication_and_follow_existing_statistics_role_access(): void
    {
        $this->get(route('statistik.filter-oli'))->assertRedirect(route('login'));
        $this->get(route('rekap.maintenance.filter-oli'))->assertRedirect(route('login'));
        $this->loginAs('vendor');
        $this->get(route('statistik.filter-oli'))->assertOk();
        $this->get(route('rekap.maintenance.filter-oli'))->assertForbidden();
    }

    public function test_statistics_only_lists_and_accepts_vehicles_that_are_not_inactive(): void
    {
        $this->loginAs();
        $active = $this->vehicle();
        $inactive = $this->vehicle();
        DB::table('vehicles')->where('id', $inactive)->update(['status' => 'nonaktif', 'nomor_lambung' => 'UNIT-NONAKTIF']);
        $this->get(route('statistik.filter-oli'))->assertOk()->assertDontSee('UNIT-NONAKTIF')->assertViewHas('vehicles', fn ($vehicles): bool => $vehicles->count() === 1 && $vehicles->first()->id === $active);
        $this->get(route('statistik.filter-oli', ['vehicle_id' => $inactive]))->assertSessionHasErrors('vehicle_id');
    }

    public function test_ritase_modal_shows_only_recorded_transactions_for_the_selected_log(): void
    {
        $this->loginAs();
        $vehicle = $this->vehicle();
        $log = FilterOliLog::factory()->create(['vehicle_id' => $vehicle, 'created_at' => today()->subDays(3)]);
        $one = $this->transaction($vehicle, 51, today()->subDays(2));
        $half = $this->transaction($vehicle, 50, today()->subDay());
        $this->transaction($vehicle, 100, today()->subDays(4));
        $this->transaction($vehicle, 100, today(), true);
        $this->transaction($this->vehicle(), 100, today());
        $this->get(route('statistik.filter-oli', ['vehicle_id' => $vehicle]))->assertOk()->assertSee('data-bs-target="#filterRitaseModal"', false)->assertSee(route('statistik.filter-oli.transaksi-ritase', $log->id), false);
        $this->get(route('statistik.filter-oli.transaksi-ritase', $log->id))->assertOk()->assertSee('1,5 rit')->assertViewHas('transactions', fn ($rows): bool => $rows->pluck('id')->all() === [$one->id, $half->id]);
        $this->get(route('statistik.filter-oli.transaksi-ritase', 999999))->assertNotFound();
    }

    #[TestWith(['su'])]
    #[TestWith(['admin'])]
    public function test_history_date_edit_reallocates_transaction_audits_and_synchronizes_invoice_detail(string $role): void
    {
        $user = $this->loginAs($role);
        $vehicle = $this->vehicle();
        $category = KategoriFilterOliMesin::factory()->create();
        $old = FilterOliLog::factory()->create(['vehicle_id' => $vehicle, 'kategori_filter_oli_mesin_id' => $category->id, 'created_at' => today()->subDays(10)]);
        $new = FilterOliLog::factory()->create(['vehicle_id' => $vehicle, 'kategori_filter_oli_mesin_id' => $category->id, 'created_at' => today()->subDays(5)]);
        $invoice = FilterOliGantiInvoice::factory()->create(['user_id' => $user->id, 'vehicle_id' => $vehicle, 'status' => 'approved']);
        $detail = FilterOliGantiInvoiceDetail::factory()->create(['filter_oli_ganti_invoice_id' => $invoice->id, 'filter_oli_log_id' => $new->id, 'kategori_filter_oli_mesin_id' => $category->id, 'created_at' => $new->created_at]);
        $transaction = $this->transaction($vehicle, 100, today()->subDays(7));
        $this->transaction($vehicle, 25, today()->subDays(3));
        $this->assertDatabaseHas('filter_oli_logs', ['id' => $old->id, 'ritase' => 1]);
        $date = today()->subDays(8)->toDateString();
        $this->get(route('statistik.filter-oli.histori', [$vehicle, $category->id]))->assertOk()->assertSee('Edit tanggal')->assertSee('Hapus');
        $this->patch(route('statistik.filter-oli.histori.update', $new->id), ['tanggal_ganti' => $date])->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('filter_oli_logs', ['id' => $old->id, 'ritase' => 0]);
        $this->assertDatabaseHas('filter_oli_logs', ['id' => $new->id, 'ritase' => 1.5, 'created_at' => $date.' 00:00:00']);
        $this->assertDatabaseHas('filter_oli_ganti_invoice_details', ['id' => $detail->id, 'ritase' => 1.5, 'created_at' => $date.' 00:00:00']);
        $this->assertDatabaseMissing('filter_oli_log_transaksis', ['filter_oli_log_id' => $old->id, 'transaksi_id' => $transaction->id]);
        $this->assertDatabaseHas('filter_oli_log_transaksis', ['filter_oli_log_id' => $new->id, 'transaksi_id' => $transaction->id]);
        $this->assertDatabaseCount('filter_oli_log_transaksis', 2);
    }

    #[TestWith(['su'])]
    #[TestWith(['admin'])]
    public function test_history_deletion_recalculates_previous_period_and_preserves_invoice(string $role): void
    {
        $user = $this->loginAs($role);
        $vehicle = $this->vehicle();
        $category = KategoriFilterOliMesin::factory()->create();
        $old = FilterOliLog::factory()->create(['vehicle_id' => $vehicle, 'kategori_filter_oli_mesin_id' => $category->id, 'created_at' => today()->subDays(10)]);
        $new = FilterOliLog::factory()->create(['vehicle_id' => $vehicle, 'kategori_filter_oli_mesin_id' => $category->id, 'created_at' => today()->subDays(5)]);
        $invoice = FilterOliGantiInvoice::factory()->create(['user_id' => $user->id, 'vehicle_id' => $vehicle, 'status' => 'approved', 'total_nominal' => 75000]);
        $detail = FilterOliGantiInvoiceDetail::factory()->create(['filter_oli_ganti_invoice_id' => $invoice->id, 'filter_oli_log_id' => $new->id, 'kategori_filter_oli_mesin_id' => $category->id]);
        $transaction = $this->transaction($vehicle, 100, today()->subDays(3));
        $this->delete(route('statistik.filter-oli.histori.destroy', $new->id))->assertRedirect()->assertSessionHas('success');
        $this->assertModelMissing($new);
        $this->assertDatabaseHas('filter_oli_logs', ['id' => $old->id, 'ritase' => 1]);
        $this->assertDatabaseMissing('filter_oli_log_transaksis', ['filter_oli_log_id' => $new->id]);
        $this->assertDatabaseHas('filter_oli_log_transaksis', ['filter_oli_log_id' => $old->id, 'transaksi_id' => $transaction->id]);
        $this->assertDatabaseHas('filter_oli_ganti_invoice_details', ['id' => $detail->id, 'filter_oli_log_id' => null, 'ritase' => 1]);
        $this->assertDatabaseHas('filter_oli_ganti_invoices', ['id' => $invoice->id, 'status' => 'approved', 'total_nominal' => 75000]);
        $this->get(route('rekap.maintenance.filter-oli.show', $invoice->id))->assertOk()->assertViewHas('invoice', fn ($record): bool => (float) $record->details->first()->ritase === 1.0);
    }

    public function test_history_mutations_are_forbidden_for_user_and_validate_dates_for_admin(): void
    {
        $this->loginAs();
        $log = FilterOliLog::factory()->create(['vehicle_id' => $this->vehicle(), 'created_at' => today()->subDay()]);
        $this->get(route('statistik.filter-oli.histori', [$log->vehicle_id, $log->kategori_filter_oli_mesin_id]))->assertOk()->assertDontSee('Edit tanggal')->assertDontSee('Hapus');
        $this->patch(route('statistik.filter-oli.histori.update', $log->id), ['tanggal_ganti' => today()->toDateString()])->assertForbidden();
        $this->delete(route('statistik.filter-oli.histori.destroy', $log->id))->assertForbidden();
        $this->assertModelExists($log);
        $this->loginAs('admin');
        $this->patch(route('statistik.filter-oli.histori.update', $log->id), [])->assertSessionHasErrors(['tanggal_ganti' => 'Tanggal ganti wajib diisi.']);
        $this->patch(route('statistik.filter-oli.histori.update', $log->id), ['tanggal_ganti' => 'invalid'])->assertSessionHasErrors('tanggal_ganti');
        $this->patch(route('statistik.filter-oli.histori.update', $log->id), ['tanggal_ganti' => today()->addDay()->toDateString()])->assertSessionHasErrors(['tanggal_ganti' => 'Tanggal ganti tidak boleh setelah hari ini.']);
        $this->delete(route('statistik.filter-oli.histori.destroy', 999999))->assertNotFound();
    }

    public function test_failed_history_recalculation_rolls_back_date_change(): void
    {
        $this->loginAs('admin');
        $log = FilterOliLog::factory()->create(['vehicle_id' => $this->vehicle(), 'created_at' => today()->subDays(3)]);
        $this->mock(FilterOliRitaseService::class, fn ($mock) => $mock->shouldReceive('refreshVehicle')->once()->andThrow(new \RuntimeException('Calculation failed')));
        $this->patch(route('statistik.filter-oli.histori.update', $log->id), ['tanggal_ganti' => today()->toDateString()])->assertRedirect()->assertSessionHas('error');
        $this->assertDatabaseHas('filter_oli_logs', ['id' => $log->id, 'created_at' => today()->subDays(3)->format('Y-m-d').' 00:00:00']);
    }

    private function loginAs(string $role = 'user'): User
    {
        $user = User::factory()->create(['role' => $role, 'password' => 'password']);
        $this->actingAs($user);

        return $user;
    }

    private function vehicle(): int
    {
        $vendorId = DB::table('vendors')->insertGetId(['nama' => 'Vendor Uji']);

        return DB::table('vehicles')->insertGetId(['vendor_id' => $vendorId, 'nomor_lambung' => '101', 'status' => 'aktif']);
    }

    private function cart(User $user, int $vehicleId): FilterOliGantiCart
    {
        return FilterOliGantiCart::factory()->create(['user_id' => $user->id, 'vehicle_id' => $vehicleId]);
    }

    private function transaction(int $vehicleId, float $distance, CarbonInterface $date, bool $void = false): Transaksi
    {
        $ruteId = DB::table('rutes')->insertGetId(['nama' => 'Rute Uji', 'jarak' => $distance]);
        $kujId = DB::table('kas_uang_jalans')->insertGetId(['vehicle_id' => $vehicleId, 'rute_id' => $ruteId, 'nomor_uang_jalan' => 123]);

        return Transaksi::factory()->create(['kas_uang_jalan_id' => $kujId, 'void' => $void, 'created_at' => $date]);
    }

    private function checkoutData(FilterOliGantiCart $cart): array
    {
        return ['vehicle_id' => $cart->vehicle_id, 'pembayaran' => 'dibayar_sendiri',
            'items' => [$cart->id => ['tanggal_ganti' => today()->subDays(3)->format('Y-m-d')]]];
    }

    public function test_checkout_waits_for_approval_and_keeps_selected_date_and_limit(): void
    {
        $user = $this->loginAs();
        $cart = $this->cart($user, $this->vehicle());
        $limit = $cart->kategori->limit_ritase;
        $data = $this->checkoutData($cart);
        $this->get(route('billing.form-maintenance.filter-oli'))->assertOk()->assertSee('table-success', false);
        $this->get(route('billing.form-maintenance.filter-oli.confirm'))->assertSee('items['.$cart->id.'][tanggal_ganti]', false);
        $this->post(route('billing.form-maintenance.filter-oli.checkout'), $data)->assertRedirect(route('billing.form-maintenance.filter-oli'));
        $invoice = FilterOliGantiInvoice::firstOrFail();
        $this->assertDatabaseCount('filter_oli_logs', 0);
        $this->assertDatabaseCount('filter_oli_ganti_carts', 0);
        $this->assertSame('pending', $invoice->status);
        $this->assertSame($data['items'][$cart->id]['tanggal_ganti'], $invoice->details->first()->created_at->format('Y-m-d'));
        KategoriFilterOliMesin::whereKey($cart->kategori_filter_oli_mesin_id)->update(['limit_ritase' => 999]);
        $admin = $this->loginAs('admin');
        $this->get(route('billing.otorisasi-maintenance.filter-oli'))->assertSee($invoice->no_invoice)->assertSee('table-success', false);
        $this->post(route('billing.otorisasi-maintenance.filter-oli.approve', $invoice->id))->assertSessionHas('success');
        $log = FilterOliLog::firstOrFail();
        $this->assertSame($limit, $log->limit_ritase);
        $this->assertSame($data['items'][$cart->id]['tanggal_ganti'], $log->created_at->format('Y-m-d'));
        $this->assertDatabaseHas('filter_oli_ganti_invoices', ['id' => $invoice->id, 'status' => 'approved', 'authorized_by' => $admin->id]);
        $this->post(route('billing.otorisasi-maintenance.filter-oli.approve', $invoice->id))->assertSessionHas('error');
        $this->post(route('billing.otorisasi-maintenance.filter-oli.reject', $invoice->id))->assertSessionHas('error');
        $this->assertDatabaseCount('filter_oli_logs', 1);
        $this->assertDatabaseHas('filter_oli_ganti_invoices', ['id' => $invoice->id, 'status' => 'approved']);
    }

    public function test_reject_does_not_create_history_or_debit_cash(): void
    {
        $user = $this->loginAs();
        $cart = $this->cart($user, $this->vehicle());
        $this->post(route('billing.form-maintenance.filter-oli.checkout'), $this->checkoutData($cart));
        $invoice = FilterOliGantiInvoice::firstOrFail();
        $this->loginAs('su');
        $this->post(route('billing.otorisasi-maintenance.filter-oli.reject', $invoice->id))->assertSessionHas('success');
        $this->post(route('billing.otorisasi-maintenance.filter-oli.approve', $invoice->id))->assertSessionHas('error');
        $this->assertDatabaseHas('filter_oli_ganti_invoices', ['id' => $invoice->id, 'status' => 'rejected']);
        $this->assertDatabaseCount('filter_oli_logs', 0);
        $this->assertDatabaseCount('kas_besars', 0);
    }

    public function test_cart_add_rejects_duplicate_categories_and_other_vehicle(): void
    {
        $user = $this->loginAs();
        $vehicleId = $this->vehicle();
        $category = KategoriFilterOliMesin::factory()->create();
        $data = ['vehicle_id' => $vehicleId, 'kategori_filter_oli_mesin_id' => $category->id, 'merk' => 'SAKURA', 'kondisi' => 100, 'tanggal_ganti' => today()->subDays(2)->format('Y-m-d'), 'ritase' => 99];
        $this->post(route('billing.form-maintenance.filter-oli.cart.add'), $data)->assertSessionHas('success');
        $this->post(route('billing.form-maintenance.filter-oli.cart.add'), $data)->assertSessionHasErrors('kategori_filter_oli_mesin_id');
        $data['vehicle_id'] = $this->vehicle();
        $this->post(route('billing.form-maintenance.filter-oli.cart.add'), $data)->assertSessionHasErrors('vehicle_id');
        $this->assertDatabaseHas('filter_oli_ganti_carts', ['user_id' => $user->id, 'vehicle_id' => $vehicleId, 'ritase' => 0, 'created_at' => today()->subDays(2)->format('Y-m-d').' 00:00:00']);
        $this->assertDatabaseCount('filter_oli_ganti_carts', 1);
    }

    public function test_carts_are_isolated_and_cannot_checkout_another_users_items(): void
    {
        $other = $this->loginAs();
        $cart = $this->cart($other, $this->vehicle());
        $user = $this->loginAs();
        $ownCart = $this->cart($user, $this->vehicle());
        $this->delete(route('billing.form-maintenance.filter-oli.cart.delete', $cart->id))->assertNotFound();
        $this->post(route('billing.form-maintenance.filter-oli.checkout'), $this->checkoutData($cart))->assertSessionHasErrors('items');
        $this->assertDatabaseCount('filter_oli_ganti_invoices', 0);
        $this->delete(route('billing.form-maintenance.filter-oli.cart.clear'))->assertSessionHas('success');
        $this->assertModelExists($cart);
        $this->assertModelMissing($ownCart);
    }

    public function test_invalid_inputs_and_future_replacement_date_preserve_cart(): void
    {
        $user = $this->loginAs();
        $cart = $this->cart($user, $this->vehicle());
        $data = $this->checkoutData($cart);
        $data['items'][$cart->id]['tanggal_ganti'] = today()->addDay()->format('Y-m-d');
        $this->post(route('billing.form-maintenance.filter-oli.checkout'), $data)->assertSessionHasErrors('items.'.$cart->id.'.tanggal_ganti');
        $this->post(route('billing.form-maintenance.filter-oli.cart.add'), [
            'vehicle_id' => $cart->vehicle_id, 'kategori_filter_oli_mesin_id' => $cart->kategori_filter_oli_mesin_id,
            'merk' => '', 'kondisi' => 101, 'ritase' => -1,
        ])->assertSessionHasErrors(['merk', 'kondisi', 'tanggal_ganti']);
        $this->assertModelExists($cart);
        $this->assertDatabaseCount('filter_oli_ganti_invoices', 0);
    }

    public function test_guest_and_operator_cannot_authorize(): void
    {
        $this->get(route('billing.form-maintenance.filter-oli'))->assertRedirect(route('login'));
        $this->loginAs();
        $this->get(route('billing.otorisasi-maintenance.filter-oli'))->assertForbidden();
        $this->post(route('billing.otorisasi-maintenance.filter-oli.approve', 1))->assertForbidden();
        $this->post(route('billing.otorisasi-maintenance.filter-oli.reject', 1))->assertForbidden();
        $this->loginAs('vendor');
        $this->get(route('billing.form-maintenance.filter-oli'))->assertForbidden();
    }

    public function test_kas_besar_approval_debits_once_and_records_vendor_debt(): void
    {
        config(['maintenance.filter_oli.kas_besar_enabled' => true]);
        $user = $this->loginAs();
        $cart = $this->cart($user, $this->vehicle());
        $data = array_replace($this->checkoutData($cart), ['pembayaran' => 'kas_besar', 'total_nominal' => 500000,
            'nama_bank' => 'BCA', 'nomor_rekening' => '0123456', 'nama_rekening' => 'Vendor']);
        DB::table('kas_besars')->insert(['saldo' => 1000000]);
        $this->post(route('billing.form-maintenance.filter-oli.checkout'), $data)->assertSessionHas('success');
        $invoice = FilterOliGantiInvoice::firstOrFail();
        $this->loginAs('admin');
        $this->post(route('billing.otorisasi-maintenance.filter-oli.approve', $invoice->id))->assertSessionHas('success');
        $this->assertDatabaseHas('kas_besars', ['filter_oli_ganti_invoice_id' => $invoice->id, 'saldo' => 500000, 'nominal_transaksi' => 500000]);
        $this->assertDatabaseHas('kas_vendors', ['filter_oli_ganti_invoice_id' => $invoice->id, 'pinjaman' => 500000, 'sisa' => 500000]);
        $this->post(route('billing.otorisasi-maintenance.filter-oli.approve', $invoice->id))->assertSessionHas('error');
        $this->assertDatabaseCount('kas_besars', 2);
        $this->assertDatabaseCount('kas_vendors', 1);
    }

    public function test_insufficient_cash_rolls_back_logs_and_authorization(): void
    {
        config(['maintenance.filter_oli.kas_besar_enabled' => true]);
        $user = $this->loginAs();
        $cart = $this->cart($user, $this->vehicle());
        $data = array_replace($this->checkoutData($cart), ['pembayaran' => 'kas_besar', 'total_nominal' => 500000,
            'nama_bank' => 'BCA', 'nomor_rekening' => '0123456', 'nama_rekening' => 'Vendor']);
        $this->post(route('billing.form-maintenance.filter-oli.checkout'), $data);
        $invoice = FilterOliGantiInvoice::firstOrFail();
        $this->transaction($cart->vehicle_id, 80, today()->subDay());
        $this->loginAs('admin');
        $this->post(route('billing.otorisasi-maintenance.filter-oli.approve', $invoice->id))->assertSessionHas('error');
        $this->assertDatabaseCount('filter_oli_logs', 0);
        $this->assertDatabaseCount('filter_oli_log_transaksis', 0);
        $this->assertDatabaseCount('kas_besars', 0);
        $this->assertDatabaseCount('kas_vendors', 0);
        $this->assertDatabaseHas('filter_oli_ganti_invoices', ['id' => $invoice->id, 'status' => 'pending']);
        $this->assertDatabaseHas('filter_oli_ganti_invoice_details', ['filter_oli_ganti_invoice_id' => $invoice->id, 'filter_oli_log_id' => null]);
    }

    public function test_checkout_requires_bank_details_and_exact_cart_items(): void
    {
        config(['maintenance.filter_oli.kas_besar_enabled' => true]);
        $user = $this->loginAs();
        $cart = $this->cart($user, $this->vehicle());
        $data = $this->checkoutData($cart);
        $data['pembayaran'] = 'kas_besar';
        $this->post(route('billing.form-maintenance.filter-oli.checkout'), $data)
            ->assertSessionHasErrors(['total_nominal', 'nama_bank', 'nomor_rekening', 'nama_rekening']);
        $data = $this->checkoutData($cart);
        $data['items'][999] = ['tanggal_ganti' => today()->format('Y-m-d')];
        $this->post(route('billing.form-maintenance.filter-oli.checkout'), $data)->assertSessionHasErrors('items');
        $this->assertDatabaseCount('filter_oli_ganti_invoices', 0);
        $this->assertModelExists($cart);
    }

    public function test_each_item_preserves_its_own_date_brand_condition_and_ritase(): void
    {
        $user = $this->loginAs();
        $first = $this->cart($user, $this->vehicle());
        $second = FilterOliGantiCart::factory()->create([
            'user_id' => $user->id, 'vehicle_id' => $first->vehicle_id,
            'merk' => 'Merek Kedua', 'kondisi' => 75, 'ritase' => 0.5,
        ]);
        $this->transaction($first->vehicle_id, 25, today()->subDays(6));
        $data = $this->checkoutData($first);
        $data['items'][$second->id] = ['tanggal_ganti' => today()->subDays(7)->format('Y-m-d')];
        $this->post(route('billing.form-maintenance.filter-oli.checkout'), $data)->assertSessionHas('success');
        $invoice = FilterOliGantiInvoice::firstOrFail();
        $this->loginAs('admin');
        $this->post(route('billing.otorisasi-maintenance.filter-oli.approve', $invoice->id))->assertSessionHas('success');
        $this->assertDatabaseHas('filter_oli_logs', [
            'kategori_filter_oli_mesin_id' => $second->kategori_filter_oli_mesin_id,
            'merk' => 'Merek Kedua', 'kondisi' => 75, 'ritase' => 0.5,
            'created_at' => $data['items'][$second->id]['tanggal_ganti'].' 00:00:00',
        ]);
        $this->assertDatabaseCount('filter_oli_logs', 2);
    }

    public function test_nonexistent_invoice_cannot_be_authorized(): void
    {
        $this->loginAs('admin');
        $this->post(route('billing.otorisasi-maintenance.filter-oli.approve', 999))->assertNotFound();
        $this->post(route('billing.otorisasi-maintenance.filter-oli.reject', 999))->assertNotFound();
    }

    public function test_kas_besar_is_hidden_and_rejected_until_enabled(): void
    {
        $user = $this->loginAs();
        $cart = $this->cart($user, $this->vehicle());
        $this->get(route('billing.form-maintenance.filter-oli', ['vehicle_id' => $cart->vehicle_id]))
            ->assertSee('name="tanggal_ganti"', false)->assertDontSee('name="ritase"', false)->assertSee('maintenance-select', false);
        $this->get(route('billing.form-maintenance.filter-oli.confirm'))->assertDontSee('value="kas_besar"', false);
        $data = array_replace($this->checkoutData($cart), ['pembayaran' => 'kas_besar', 'total_nominal' => 500000,
            'nama_bank' => 'BCA', 'nomor_rekening' => '0123456', 'nama_rekening' => 'Vendor']);
        $this->post(route('billing.form-maintenance.filter-oli.checkout'), $data)->assertSessionHasErrors('pembayaran');
        $this->assertModelExists($cart);
        $this->assertDatabaseCount('filter_oli_ganti_invoices', 0);
        config(['maintenance.filter_oli.kas_besar_enabled' => true]);
        $this->get(route('billing.form-maintenance.filter-oli.confirm'))->assertSee('value="kas_besar"', false);
    }

    public function test_authorization_detail_compares_previous_replacement_and_locks_after_approval(): void
    {
        $user = $this->loginAs();
        $cart = $this->cart($user, $this->vehicle());
        FilterOliLog::factory()->create([
            'vehicle_id' => $cart->vehicle_id, 'kategori_filter_oli_mesin_id' => $cart->kategori_filter_oli_mesin_id,
            'merk' => 'Merek Sebelumnya', 'ritase' => 5, 'created_at' => today()->subDays(10),
        ]);
        $this->post(route('billing.form-maintenance.filter-oli.checkout'), $this->checkoutData($cart));
        $invoice = FilterOliGantiInvoice::firstOrFail();
        $detail = $invoice->details->first();
        $this->loginAs('admin');
        $this->get(route('billing.otorisasi-maintenance.filter-oli'))
            ->assertSee('Approve')->assertSee('Reject')->assertSee(route('billing.otorisasi-maintenance.filter-oli.show', $invoice->id));
        $this->get(route('billing.otorisasi-maintenance.filter-oli.show', $invoice->id))
            ->assertSee('Merek Sebelumnya')->assertSee('LAMA (DIGANTI)')->assertSee('BARU (DIPASANG)');
        $data = ['merk' => 'Merek Direvisi', 'kondisi' => 80, 'tanggal_ganti' => today()->subDays(2)->format('Y-m-d')];
        $this->patch(route('billing.otorisasi-maintenance.filter-oli.detail.update', $detail->id), $data)->assertSessionHas('success');
        $this->post(route('billing.otorisasi-maintenance.filter-oli.approve', $invoice->id))->assertSessionHas('success');
        $this->patch(route('billing.otorisasi-maintenance.filter-oli.detail.update', $detail->id), array_replace($data, ['merk' => 'Dilarang']))->assertSessionHas('error');
        $this->assertDatabaseHas('filter_oli_ganti_invoice_details', ['id' => $detail->id, 'merk' => 'Merek Direvisi']);
        $this->assertDatabaseHas('filter_oli_logs', ['merk' => 'Merek Direvisi', 'kondisi' => 80]);
    }

    public function test_billing_modal_shows_pending_counts_for_each_maintenance_type(): void
    {
        $user = $this->loginAs('admin');
        foreach (['rekap_gajis', 'customers', 'sponsors'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->string('nama')->nullable();
            });
        }
        foreach (['invoice_tagihans', 'invoice_bayars', 'invoice_bonuses', 'invoice_csrs'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->boolean('lunas')->default(false);
            });
        }
        foreach (['ban_ganti_invoices', 'aki_ganti_invoices'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->string('status');
            });
        }
        Schema::table('vendors', function (Blueprint $table): void {
            $table->string('nickname')->nullable();
            $table->string('status')->default('aktif');
        });
        DB::table('ban_ganti_invoices')->insert([['status' => 'pending'], ['status' => 'approved']]);
        DB::table('aki_ganti_invoices')->insert([['status' => 'pending'], ['status' => 'pending'], ['status' => 'rejected']]);
        FilterOliGantiInvoice::factory()->count(2)->create(['user_id' => $user->id, 'vehicle_id' => $this->vehicle()]);
        FilterOliGantiInvoice::factory()->create(['user_id' => $user->id, 'vehicle_id' => $this->vehicle(), 'status' => 'approved']);
        $this->get(route('billing.index'))->assertOk()
            ->assertViewHas('countBanPending', 1)->assertViewHas('countAkiPending', 2)
            ->assertViewHas('countFilterOliPending', 2)->assertViewHas('countOB', 5)
            ->assertSee('bg-danger">(1)', false)->assertSee('bg-danger">(2)', false)
            ->assertSee('maintenance-menu-link', false);
    }

    public function test_short_invoice_and_backdated_replacement_count_only_valid_vehicle_transactions(): void
    {
        $this->travelTo(today()->setTime(12, 0));
        $user = $this->loginAs();
        $cart = $this->cart($user, $this->vehicle());
        $from = today()->subDays(3);
        $half = $this->transaction($cart->vehicle_id, 50, $from);
        $full = $this->transaction($cart->vehicle_id, 51, today()->subDays(2));
        $todayTrip = $this->transaction($cart->vehicle_id, 10, today()->setTime(10, 0));
        $this->transaction($cart->vehicle_id, 80, $from->copy()->subSecond());
        $this->transaction($cart->vehicle_id, 80, today()->subDay(), true);
        $this->transaction($cart->vehicle_id, 80, now()->addDay());
        $this->transaction($this->vehicle(), 80, today()->subDay());
        $this->post(route('billing.form-maintenance.filter-oli.checkout'), $this->checkoutData($cart))->assertSessionHas('success');
        $invoice = FilterOliGantiInvoice::firstOrFail();
        $detail = $invoice->details->first();
        $this->assertSame('FO-'.str_pad((string) $invoice->id, 6, '0', STR_PAD_LEFT), $invoice->no_invoice);
        $this->assertSame('2.0', $detail->ritase);
        $this->loginAs('admin');
        $this->get(route('billing.otorisasi-maintenance.filter-oli.show', $invoice->id))
            ->assertSee('RITASE SAAT INI')->assertSee('LAMA (DIGANTI)')->assertSee('bg-warning', false)
            ->assertSee('Rute Uji')->assertSee('#'.$half->id)->assertDontSee('LIMIT ACUAN');
        $this->post(route('billing.otorisasi-maintenance.filter-oli.approve', $invoice->id))->assertSessionHas('success');
        $log = FilterOliLog::firstOrFail();
        $this->assertSame('2.0', $log->ritase);
        foreach ([$half->id => 0.5, $full->id => 1, $todayTrip->id => 0.5] as $id => $value) {
            $this->assertDatabaseHas('filter_oli_log_transaksis', ['filter_oli_log_id' => $log->id, 'transaksi_id' => $id, 'nilai_ritase' => $value]);
        }
        $this->assertDatabaseCount('filter_oli_log_transaksis', 3);
        $this->artisan('filter-oli:sync-ritase')->assertExitCode(0);
        $this->assertDatabaseCount('filter_oli_log_transaksis', 3);
        $this->assertSame('2.0', $log->fresh()->ritase);
    }

    public function test_editing_date_recalculates_and_approval_includes_later_transactions(): void
    {
        $this->travelTo(today()->setTime(12, 0));
        $user = $this->loginAs();
        $cart = $this->cart($user, $this->vehicle());
        $old = $this->transaction($cart->vehicle_id, 80, today()->subDay());
        $current = $this->transaction($cart->vehicle_id, 50, today()->setTime(9, 0));
        $this->post(route('billing.form-maintenance.filter-oli.checkout'), $this->checkoutData($cart));
        $invoice = FilterOliGantiInvoice::firstOrFail();
        $detail = $invoice->details->first();
        $this->assertSame('1.5', $detail->ritase);
        $this->loginAs('admin');
        $this->patch(route('billing.otorisasi-maintenance.filter-oli.detail.update', $detail->id), [
            'merk' => 'SAKURA', 'kondisi' => 100, 'tanggal_ganti' => today()->format('Y-m-d'),
        ])->assertSessionHas('success');
        $this->assertSame('0.5', $detail->fresh()->ritase);
        $later = $this->transaction($cart->vehicle_id, 80, today()->setTime(11, 0));
        $this->post(route('billing.otorisasi-maintenance.filter-oli.approve', $invoice->id))->assertSessionHas('success');
        $log = FilterOliLog::firstOrFail();
        $this->assertSame('1.5', $log->ritase);
        $this->assertDatabaseMissing('filter_oli_log_transaksis', ['filter_oli_log_id' => $log->id, 'transaksi_id' => $old->id]);
        $this->assertDatabaseHas('filter_oli_log_transaksis', ['filter_oli_log_id' => $log->id, 'transaksi_id' => $current->id]);
        $this->assertDatabaseHas('filter_oli_log_transaksis', ['filter_oli_log_id' => $log->id, 'transaksi_id' => $later->id]);
    }

    public function test_new_transactions_and_void_changes_keep_log_and_audit_in_sync(): void
    {
        $this->travelTo(today()->setTime(12, 0));
        $user = $this->loginAs();
        $cart = $this->cart($user, $this->vehicle());
        $this->post(route('billing.form-maintenance.filter-oli.checkout'), $this->checkoutData($cart));
        $invoice = FilterOliGantiInvoice::firstOrFail();
        $this->loginAs('admin');
        $this->post(route('billing.otorisasi-maintenance.filter-oli.approve', $invoice->id));
        $log = FilterOliLog::firstOrFail();
        $trip = $this->transaction($cart->vehicle_id, 80, now());
        $this->assertSame('1.0', $log->fresh()->ritase);
        $this->assertDatabaseCount('filter_oli_log_transaksis', 1);
        $trip->update(['void' => true]);
        $this->assertSame('0.0', $log->fresh()->ritase);
        $this->assertDatabaseCount('filter_oli_log_transaksis', 0);
        $trip->update(['void' => false]);
        $this->assertSame('1.0', $log->fresh()->ritase);
        $this->assertSame('1.0', $invoice->details()->first()->ritase);
        $this->assertDatabaseCount('filter_oli_log_transaksis', 1);
    }

    public function test_successive_replacements_allocate_each_transaction_to_its_own_period(): void
    {
        $this->travelTo(today()->setTime(12, 0));
        $user = $this->loginAs();
        $vehicleId = $this->vehicle();
        $category = KategoriFilterOliMesin::factory()->create();
        $older = FilterOliLog::factory()->create(['vehicle_id' => $vehicleId, 'kategori_filter_oli_mesin_id' => $category->id, 'created_at' => today()->subDays(10)]);
        $oldTrip = $this->transaction($vehicleId, 80, today()->subDays(5));
        $newTrip = $this->transaction($vehicleId, 50, today()->subDay());
        $cart = FilterOliGantiCart::factory()->create(['user_id' => $user->id, 'vehicle_id' => $vehicleId, 'kategori_filter_oli_mesin_id' => $category->id]);
        $this->post(route('billing.form-maintenance.filter-oli.checkout'), $this->checkoutData($cart));
        $invoice = FilterOliGantiInvoice::firstOrFail();
        $this->loginAs('admin');
        $this->post(route('billing.otorisasi-maintenance.filter-oli.approve', $invoice->id))->assertSessionHas('success');
        $newer = FilterOliLog::latest('id')->first();
        $this->assertSame('1.0', $older->fresh()->ritase);
        $this->assertSame('0.5', $newer->ritase);
        $this->assertDatabaseHas('filter_oli_log_transaksis', ['filter_oli_log_id' => $older->id, 'transaksi_id' => $oldTrip->id]);
        $this->assertDatabaseMissing('filter_oli_log_transaksis', ['filter_oli_log_id' => $older->id, 'transaksi_id' => $newTrip->id]);
        $this->assertDatabaseHas('filter_oli_log_transaksis', ['filter_oli_log_id' => $newer->id, 'transaksi_id' => $newTrip->id]);
    }
}
