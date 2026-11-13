<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class VendorOperationalBillingTest extends MobileApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        View::share(['global_app_name' => 'SAIND', 'global_app_favicon' => '', 'global_app_nav_bg' => '#212529', 'global_app_logo' => '']);
        Schema::table('vehicles', fn (Blueprint $table) => $table->unsignedBigInteger('driver_id')->nullable());
        foreach (['customers', 'drivers', 'rutes'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->string('nama');
                $table->integer('jarak')->nullable();
                $table->integer('tagihan_dari')->nullable();
            });
        }
        Schema::create('kas_uang_jalans', function (Blueprint $table): void {
            $table->id();
            foreach (['vendor_id', 'vehicle_id', 'customer_id', 'rute_id', 'nomor_uang_jalan'] as $field) {
                $table->integer($field);
            }
            $table->date('tanggal');
            $table->bigInteger('nominal_transaksi')->default(123456);
        });
        Schema::create('transaksis', function (Blueprint $table): void {
            $table->id();
            $table->integer('kas_uang_jalan_id');
            $table->integer('status')->default(3);
            $table->integer('void')->default(0);
            $table->integer('bayar')->default(0);
            $table->integer('keranjang')->default(0);
            foreach (['tanggal_muat', 'tanggal_bongkar', 'nota_muat', 'nota_bongkar', 'tonase', 'timbangan_bongkar', 'gross_muat', 'tarra_muat', 'gross_bongkar', 'tarra_bongkar'] as $field) {
                $table->string($field)->nullable();
            }
            $table->bigInteger('harga_vendor')->default(987654321);
        });
        Schema::create('keranjang_nota_bayars', function (Blueprint $table): void {
            $table->id();
            $table->integer('vendor_id');
            $table->integer('transaksi_id');
            $table->integer('user_id')->nullable();
        });
        Schema::create('transaksi_additionals', function (Blueprint $table): void {
            $table->id();
            $table->integer('transaksi_id');
            $table->integer('vendor_id');
            $table->string('jenis');
            $table->integer('status')->default(3);
            $table->integer('customer_id')->nullable();
            $table->integer('rute_id')->nullable();
            $table->bigInteger('nominal')->default(987654321);
        });
        foreach (['invoice_bayars', 'invoice_add_vendors'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->integer('vendor_id');
                $table->string('periode');
                $table->date('tanggal')->nullable();
                $table->date('tempo')->nullable();
                $table->string('jenis')->nullable();
                $table->integer('lunas')->default(0);
                $table->integer('is_finished')->default(0);
                $table->integer('status')->default(1);
                $table->bigInteger('total')->default(987654321);
                $table->bigInteger('total_bayar')->default(987654321);
                $table->timestamps();
            });
        }
        foreach (['invoice_bayar_details', 'invoice_add_vendor_details'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->integer('invoice_bayar_id')->nullable();
                $table->integer('invoice_add_vendor_id')->nullable();
                $table->integer('transaksi_id')->nullable();
                $table->integer('transaksi_additional_id')->nullable();
            });
        }
        DB::table('vendors')->insert([['id' => 1, 'nama' => 'Vendor A'], ['id' => 2, 'nama' => 'FOREIGN VENDOR']]);
        DB::table('vehicles')->insert([['id' => 1, 'vendor_id' => 1, 'nomor_lambung' => 'UNIT A'], ['id' => 2, 'vendor_id' => 2, 'nomor_lambung' => 'FOREIGN UNIT']]);
        DB::table('customers')->insert(['id' => 1, 'nama' => 'Tambang A']);
        DB::table('rutes')->insert(['id' => 1, 'nama' => 'Rute A', 'jarak' => 50]);
        foreach ([1, 2] as $vendor) {
            DB::table('kas_uang_jalans')->insert(['id' => $vendor, 'vendor_id' => $vendor, 'vehicle_id' => $vendor, 'customer_id' => 1, 'rute_id' => 1, 'nomor_uang_jalan' => $vendor, 'tanggal' => '2026-11-01']);
            DB::table('transaksis')->insert(['id' => $vendor, 'kas_uang_jalan_id' => $vendor, 'nota_muat' => 'TICKET '.$vendor, 'tonase' => '30', 'timbangan_bongkar' => '29']);
            DB::table('invoice_bayars')->insert(['id' => $vendor, 'vendor_id' => $vendor, 'periode' => 'PERIODE '.$vendor]);
            DB::table('invoice_bayar_details')->insert(['invoice_bayar_id' => $vendor, 'transaksi_id' => $vendor]);
            foreach (['kompensasi_jr', 'penyesuaian_bbm', 'achievement'] as $index => $kind) {
                $id = $vendor * 10 + $index;
                DB::table('transaksi_additionals')->insert(['id' => $id, 'vendor_id' => $vendor, 'transaksi_id' => $vendor, 'jenis' => $kind]);
                DB::table('invoice_add_vendors')->insert(['id' => $id, 'vendor_id' => $vendor, 'jenis' => $kind, 'periode' => '1', 'created_at' => '2026-11-01', 'updated_at' => '2026-11-01']);
                DB::table('invoice_add_vendor_details')->insert(['invoice_add_vendor_id' => $id, 'transaksi_additional_id' => $id]);
            }
        }
        $this->actingAs(User::factory()->create(['username' => 'billing-test', 'password' => 'secret-pass', 'role' => 'vendor-operational', 'vendor_id' => 1]));
    }

    public function test_lists_and_details_are_tenant_scoped_and_never_serialize_restricted_money(): void
    {
        foreach (['nota-bongkar', 'nota-bayar', 'invoice'] as $page) {
            $this->get('/vendor-operational/'.$page)->assertOk()->assertDontSee('987654321')->assertDontSee('FOREIGN UNIT');
            $this->getJson('/vendor-operational/'.$page)->assertOk()->assertDontSee('987654321')->assertDontSee('FOREIGN UNIT');
        }
        foreach (['utama', 'kompensasi_jr', 'penyesuaian_bbm', 'achievement'] as $index => $kind) {
            $id = $kind === 'utama' ? 1 : 9 + $index;
            foreach (['nota-bayar', 'invoice'] as $page) {
                $this->getJson('/vendor-operational/'.$page.'?kind='.$kind)->assertOk()->assertDontSee('987654321');
                $this->get('/vendor-operational/'.$page.'/'.$kind.'/'.$id)->assertOk()->assertSee('UNIT A')->assertDontSee('987654321')->assertDontSee('987.654.321');
                $foreign = $kind === 'utama' ? 2 : $id + 10;
                $this->get('/vendor-operational/'.$page.'/'.$kind.'/'.$foreign)->assertNotFound();
            }
        }
        $this->get('/vendor-operational/nota-bongkar/2')->assertNotFound();
    }

    public function test_incremental_rows_are_bounded_and_can_be_retrieved_without_mutation(): void
    {
        foreach (range(3, 24) as $id) {
            DB::table('transaksis')->insert(['id' => $id, 'kas_uang_jalan_id' => 1, 'nota_muat' => 'PAGE-'.$id]);
        }
        $before = DB::table('transaksis')->get()->toArray();
        $response = $this->getJson('/vendor-operational/nota-bayar')->assertOk();
        $this->assertNotNull($response->json('next'));
        $this->assertSame(20, substr_count($response->json('rows'), '<tr>'));
        $this->getJson($response->json('next'))->assertOk()->assertJsonPath('next', null);
        $this->assertEquals($before, DB::table('transaksis')->get()->toArray());
        $this->get('/vendor-operational/nota-bayar?kind=invalid')->assertSessionHasErrors('kind');
    }

    public function test_filters_search_each_category_and_do_not_accept_another_vendor_context(): void
    {
        foreach (['utama', 'kompensasi_jr', 'penyesuaian_bbm', 'achievement'] as $kind) {
            $this->getJson('/vendor-operational/nota-bayar?kind='.$kind.'&search=TICKET%201&vendor_id=2')->assertOk()->assertSee('UNIT A')->assertDontSee('FOREIGN UNIT');
            $this->getJson('/vendor-operational/nota-bayar?kind='.$kind.'&search=NOT-A-TICKET')->assertOk()->assertJsonPath('rows', '');
        }
        DB::table('transaksis')->where('id', 1)->update(['status' => 2]);
        $this->getJson('/vendor-operational/nota-bongkar?status=waiting')->assertOk()->assertSee('UNIT A');
        $this->getJson('/vendor-operational/nota-bongkar?status=completed')->assertUnprocessable();
    }

    public function test_invoice_details_filter_cross_vendor_links_and_escape_text(): void
    {
        DB::table('invoice_bayar_details')->insert(['invoice_bayar_id' => 1, 'transaksi_id' => 2]);
        DB::table('invoice_add_vendor_details')->insert(['invoice_add_vendor_id' => 10, 'transaksi_additional_id' => 20]);
        DB::table('transaksis')->where('id', 1)->update(['nota_muat' => '<script>alert(1)</script>']);
        foreach (['utama/1', 'kompensasi_jr/10'] as $path) {
            $response = $this->get('/vendor-operational/invoice/'.$path)->assertOk()->assertDontSee('FOREIGN UNIT');
            $response->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
            $this->getJson('/vendor-operational/invoice/'.$path)->assertOk()->assertDontSee('FOREIGN UNIT')->assertDontSee('987654321');
        }
        $this->get('/vendor-operational/nota-bayar/invalid/1')->assertNotFound();
        $this->get('/vendor-operational/invoice/invalid/1')->assertNotFound();
        $this->post('/vendor-operational/nota-bayar')->assertStatus(405);
        $this->patch('/vendor-operational/nota-bongkar/1')->assertStatus(405);
    }

    public function test_accounts_without_vendor_and_other_roles_cannot_read_billing(): void
    {
        $this->actingAs(User::factory()->create(['username' => 'wrong-role', 'password' => 'secret-pass', 'role' => 'customer', 'vendor_id' => 1]));
        foreach (['nota-bongkar', 'nota-bayar', 'invoice'] as $path) {
            $this->get('/vendor-operational/'.$path)->assertForbidden();
        }
        $this->actingAs(User::factory()->create(['username' => 'no-vendor', 'password' => 'secret-pass', 'role' => 'vendor-operational', 'vendor_id' => null]));
        foreach (['nota-bongkar', 'nota-bayar', 'invoice'] as $path) {
            $this->get('/vendor-operational/'.$path)->assertForbidden();
        }
    }

    public function test_operasional_reads_every_vendor_without_finance_and_picks_a_vendor_for_payment_notes(): void
    {
        $this->actingAs(User::factory()->create(['username' => 'ops-all', 'password' => 'secret-pass', 'role' => 'operasional', 'vendor_id' => null]));
        // Nota Bongkar: seluruh vendor secara default, bisa disaring per vendor.
        DB::table('transaksis')->update(['status' => 2]);
        $this->get('/vendor-operational/nota-bongkar')->assertOk()->assertSee('UNIT A')->assertSee('FOREIGN UNIT')->assertSee('Seluruh vendor')->assertDontSee('987654321');
        $this->getJson('/vendor-operational/nota-bongkar?vendor_id=2')->assertOk()->assertSee('FOREIGN UNIT')->assertDontSee('UNIT A');
        $this->get('/vendor-operational/nota-bongkar?vendor_id=999')->assertSessionHasErrors('vendor_id');
        // Nota Bayar: pilih vendor dulu, lalu menu dan daftar vendor itu saja.
        DB::table('transaksis')->update(['status' => 3]);
        $this->get('/vendor-operational/nota-bayar')->assertOk()->assertSee('Pilih Vendor')->assertSee('FOREIGN VENDOR')->assertDontSee('UNIT A');
        $this->getJson('/vendor-operational/nota-bayar?kind=utama')->assertStatus(422);
        $this->get('/vendor-operational/nota-bayar?vendor_id=2')->assertOk()->assertSee('FOREIGN VENDOR')->assertSee('GANTI VENDOR')
            ->assertSee(route('vendor-operational.nota-bayar', ['kind' => 'utama', 'vendor_id' => 2]));
        $this->get('/vendor-operational/nota-bayar?vendor_id=1&kind=utama')->assertOk()->assertSee('UNIT A')->assertDontSee('FOREIGN UNIT')
            ->assertSee('Ganti vendor')->assertDontSee('Bruto')->assertDontSee('987654321');
        // Invoice: seluruh vendor dengan nama vendor per baris, tanpa nilai keuangan.
        $this->get('/vendor-operational/invoice?status=all')->assertOk()->assertSee('PERIODE 1')->assertSee('PERIODE 2')->assertSee('FOREIGN VENDOR')
            ->assertDontSee('Grand Total')->assertDontSee('Total Bayar');
        $this->get('/vendor-operational/invoice?status=all&vendor_id=1')->assertOk()->assertSee('PERIODE 1')->assertDontSee('PERIODE 2');
        $this->get('/vendor-operational/invoice/utama/2')->assertOk()->assertSee('PERIODE 2')->assertDontSee('Total Bayar')->assertDontSee('notaTable');
    }

    public function test_mobile_operasional_picks_a_vendor_for_payment_notes_and_reads_every_invoice(): void
    {
        $ops = User::factory()->create(['username' => 'ops-mobile-all', 'password' => 'secret-pass', 'role' => 'operasional', 'vendor_id' => null]);
        $this->app['auth']->forgetGuards();
        $headers = ['X-Workspace-ID' => self::WORKSPACE, 'Authorization' => 'Bearer '.$ops->createToken('billing-test', ['workspace:'.self::WORKSPACE])->plainTextToken];
        $this->getJson('/api/v1/payment-notes?kind=utama', $headers)->assertUnprocessable()->assertJsonValidationErrors('vendor_id');
        $this->getJson('/api/v1/payment-notes?kind=utama&vendor_id=2', $headers)->assertOk()->assertSee('FOREIGN UNIT')->assertDontSee('UNIT A')
            ->assertJsonMissingPath('data.0.finance')->assertJsonMissingPath('meta.total_gross_amount');
        $invoices = $this->getJson('/api/v1/vendor-invoices?status=all', $headers)->assertOk()->assertJsonMissingPath('meta.grand_total_amount');
        $this->assertContains('FOREIGN VENDOR', array_column($invoices->json('data'), 'vendor_name'));
        $this->assertContains('Vendor A', array_column($invoices->json('data'), 'vendor_name'));
        $this->getJson('/api/v1/vendor-invoices?status=all&vendor_id=1', $headers)->assertOk()->assertDontSee('FOREIGN VENDOR');
        $this->getJson('/api/v1/vendor-invoices/utama/2', $headers)->assertOk()->assertJsonPath('invoice.vendor_name', 'FOREIGN VENDOR')->assertJsonMissingPath('invoice.finance');
    }

    public function test_payment_notes_only_show_the_same_eligible_records_and_columns_as_source(): void
    {
        $menu = $this->get('/vendor-operational/nota-bayar')->assertOk()->assertSee('HPP')->assertSee('KOMPENSASI JALAN RUSAK')->assertSee('PENYESUAIAN BBM')->assertSee('ACHIEVEMENT');
        $menu->assertDontSee('FOREIGN VENDOR');
        $response = $this->get('/vendor-operational/nota-bayar?kind=utama')->assertOk()->assertSee('123.456');
        $html = new \DOMDocument;
        @$html->loadHTML($response->getContent());
        $xpath = new \DOMXPath($html);
        $headers = array_map(fn ($node): string => trim($node->textContent), iterator_to_array($xpath->query('//table[@id="note-inbox-table"]/thead/tr/th')));
        $expected = ['No', 'Tanggal UJ', 'Kode', 'Nomor Lambung', 'Vendor', 'Rute', 'Jarak', 'Tanggal Muat', 'Nota Muat', 'Tonase Muat', 'Tanggal Bongkar', 'Nota Bongkar', 'Tonase Bongkar', 'Selisih (Ton)', 'Selisih (%)', 'Uang Jalan'];
        $this->assertSame($expected, $headers);
        $this->assertSame(0, $xpath->query('//select[@name="status"]')->length);
        $this->assertSame(0, $xpath->query('//table[@id="note-inbox-table"]//input')->length);
        // Tautan hanya di judul kolom untuk mengurutkan, tidak ada aksi di baris nota.
        $this->assertSame(0, $xpath->query('//table[@id="note-inbox-table"]/tbody//a')->length);
        $this->assertSame(5, $xpath->query('//table[@id="note-inbox-table"]/thead//a[@data-sort]')->length);
        foreach (['kompensasi_jr', 'penyesuaian_bbm', 'achievement'] as $kind) {
            $response = $this->get('/vendor-operational/nota-bayar?kind='.$kind)->assertOk()->assertDontSee('Harga');
            @$html->loadHTML($response->getContent());
            $xpath = new \DOMXPath($html);
            $headers = array_map(fn ($node): string => trim($node->textContent), iterator_to_array($xpath->query('//table[@id="note-inbox-table"]/thead/tr/th')));
            $this->assertSame(array_slice($expected, 0, -1), $headers);
        }
        DB::table('keranjang_nota_bayars')->insert(['vendor_id' => 1, 'transaksi_id' => 1, 'user_id' => auth()->id()]);
        $this->getJson('/vendor-operational/nota-bayar?kind=utama')->assertOk()->assertJsonPath('rows', '');
        $this->get('/vendor-operational/nota-bayar/utama/1')->assertNotFound();
        DB::table('keranjang_nota_bayars')->delete();
        DB::table('keranjang_nota_bayars')->insert(['vendor_id' => 1, 'transaksi_id' => 1, 'user_id' => auth()->id() + 1]);
        DB::table('transaksis')->where('id', 1)->update(['keranjang' => 1]);
        $this->getJson('/vendor-operational/nota-bayar?kind=utama')->assertOk()->assertSee('UNIT A');
        foreach (['bayar'] as $field) {
            DB::table('transaksis')->where('id', 1)->update([$field => 1]);
            $this->getJson('/vendor-operational/nota-bayar?kind=utama')->assertOk()->assertJsonPath('rows', '');
            DB::table('transaksis')->where('id', 1)->update([$field => 0]);
        }
        DB::table('transaksi_additionals')->where('vendor_id', 1)->update(['status' => 4]);
        foreach (['kompensasi_jr', 'penyesuaian_bbm', 'achievement'] as $kind) {
            $this->getJson('/vendor-operational/nota-bayar?kind='.$kind)->assertOk()->assertJsonPath('rows', '');
        }
    }

    public function test_unloading_matches_the_original_flat_table_and_defaults_to_waiting(): void
    {
        DB::table('transaksis')->where('id', 1)->update(['status' => 2]);
        DB::table('transaksis')->insert(['id' => 3, 'kas_uang_jalan_id' => 1, 'status' => 3, 'nota_muat' => 'COMPLETED-TICKET']);
        $response = $this->get('/vendor-operational/nota-bongkar')->assertOk()->assertSee('TICKET 1')->assertDontSee('COMPLETED-TICKET')
            ->assertSee('<h1><u>Nota Bongkar</u></h1>', false)->assertDontSee('Selisih / uang jalan')->assertDontSee('Uang jalan / unit');
        $html = new \DOMDocument;
        @$html->loadHTML($response->getContent());
        $xpath = new \DOMXPath($html);
        $headers = array_map(fn ($node): string => trim($node->textContent), iterator_to_array($xpath->query('//table[@id="note-inbox-table"]/thead/tr/th')));
        $this->assertSame(['No', 'Tanggal', 'Kode', 'Nomor Lambung', 'Vendor', 'Tambang', 'Rute', 'Tanggal Muat', 'Nota Muat', 'Timbangan Muat'], $headers);
        $this->assertSame(1, $xpath->query('//table[@id="note-inbox-table"]//a[contains(@class,"js-readonly-detail")]')->length);
        $this->assertSame(0, $xpath->query('//table[@id="note-inbox-table"]//form')->length);
        $this->getJson('/vendor-operational/nota-bongkar?status=all')->assertUnprocessable();
        $this->getJson('/vendor-operational/nota-bongkar?status=completed')->assertUnprocessable();
        $this->get('/vendor-operational/nota-bongkar/3')->assertNotFound();
        $this->assertSame(0, $xpath->query('//select[@name="status"]')->length);
        $this->get('/vendor-operational/nota-bongkar/1')->assertOk()->assertSee('UNIT A')->assertDontSee('987654321');
        foreach (range(4, 24) as $id) {
            DB::table('transaksis')->insert(['id' => $id, 'kas_uang_jalan_id' => 1, 'status' => 2, 'nota_muat' => 'WAITING-'.$id]);
        }
        $rows = $this->getJson('/vendor-operational/nota-bongkar')->assertOk()->assertDontSee('FOREIGN UNIT')->assertDontSee('987654321');
        $this->assertSame(20, substr_count($rows->json('rows'), '<tr>'));
        $this->assertNotNull($rows->json('next'));
        $this->getJson($rows->json('next'))->assertOk()->assertJsonPath('next', null);
    }

    /** Nota bayar: urut per kolom, saring per rute, total seluruh nota hasil filter. */
    public function test_payment_notes_sort_filter_route_and_total_every_filtered_note(): void
    {
        $this->actingAs(User::factory()->create(['username' => 'ops-sort', 'password' => 'secret-pass', 'role' => 'operasional', 'vendor_id' => null]));
        DB::table('rutes')->insert(['id' => 2, 'nama' => 'Rute B', 'jarak' => 20]);
        DB::table('kas_uang_jalans')->insert([
            ['id' => 3, 'vendor_id' => 1, 'vehicle_id' => 1, 'customer_id' => 1, 'rute_id' => 2, 'nomor_uang_jalan' => 9, 'tanggal' => '2026-10-15', 'nominal_transaksi' => 100000],
            ['id' => 4, 'vendor_id' => 1, 'vehicle_id' => 1, 'customer_id' => 1, 'rute_id' => 2, 'nomor_uang_jalan' => 3, 'tanggal' => '2026-11-20', 'nominal_transaksi' => 200000],
        ]);
        DB::table('transaksis')->where('id', 1)->update(['tanggal_muat' => '2026-11-02', 'tanggal_bongkar' => '2026-11-03']);
        DB::table('transaksis')->insert([
            ['id' => 3, 'kas_uang_jalan_id' => 3, 'nota_muat' => 'TICKET OKT', 'tonase' => '10.5', 'timbangan_bongkar' => '10,25', 'tanggal_muat' => '2026-10-16', 'tanggal_bongkar' => '2026-11-30'],
            ['id' => 4, 'kas_uang_jalan_id' => 4, 'nota_muat' => 'TICKET NOV', 'tonase' => '20', 'timbangan_bongkar' => '19', 'tanggal_muat' => '2026-11-21', 'tanggal_bongkar' => '2026-11-22'],
        ]);
        $order = function (string $query): array {
            $rows = $this->getJson('/vendor-operational/nota-bayar?vendor_id=1&kind=utama'.$query)->assertOk()->json('rows');
            preg_match_all('/TICKET (?:OKT|NOV|1)/', $rows, $matches);

            return $matches[0];
        };
        $this->assertSame(['TICKET OKT', 'TICKET 1', 'TICKET NOV'], $order('&sort=tanggal_uj'));
        $this->assertSame(['TICKET NOV', 'TICKET 1', 'TICKET OKT'], $order('&sort=tanggal_uj&dir=desc'));
        $this->assertSame(['TICKET 1', 'TICKET NOV', 'TICKET OKT'], $order('&sort=kode'));
        $this->assertSame(['TICKET 1', 'TICKET NOV', 'TICKET OKT'], $order('&sort=rute'));
        $this->assertSame(['TICKET OKT', 'TICKET 1', 'TICKET NOV'], $order('&sort=tanggal_muat'));
        $this->assertSame(['TICKET OKT', 'TICKET NOV', 'TICKET 1'], $order('&sort=tanggal_bongkar&dir=desc'));
        $this->assertSame(['TICKET NOV', 'TICKET OKT'], $order('&rute_id=2&sort=kode'));
        $this->getJson('/vendor-operational/nota-bayar?vendor_id=1&kind=utama&sort=harga')->assertUnprocessable();

        // Halaman: pilihan rute, judul kolom bisa diklik, total seluruh nota (bukan per halaman).
        $this->get('/vendor-operational/nota-bayar?vendor_id=1&kind=utama')->assertOk()
            ->assertSee('<option value="2" >Rute B</option>', false)->assertSee('data-sort="tanggal_uj"', false)->assertSee('data-sort="tanggal_bongkar"', false)
            ->assertSee('Total seluruh hasil filter')->assertSee('60,50 ton')->assertSee('58,25 ton')->assertSee('Rp423.456');
        $this->get('/vendor-operational/nota-bayar?vendor_id=1&kind=utama&rute_id=2&sort=kode&dir=desc')->assertOk()
            ->assertSee('30,50 ton')->assertSee('29,25 ton')->assertSee('Rp300.000')->assertDontSee('TICKET 1')
            ->assertSee('name="sort" value="kode"', false)->assertSee('aria-sort="descending"', false);

        // Nota tambahan: rute miliknya dipakai bila diisi; tanpa total uang jalan.
        DB::table('transaksi_additionals')->insert(['id' => 40, 'vendor_id' => 1, 'transaksi_id' => 3, 'jenis' => 'achievement', 'rute_id' => 1]);
        $rows = $this->getJson('/vendor-operational/nota-bayar?vendor_id=1&kind=achievement&rute_id=1')->assertOk()->json('rows');
        $this->assertSame(2, substr_count($rows, '<tr>'));
        $this->assertStringNotContainsString('TICKET NOV', $rows);
        $this->get('/vendor-operational/nota-bayar?vendor_id=1&kind=achievement&sort=rute')->assertOk()
            ->assertSee('40,50 ton')->assertDontSee('Rp423.456');
    }

    public function test_invoice_list_and_detail_sort_by_column(): void
    {
        $this->actingAs(User::factory()->create(['username' => 'ops-invoice-sort', 'password' => 'secret-pass', 'role' => 'operasional', 'vendor_id' => null]));
        DB::table('invoice_bayars')->where('id', 1)->update(['tanggal' => '2026-11-05', 'tempo' => '2026-12-20']);
        DB::table('invoice_bayars')->where('id', 2)->update(['tanggal' => '2026-11-01', 'tempo' => '2026-12-01']);
        DB::table('invoice_add_vendors')->update(['status' => 0]);
        $order = function (string $query): array {
            $rows = $this->getJson('/vendor-operational/invoice?status=all'.$query)->assertOk()->json('rows');
            preg_match_all('/PERIODE \d/', $rows, $matches);

            return $matches[0];
        };
        $this->assertSame(['PERIODE 2', 'PERIODE 1'], $order('&sort=tanggal'));
        $this->assertSame(['PERIODE 1', 'PERIODE 2'], $order('&sort=tempo&dir=desc'));
        $this->assertSame(['PERIODE 2', 'PERIODE 1'], $order('&sort=vendor'));
        $this->assertSame(['PERIODE 2', 'PERIODE 1'], $order('&sort=invoice&dir=desc'));
        $this->getJson('/vendor-operational/invoice?sort=total')->assertUnprocessable();
        $this->get('/vendor-operational/invoice?sort=vendor')->assertOk()->assertSee('data-sort="tempo"', false)->assertSee('name="sort" value="vendor"', false);

        DB::table('kas_uang_jalans')->insert(['id' => 3, 'vendor_id' => 1, 'vehicle_id' => 1, 'customer_id' => 1, 'rute_id' => 1, 'nomor_uang_jalan' => 7, 'tanggal' => '2026-10-01']);
        DB::table('transaksis')->insert(['id' => 3, 'kas_uang_jalan_id' => 3, 'nota_muat' => 'TICKET 3']);
        DB::table('invoice_bayar_details')->insert(['invoice_bayar_id' => 1, 'transaksi_id' => 3]);
        $detail = fn (string $query): string => $this->getJson('/vendor-operational/invoice/utama/1'.$query)->assertOk()->json('rows');
        $this->assertLessThan(strpos($detail('?sort=tanggal_uj'), 'TICKET 1'), strpos($detail('?sort=tanggal_uj'), 'TICKET 3'));
        $this->assertLessThan(strpos($detail('?sort=kode&dir=desc'), 'TICKET 1'), strpos($detail('?sort=kode&dir=desc'), 'TICKET 3'));
        $this->get('/vendor-operational/invoice/utama/1')->assertOk()->assertSee('data-sort="kode"', false);
        $this->getJson('/vendor-operational/invoice/utama/1?sort=vendor')->assertUnprocessable();
    }

    /** Mobile memakai urutan kolom yang sama dengan web: Nota Bayar & daftar invoice. */
    public function test_mobile_payment_notes_and_invoices_sort_like_web(): void
    {
        $ops = User::factory()->create(['username' => 'ops-mobile-sort', 'password' => 'secret-pass', 'role' => 'operasional', 'vendor_id' => null]);
        $this->app['auth']->forgetGuards();
        $headers = ['X-Workspace-ID' => self::WORKSPACE, 'Authorization' => 'Bearer '.$ops->createToken('billing-test', ['workspace:'.self::WORKSPACE])->plainTextToken];
        DB::table('rutes')->insert(['id' => 2, 'nama' => 'Rute B', 'jarak' => 20]);
        DB::table('kas_uang_jalans')->insert([
            ['id' => 3, 'vendor_id' => 1, 'vehicle_id' => 1, 'customer_id' => 1, 'rute_id' => 2, 'nomor_uang_jalan' => 9, 'tanggal' => '2026-10-15', 'nominal_transaksi' => 100000],
            ['id' => 4, 'vendor_id' => 1, 'vehicle_id' => 1, 'customer_id' => 1, 'rute_id' => 2, 'nomor_uang_jalan' => 3, 'tanggal' => '2026-11-20', 'nominal_transaksi' => 200000],
        ]);
        DB::table('transaksis')->insert([
            ['id' => 3, 'kas_uang_jalan_id' => 3, 'nota_muat' => 'TICKET OKT', 'tonase' => '10.5', 'timbangan_bongkar' => '10,25'],
            ['id' => 4, 'kas_uang_jalan_id' => 4, 'nota_muat' => 'TICKET NOV', 'tonase' => '20', 'timbangan_bongkar' => '19'],
        ]);
        $notes = fn (string $query): array => $this->getJson('/api/v1/payment-notes?kind=utama&vendor_id=1'.$query, $headers)->assertOk()->json('data.*.id');
        $this->assertSame([4, 3, 1], $notes(''));
        $this->assertSame([1, 4, 3], $notes('&sort=kode'));
        $this->assertSame([4, 1, 3], $notes('&sort=tanggal_uj&dir=desc'));
        $this->assertSame([1, 4, 3], $notes('&sort=rute&search=TICKET'));
        $this->getJson('/api/v1/payment-notes?kind=utama&vendor_id=1&sort=harga', $headers)->assertUnprocessable()->assertJsonValidationErrors('sort');
        $this->getJson('/api/v1/payment-notes?kind=utama&vendor_id=1&dir=up', $headers)->assertUnprocessable()->assertJsonValidationErrors('dir');

        DB::table('invoice_bayars')->where('id', 1)->update(['tanggal' => '2026-11-05', 'tempo' => '2026-12-20']);
        DB::table('invoice_bayars')->where('id', 2)->update(['tanggal' => '2026-11-01', 'tempo' => '2026-12-01']);
        DB::table('invoice_add_vendors')->update(['status' => 0]);
        $invoices = fn (string $query): array => $this->getJson('/api/v1/vendor-invoices?status=all'.$query, $headers)->assertOk()->json('data.*.id');
        $this->assertSame([2, 1], $invoices('&sort=tanggal'));
        $this->assertSame([1, 2], $invoices('&sort=tempo&dir=desc'));
        $this->getJson('/api/v1/vendor-invoices?sort=total', $headers)->assertUnprocessable();
    }

    public function test_invoice_list_follows_source_columns_and_only_active_unpaid_invoices(): void
    {
        DB::table('invoice_bayars')->insert(['id' => 30, 'vendor_id' => 1, 'periode' => 'PAID-BASE', 'lunas' => 1]);
        DB::table('invoice_add_vendors')->insert([
            ['id' => 30, 'vendor_id' => 1, 'jenis' => 'achievement', 'periode' => 'PAID-EXTRA', 'status' => 1, 'is_finished' => 1],
            ['id' => 31, 'vendor_id' => 1, 'jenis' => 'achievement', 'periode' => 'DRAFT-EXTRA', 'status' => 0, 'is_finished' => 0],
        ]);
        $response = $this->get('/vendor-operational/invoice')->assertOk()
            ->assertSee('Tempo Dari')->assertSee('Tempo Sampai')
            ->assertDontSee('PAID-BASE')->assertDontSee('PAID-EXTRA')->assertDontSee('DRAFT-EXTRA')
            ->assertDontSee('987654321')->assertDontSee('987.654.321')->assertDontSee('Total Bayar');
        $dom = new \DOMDocument;
        @$dom->loadHTML($response->getContent());
        $xpath = new \DOMXPath($dom);
        $headers = array_map(fn ($node) => trim($node->textContent), iterator_to_array($xpath->query('//*[@id="note-inbox-table"]/thead/tr/th')));
        $this->assertSame(['No', 'Tanggal', 'Vendor', 'Invoice', 'Jatuh Tempo', 'Status'], $headers);
        $this->assertSame(4, $xpath->query('//*[@id="note-inbox-table"]/tbody/tr')->length);
        $this->assertSame(0, $xpath->query('//form[translate(@method,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz")="post" and not(contains(@action,"/logout"))]')->length);
        DB::table('invoice_bayars')->where('id', 1)->update(['tempo' => '2026-11-10']);
        DB::table('invoice_add_vendors')->where('vendor_id', 1)->update(['tempo' => '2026-11-20']);
        $filtered = $this->getJson('/vendor-operational/invoice?start_date=2026-11-09&end_date=2026-11-11')->assertOk();
        $this->assertSame(1, substr_count($filtered->json('rows'), '<tr>'));
        $this->getJson('/vendor-operational/invoice?start_date=2026-11-20&end_date=2026-11-01')->assertUnprocessable();
    }

    public function test_invoice_combined_types_scroll_without_duplicates_and_hide_money(): void
    {
        foreach (range(30, 53) as $id) {
            DB::table('invoice_bayars')->insert(['id' => $id, 'vendor_id' => 1, 'periode' => 'PAGE-'.$id, 'tanggal' => '2026-11-01']);
        }
        $first = $this->getJson('/vendor-operational/invoice')->assertOk()->assertDontSee('987654321');
        $second = $this->getJson($first->json('next'))->assertOk()->assertDontSee('987654321')->assertJsonPath('next', null);
        $this->assertSame(20, substr_count($first->json('rows'), '<tr>'));
        $this->assertSame(8, substr_count($second->json('rows'), '<tr>'));
        preg_match_all('/href="([^"]+)"/', $first->json('rows').$second->json('rows'), $links);
        $this->assertCount(28, array_unique($links[1]));
    }

    public function test_invoice_payment_status_filters_preserve_vendor_scope_and_show_update_date(): void
    {
        DB::table('invoice_bayars')->insert([
            ['id' => 30, 'vendor_id' => 1, 'periode' => 'LUNAS-UTAMA', 'lunas' => 1, 'updated_at' => '2026-11-04 15:30:00'],
            ['id' => 31, 'vendor_id' => 2, 'periode' => 'LUNAS-VENDOR-LAIN', 'lunas' => 1, 'updated_at' => '2026-11-04 15:30:00'],
        ]);
        DB::table('invoice_add_vendors')->insert([
            ['id' => 30, 'vendor_id' => 1, 'jenis' => 'achievement', 'periode' => 'LUNAS-TAMBAHAN', 'status' => 1, 'is_finished' => 1, 'updated_at' => '2026-11-05 11:00:00'],
            ['id' => 31, 'vendor_id' => 2, 'jenis' => 'achievement', 'periode' => 'LUNAS-TAMBAHAN-LAIN', 'status' => 1, 'is_finished' => 1, 'updated_at' => '2026-11-05 11:00:00'],
        ]);
        $this->get('/vendor-operational/invoice')->assertOk()->assertDontSee('LUNAS-UTAMA')->assertDontSee('LUNAS-TAMBAHAN')
            ->assertViewHas('filters', fn (array $filters): bool => $filters['status'] === 'unpaid');
        $paid = $this->getJson('/vendor-operational/invoice?status=paid&vendor_id=2')->assertOk()
            ->assertSee('LUNAS-UTAMA')->assertSee('LUNAS-TAMBAHAN')->assertSee('04-11-2026')->assertSee('05-11-2026')
            ->assertDontSee('LUNAS-VENDOR-LAIN')->assertDontSee('LUNAS-TAMBAHAN-LAIN')->assertDontSee('987654321')->assertDontSee('987.654.321');
        $this->assertSame(2, substr_count($paid->json('rows'), '<tr>'));
        $all = $this->getJson('/vendor-operational/invoice?status=all')->assertOk()->assertSee('Belum lunas')->assertSee('Lunas');
        $this->assertSame(6, substr_count($all->json('rows'), '<tr>'));
        $this->getJson('/vendor-operational/invoice?status=unknown')->assertUnprocessable();
        DB::table('invoice_bayars')->where('id', 30)->update(['updated_at' => null]);
        $this->getJson('/vendor-operational/invoice?status=paid')->assertOk()->assertSee('Tanggal belum tercatat');
    }

    public function test_paid_invoice_scroll_retains_status_filter_and_does_not_repeat_rows(): void
    {
        foreach (range(30, 54) as $id) {
            DB::table('invoice_bayars')->insert(['id' => $id, 'vendor_id' => 1, 'periode' => 'LUNAS-PAGE-'.$id, 'lunas' => 1, 'updated_at' => '2026-11-05 12:00:00']);
        }
        $first = $this->getJson('/vendor-operational/invoice?status=paid')->assertOk();
        $this->assertStringContainsString('status=paid', $first->json('next'));
        $this->assertSame(20, substr_count($first->json('rows'), '<tr>'));
        $second = $this->getJson($first->json('next'))->assertOk()->assertJsonPath('next', null);
        $this->assertSame(5, substr_count($second->json('rows'), '<tr>'));
        $this->assertStringNotContainsString('Belum lunas', $first->json('rows').$second->json('rows'));
    }

    public function test_vendor_sees_own_finance_while_vendor_operational_only_sees_travel_cash(): void
    {
        Schema::table('transaksis', fn (Blueprint $table) => $table->bigInteger('nominal_bayar')->nullable());
        Schema::table('invoice_bayars', function (Blueprint $table): void {
            foreach (['bayar', 'sisa_bayar'] as $column) {
                $table->bigInteger($column)->default(0);
            }
        });
        foreach (['ppn_masukans', 'pph_simpans'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->integer('invoice_bayar_id')->nullable();
                $table->integer('invoice_add_vendor_id')->nullable();
                $table->bigInteger('nominal');
            });
        }
        DB::table('transaksis')->where('id', 1)->update(['harga_vendor' => 70000, 'nominal_bayar' => 2000000]);
        DB::table('transaksis')->where('id', 2)->update(['harga_vendor' => 70000, 'nominal_bayar' => 5555555]);
        DB::table('invoice_bayars')->where('id', 1)->update(['total_bayar' => 2220000, 'bayar' => 1000000, 'sisa_bayar' => 1220000]);
        DB::table('ppn_masukans')->insert(['invoice_bayar_id' => 1, 'nominal' => 220000]);
        DB::table('pph_simpans')->insert(['invoice_bayar_id' => 1, 'nominal' => 40000]);

        $vendor = User::factory()->create(['username' => 'vendor-owner', 'password' => 'secret-pass', 'role' => 'vendor', 'vendor_id' => 1]);
        $this->actingAs($vendor);
        $response = $this->get('/vendor-operational/nota-bayar?kind=utama')->assertOk()
            ->assertSee('70.000')->assertSee('2.000.000')->assertSee('1.876.544')->assertDontSee('5.555.555')->assertDontSee('FOREIGN UNIT');
        $html = new \DOMDocument;
        @$html->loadHTML($response->getContent());
        $headers = array_map(fn ($node): string => trim($node->textContent), iterator_to_array((new \DOMXPath($html))->query('//table[@id="note-inbox-table"]/thead/tr/th')));
        $this->assertSame(['Uang Jalan', 'Harga', 'Bruto', 'Netto'], array_slice($headers, -4));
        $this->get('/vendor-operational/invoice/utama/1')->assertOk()
            ->assertSeeInOrder(['Total Bayar', 'Rp2.220.000', 'PPN', 'Rp220.000', 'PPh', 'Rp40.000', 'Sudah Dibayar', 'Rp1.000.000', 'Sisa Bayar', 'Rp1.220.000']);
        // Di bawah ringkasan: tabel perjalanan format admin (Total/PPN/PPh/Tagihan) tanpa aksi.
        $detail = $this->get('/vendor-operational/invoice/utama/1')->assertOk()->assertSee('id="notaTable"', false)
            ->assertSeeInOrder(['Bruto', 'Uang Jalan', 'Netto', 'Total', 'PPN', 'PPh', 'Tagihan'])->assertDontSee('note-inbox-table');
        $page = new \DOMDocument;
        @$page->loadHTML($detail->getContent());
        $this->assertSame(0, (new \DOMXPath($page))->query('//table[@id="notaTable"]//form|//table[@id="notaTable"]//button[not(@data-bs-dismiss)]')->length);
        $this->get('/vendor-operational/invoice/utama/2')->assertNotFound();
        // Daftar invoice: nominal per invoice dan grand total seluruh invoice yang sesuai filter, tanpa aksi.
        DB::table('invoice_add_vendors')->where('vendor_id', 1)->update(['total' => 100000]);
        $list = $this->get('/vendor-operational/invoice?status=all')->assertOk()
            ->assertSee('Total Bayar')->assertSee('Rp 2.220.000')->assertSee('Rp 100.000')->assertDontSee('987.654.321')
            ->assertDontSee('form-bayar')->assertDontSee('invoice.bayar.lunas');
        $this->assertStringContainsString('Rp 2.520.000', $list->getContent());
        $dom = new \DOMDocument;
        @$dom->loadHTML($list->getContent());
        $this->assertSame('Rp 2.520.000', trim((new \DOMXPath($dom))->query('//*[@id="invoice-grand-total"]')->item(0)?->textContent ?? ''));
        $this->assertSame(0, (new \DOMXPath($dom))->query('//table[@id="note-inbox-table"]//form|//table[@id="note-inbox-table"]//button')->length);
        // Invoice tambahan memperlihatkan total invoice, bukan nominal per perjalanan.
        $this->get('/vendor-operational/invoice/kompensasi_jr/10')->assertOk()->assertSee('Total')->assertSee('Rp100.000');

        $this->actingAs(User::factory()->create(['username' => 'vendor-ops', 'password' => 'secret-pass', 'role' => 'vendor-operational', 'vendor_id' => 1]));
        $this->get('/vendor-operational/nota-bayar?kind=utama')->assertOk()->assertSee('123.456')->assertDontSee('2.000.000')->assertDontSee('Bruto');
        $this->get('/vendor-operational/invoice/utama/1')->assertOk()->assertDontSee('Total Bayar')->assertDontSee('2.220.000');
        $this->get('/vendor-operational/invoice?status=all')->assertOk()->assertDontSee('Total Bayar')->assertDontSee('2.220.000')->assertDontSee('Grand Total');
    }

    public function test_mobile_vendor_sees_own_finance_and_vendor_operational_does_not(): void
    {
        Schema::table('transaksis', fn (Blueprint $table) => $table->bigInteger('nominal_bayar')->nullable());
        Schema::table('invoice_bayars', function (Blueprint $table): void {
            foreach (['bayar', 'sisa_bayar'] as $column) {
                $table->bigInteger($column)->default(0);
            }
        });
        foreach (['ppn_masukans', 'pph_simpans'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->integer('invoice_bayar_id')->nullable();
                $table->integer('invoice_add_vendor_id')->nullable();
                $table->bigInteger('nominal');
            });
        }
        DB::table('transaksis')->where('id', 1)->update(['harga_vendor' => 70000, 'nominal_bayar' => 2000000]);
        DB::table('invoice_bayars')->where('id', 1)->update(['total_bayar' => 2220000, 'bayar' => 1000000, 'sisa_bayar' => 1220000]);
        DB::table('invoice_add_vendors')->where('vendor_id', 1)->update(['total' => 100000]);

        $headers = $this->mobileHeaders(User::factory()->create(['username' => 'vendor-mobile', 'password' => 'secret-pass', 'role' => 'vendor', 'vendor_id' => 1]));
        $this->getJson('/api/v1/me', $headers)->assertOk()->assertJsonPath('data.permissions.vendor_billing', true)
            ->assertJsonPath('data.permissions.vendor_finance', true)->assertJsonPath('data.permissions.maintenance_history', true)
            ->assertJsonPath('data.permissions.load_notes', true);
        $this->getJson('/api/v1/payment-notes?kind=utama', $headers)->assertOk()
            ->assertJsonPath('data.0.finance', ['price' => 70000, 'gross_amount' => 2000000, 'net_amount' => 1876544])
            ->assertJsonPath('meta.total_gross_amount', 2000000)->assertJsonPath('meta.total_net_amount', 1876544)->assertDontSee('FOREIGN');
        $this->getJson('/api/v1/payment-notes/utama/1', $headers)->assertOk()->assertJsonPath('data.fields.Bruto (Rp)', '2.000.000');
        $this->getJson('/api/v1/payment-notes?kind=achievement', $headers)->assertOk()->assertJsonMissingPath('data.0.finance');
        $invoices = $this->getJson('/api/v1/vendor-invoices?status=all', $headers)->assertOk()->assertJsonPath('meta.grand_total_amount', 2520000);
        $this->assertEqualsCanonicalizing([2220000, 100000, 100000, 100000], array_column($invoices->json('data'), 'total_amount'));
        $this->getJson('/api/v1/vendor-invoices/utama/1', $headers)->assertOk()
            ->assertJsonPath('invoice.finance.headline', ['Total Bayar', 'Rp2.220.000'])
            ->assertJsonPath('invoice.finance.breakdown.0', ['label' => 'Bruto', 'amount' => 2000000, 'op' => null])
            ->assertJsonPath('invoice.finance.breakdown.2.label', 'Netto')
            ->assertJsonPath('invoice.finance.breakdown.5.label', 'Penyesuaian')
            ->assertJsonPath('invoice.finance.breakdown.6', ['label' => 'Total Bayar', 'amount' => 2220000, 'op' => '='])
            ->assertJsonPath('invoice.finance.progress.percent', 45)->assertJsonPath('data.0.finance.gross_amount', 2000000);

        $headers = $this->mobileHeaders(User::factory()->create(['username' => 'ops-mobile', 'password' => 'secret-pass', 'role' => 'vendor-operational', 'vendor_id' => 1]));
        $this->getJson('/api/v1/me', $headers)->assertJsonPath('data.permissions.vendor_finance', false)->assertJsonPath('data.permissions.maintenance_history', false);
        $this->getJson('/api/v1/payment-notes?kind=utama', $headers)->assertOk()->assertJsonMissingPath('data.0.finance')
            ->assertJsonMissingPath('meta.total_gross_amount')->assertDontSee('2000000');
        $this->getJson('/api/v1/vendor-invoices?status=all', $headers)->assertOk()->assertJsonMissingPath('meta.grand_total_amount')
            ->assertJsonMissingPath('data.0.total_amount');
        $this->getJson('/api/v1/vendor-invoices/utama/1', $headers)->assertOk()->assertJsonMissingPath('invoice.finance');
    }

    private function mobileHeaders(?User $user = null): array
    {
        $user ??= auth()->user();
        $this->app['auth']->forgetGuards();

        return ['X-Workspace-ID' => self::WORKSPACE, 'X-Vendor-ID' => (string) $user->vendor_id,
            'Authorization' => 'Bearer '.$user->createToken('billing-test', ['workspace:'.self::WORKSPACE])->plainTextToken];
    }

    public function test_mobile_payment_notes_reuse_web_eligibility_and_never_expose_other_vendor_or_prices(): void
    {
        $headers = $this->mobileHeaders();
        foreach (['utama', 'kompensasi_jr', 'penyesuaian_bbm', 'achievement'] as $index => $kind) {
            $id = $kind === 'utama' ? 1 : 9 + $index;
            $response = $this->getJson('/api/v1/payment-notes?kind='.$kind.'&vendor_id=2', $headers)
                ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $id)
                ->assertJsonPath('data.0.kind', $kind)->assertJsonPath('data.0.vehicle_number', 'UNIT A')
                ->assertJsonPath('meta.per_page', 20)->assertJsonPath('meta.categories.utama', 'HPP');
            $this->assertSame($kind === 'utama' ? 123456 : null, $response->json('data.0.money_out_amount'));
            $detail = $this->getJson('/api/v1/payment-notes/'.$kind.'/'.$id, $headers)->assertOk()
                ->assertJsonPath('data.fields.Nomor Lambung', 'UNIT A')
                ->assertJsonPath('data.fields.Uang Jalan (Rp)', '123.456');
            foreach ([$response, $detail] as $result) {
                $result->assertDontSee('987654321')->assertDontSee('987.654.321')->assertDontSee('FOREIGN');
            }
            $this->getJson('/api/v1/payment-notes/'.$kind.'/'.($kind === 'utama' ? 2 : $id + 10), $headers)->assertNotFound();
        }
    }

    public function test_mobile_payment_total_covers_filtered_pages_and_stays_vendor_scoped(): void
    {
        DB::table('customers')->where('id', 1)->update(['tagihan_dari' => 1]);
        for ($id = 3; $id <= 23; $id++) {
            DB::table('kas_uang_jalans')->insert(['id' => $id, 'vendor_id' => 1, 'vehicle_id' => 1, 'customer_id' => 1, 'rute_id' => 1, 'nomor_uang_jalan' => $id, 'tanggal' => '2026-11-01']);
            DB::table('transaksis')->insert(['id' => $id, 'kas_uang_jalan_id' => $id, 'nota_muat' => 'EXTRA '.$id, 'tonase' => '1.111', 'timbangan_bongkar' => '1']);
        }
        $headers = $this->mobileHeaders();
        $this->getJson('/api/v1/payment-notes', $headers)->assertOk()->assertJsonCount(20, 'data')->assertJsonPath('meta.total_weight_kg', 53331);
        $this->getJson('/api/v1/payment-notes?page=2', $headers)->assertOk()->assertJsonPath('meta.total_weight_kg', 53331);
        $this->getJson('/api/v1/payment-notes?search=EXTRA', $headers)->assertOk()->assertJsonPath('meta.total_weight_kg', 23331);
        $this->getJson('/api/v1/payment-notes?search=NOTFOUND', $headers)->assertOk()->assertJsonPath('meta.total_weight_kg', 0);
        DB::table('customers')->where('id', 1)->update(['tagihan_dari' => null]);
        $this->getJson('/api/v1/payment-notes', $headers)->assertOk()->assertJsonPath('meta.total_weight_kg', null)->assertJsonPath('meta.missing_weight_count', 22);
        DB::table('customers')->insert(['id' => 3, 'nama' => 'Customer Tambahan', 'tagihan_dari' => 2]);
        DB::table('transaksi_additionals')->where('id', 10)->update(['customer_id' => 3]);
        $this->getJson('/api/v1/payment-notes?kind=kompensasi_jr', $headers)->assertOk()->assertJsonPath('meta.total_weight_kg', 29000);
    }

    public function test_mobile_billing_weight_follows_customer_basis_without_guessing(): void
    {
        $headers = $this->mobileHeaders();
        foreach ([1 => ['loaded', '30'], 2 => ['unloaded', '29'], 0 => [null, null]] as $basis => [$source, $weight]) {
            DB::table('customers')->where('id', 1)->update(['tagihan_dari' => $basis]);
            $this->getJson('/api/v1/payment-notes', $headers)->assertOk()
                ->assertJsonPath('data.0.billing_weight_source', $source)
                ->assertJsonPath('data.0.billing_weight', $weight);
        }
        DB::table('customers')->insert(['id' => 3, 'nama' => 'Customer Tambahan', 'tagihan_dari' => 2]);
        DB::table('transaksi_additionals')->where('id', 10)->update(['customer_id' => 3]);
        $this->getJson('/api/v1/payment-notes?kind=kompensasi_jr', $headers)->assertOk()
            ->assertJsonPath('data.0.billing_weight_source', 'unloaded')
            ->assertJsonPath('data.0.billing_weight', '29');
    }

    public function test_mobile_payment_notes_filter_paginate_and_exclude_unavailable_records_without_writes(): void
    {
        $actor = auth()->user();
        $headers = $this->mobileHeaders($actor);
        foreach (range(3, 26) as $id) {
            DB::table('transaksis')->insert(['id' => $id, 'kas_uang_jalan_id' => 1, 'nota_muat' => 'PAGE-'.$id]);
        }
        DB::table('transaksis')->where('id', 3)->update(['void' => 1]);
        DB::table('transaksis')->where('id', 4)->update(['bayar' => 1]);
        DB::table('transaksis')->where('id', 5)->update(['status' => 2]);
        DB::table('keranjang_nota_bayars')->insert(['vendor_id' => 1, 'user_id' => $actor->id, 'transaksi_id' => 6]);
        $before = DB::table('transaksis')->get()->toArray();
        $first = $this->getJson('/api/v1/payment-notes', $headers)->assertOk()->assertJsonCount(20, 'data');
        $second = $this->getJson('/api/v1/payment-notes?page=2', $headers)->assertOk()->assertJsonCount(1, 'data');
        $ids = array_merge(array_column($first->json('data'), 'id'), array_column($second->json('data'), 'id'));
        $this->assertCount(21, array_unique($ids));
        foreach ([2, 3, 4, 5, 6] as $missing) {
            $this->assertNotContains($missing, $ids);
        }
        $this->getJson('/api/v1/payment-notes?search=PAGE-26', $headers)->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', 26);
        $this->getJson('/api/v1/payment-notes?search=NO-MATCH', $headers)->assertOk()->assertJsonCount(0, 'data');
        DB::table('transaksi_additionals')->where('id', 10)->update(['status' => 4]);
        $this->getJson('/api/v1/payment-notes?kind=kompensasi_jr', $headers)->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/payment-notes/kompensasi_jr/10', $headers)->assertNotFound();
        $this->assertEquals($before, DB::table('transaksis')->get()->toArray());
    }

    public function test_mobile_payment_notes_require_token_role_workspace_and_current_vendor_context(): void
    {
        $actor = auth()->user();
        $headers = $this->mobileHeaders($actor);
        $this->getJson('/api/v1/payment-notes', array_diff_key($headers, ['Authorization' => true]))->assertUnauthorized();
        $this->getJson('/api/v1/payment-notes', array_replace($headers, ['X-Vendor-ID' => '2']))->assertStatus(409);
        $this->getJson('/api/v1/payment-notes', array_replace($headers, ['X-Workspace-ID' => '413236a8-76eb-483e-bbf4-df091aa8b67e']))->assertStatus(409);
        $wrong = User::factory()->create(['username' => 'mobile-admin', 'password' => 'secret-pass', 'role' => 'admin', 'vendor_id' => 1]);
        $this->getJson('/api/v1/payment-notes', $this->mobileHeaders($wrong))->assertForbidden();
        config(['mobile.load_note_roles' => []]);
        $this->getJson('/api/v1/payment-notes', $this->mobileHeaders($actor))->assertOk();
        $actor->update(['vendor_id' => null]);
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/v1/payment-notes', $headers)->assertForbidden();
    }

    public function test_mobile_payment_notes_validate_filters_and_have_no_mutation_routes(): void
    {
        $headers = $this->mobileHeaders();
        foreach (['kind=invalid', 'page=0', 'search='.str_repeat('x', 101)] as $filter) {
            $this->getJson('/api/v1/payment-notes?'.$filter, $headers)->assertUnprocessable();
        }
        $this->getJson('/api/v1/payment-notes/invalid/1', $headers)->assertNotFound();
        foreach (['post', 'patch', 'delete'] as $method) {
            $this->{$method.'Json'}('/api/v1/payment-notes/utama/1', [], $headers)->assertStatus(405);
        }
    }

    public function test_mobile_invoice_status_dates_and_financial_allowlist_match_vendor_web(): void
    {
        DB::table('invoice_bayars')->where('id', 1)->update(['lunas' => 1, 'tanggal' => '2026-11-01', 'tempo' => '2026-11-15', 'updated_at' => '2026-11-06 10:00:00']);
        $headers = $this->mobileHeaders();
        $this->getJson('/api/v1/vendor-invoices', $headers)->assertOk()->assertJsonCount(3, 'data');
        $paid = $this->getJson('/api/v1/vendor-invoices?status=paid', $headers)->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.kind', 'utama')->assertJsonPath('data.0.category_name', 'HPP')
            ->assertJsonPath('data.0.paid_on', '2026-11-06')->assertJsonPath('data.0.paid', true);
        $this->assertSame(['id', 'kind', 'invoice_number', 'category_name', 'issued_on', 'due_on', 'paid', 'paid_on'], array_keys($paid->json('data.0')));
        $this->getJson('/api/v1/vendor-invoices?status=all', $headers)->assertOk()->assertJsonCount(4, 'data')->assertDontSee('987654321')->assertDontSee('FOREIGN');
        $this->getJson('/api/v1/vendor-invoices?status=paid&start_date=2026-11-15&end_date=2026-11-15', $headers)->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/v1/vendor-invoices?status=paid&start_date=2026-11-16', $headers)->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/vendor-invoices?status=all&search=PERIODE%201&vendor_id=2', $headers)->assertOk()->assertJsonCount(1, 'data');
        DB::table('invoice_bayars')->where('id', 1)->update(['updated_at' => null]);
        $this->getJson('/api/v1/vendor-invoices?status=paid', $headers)->assertOk()->assertJsonPath('data.0.paid_on', null);
    }

    public function test_mobile_invoice_details_include_both_tickets_and_weights_without_restricted_nominal(): void
    {
        DB::table('transaksis')->where('id', 1)->update(['nota_bongkar' => 'UNLOAD-1']);
        DB::table('invoice_bayar_details')->insert(['invoice_bayar_id' => 1, 'transaksi_id' => 2]);
        $headers = $this->mobileHeaders();
        foreach (['utama', 'kompensasi_jr', 'penyesuaian_bbm', 'achievement'] as $index => $kind) {
            $id = $kind === 'utama' ? 1 : 9 + $index;
            $result = $this->getJson('/api/v1/vendor-invoices/'.$kind.'/'.$id, $headers)->assertOk()
                ->assertJsonCount(1, 'data')->assertJsonPath('invoice.id', $id)->assertJsonPath('invoice.kind', $kind)
                ->assertJsonPath('data.0.ticket_number', 'TICKET 1')->assertJsonPath('data.0.unloading_ticket_number', 'UNLOAD-1')
                ->assertJsonPath('data.0.net_weight', '30')->assertJsonPath('data.0.unloading_net_weight', '29')
                ->assertJsonPath('meta.per_page', 20)->assertDontSee('987654321')->assertDontSee('FOREIGN');
            $this->assertSame($kind === 'utama' ? 123456 : null, $result->json('data.0.money_out_amount'));
            $this->getJson('/api/v1/vendor-invoices/'.$kind.'/'.($kind === 'utama' ? 2 : $id + 10), $headers)->assertNotFound();
        }
        DB::table('invoice_add_vendors')->where('id', 10)->update(['status' => 0]);
        $this->getJson('/api/v1/vendor-invoices/kompensasi_jr/10', $headers)->assertNotFound();
    }

    public function test_mobile_invoice_lists_and_detail_rows_are_paginated_without_business_mutation(): void
    {
        foreach (range(3, 26) as $id) {
            DB::table('invoice_bayars')->insert(['id' => $id, 'vendor_id' => 1, 'periode' => 'INV-'.$id]);
            DB::table('transaksis')->insert(['id' => $id, 'kas_uang_jalan_id' => 1, 'nota_muat' => 'ROW-'.$id]);
            DB::table('invoice_bayar_details')->insert(['invoice_bayar_id' => 1, 'transaksi_id' => $id]);
        }
        $before = DB::table('transaksis')->get()->toArray();
        $headers = $this->mobileHeaders();
        $first = $this->getJson('/api/v1/vendor-invoices', $headers)->assertOk()->assertJsonCount(20, 'data');
        $last = $this->getJson('/api/v1/vendor-invoices?page=2', $headers)->assertOk()->assertJsonCount(8, 'data');
        $keys = array_map(fn (array $row): string => $row['kind'].':'.$row['id'], array_merge($first->json('data'), $last->json('data')));
        $this->assertCount(28, array_unique($keys));
        $this->getJson('/api/v1/vendor-invoices/utama/1', $headers)->assertOk()->assertJsonCount(20, 'data');
        $this->getJson('/api/v1/vendor-invoices/utama/1?page=2', $headers)->assertOk()->assertJsonCount(5, 'data');
        $this->assertEquals($before, DB::table('transaksis')->get()->toArray());
    }

    public function test_mobile_invoice_api_validates_filters_and_remains_vendor_only_readonly(): void
    {
        $headers = $this->mobileHeaders();
        foreach (['status=invalid', 'page=0', 'start_date=invalid', 'start_date=2026-11-15&end_date=2026-11-01'] as $filter) {
            $this->getJson('/api/v1/vendor-invoices?'.$filter, $headers)->assertUnprocessable();
        }
        $this->getJson('/api/v1/vendor-invoices/invalid/1', $headers)->assertNotFound();
        foreach (['post', 'patch', 'delete'] as $method) {
            $this->{$method.'Json'}('/api/v1/vendor-invoices/utama/1', [], $headers)->assertStatus(405);
        }
        $this->getJson('/api/v1/vendor-invoices', array_replace($headers, ['X-Vendor-ID' => '2']))->assertStatus(409);
        $admin = User::factory()->create(['username' => 'invoice-mobile-admin', 'password' => 'secret-pass', 'role' => 'admin', 'vendor_id' => 1]);
        $this->getJson('/api/v1/vendor-invoices', $this->mobileHeaders($admin))->assertForbidden();
    }
}
