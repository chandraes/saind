<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

/**
 * Nota Tagihan untuk General Manager: halaman admin tanpa aksi (web) dan
 * tabel per customer di aplikasi (Ton sesuai "tagihan dari", tanpa profit).
 */
class GeneralManagerCustomerBillTest extends MobileApiTestCase
{
    private User $gm;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        View::share(['global_app_name' => 'SAIND', 'global_app_favicon' => '', 'global_app_nav_bg' => '#212529', 'global_app_logo' => '']);
        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->string('singkatan')->nullable();
            foreach (['is_kompensasi_jr', 'is_penyesuaian_bbm', 'is_achievement', 'ppn', 'pph', 'gt_muat', 'gt_bongkar', 'tanggal_muat', 'nota_muat', 'tonase', 'tanggal_bongkar', 'selisih', 'tagihan_dari', 'status'] as $flag) {
                $table->integer($flag)->default(0);
            }
        });
        Schema::create('transaksi_additionals', function (Blueprint $table): void {
            $table->id();
            $table->integer('transaksi_id');
            $table->integer('customer_id');
            $table->string('jenis');
            $table->integer('status')->default(0);
        });
        Schema::create('rutes', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->integer('jarak')->nullable();
        });
        Schema::create('customer_rute', function (Blueprint $table): void {
            $table->integer('customer_id');
            $table->integer('rute_id');
        });
        Schema::create('kas_uang_jalans', function (Blueprint $table): void {
            $table->id();
            foreach (['vendor_id', 'vehicle_id', 'customer_id', 'rute_id', 'nomor_uang_jalan'] as $field) {
                $table->integer($field);
            }
            $table->date('tanggal');
            $table->timestamps();
        });
        Schema::create('transaksis', function (Blueprint $table): void {
            $table->id();
            $table->integer('kas_uang_jalan_id');
            foreach (['status', 'void', 'tagihan', 'keranjang', 'nota_fisik'] as $flag) {
                $table->integer($flag)->default(0);
            }
            $table->integer('do_checker_id')->nullable();
            foreach (['harga_customer', 'nominal_tagihan', 'profit', 'nominal_bayar'] as $money) {
                $table->bigInteger($money)->default(0);
            }
            foreach (['tanggal_muat', 'tanggal_bongkar', 'nota_muat', 'nota_bongkar', 'tonase', 'timbangan_bongkar', 'gross_muat', 'tarra_muat', 'gross_bongkar', 'tarra_bongkar'] as $field) {
                $table->string($field)->nullable();
            }
        });
        DB::table('vendors')->insert([['id' => 1, 'nama' => 'Vendor A', 'nickname' => 'VA'], ['id' => 2, 'nama' => 'Vendor B', 'nickname' => null]]);
        DB::table('vehicles')->insert([['id' => 1, 'vendor_id' => 1, 'nomor_lambung' => '288'], ['id' => 2, 'vendor_id' => 2, 'nomor_lambung' => '512']]);
        DB::table('customers')->insert([['id' => 1, 'nama' => 'Tambang A', 'singkatan' => 'TA', 'is_kompensasi_jr' => 1, 'ppn' => 1, 'pph' => 1, 'tagihan_dari' => 2, 'status' => 1],
            ['id' => 2, 'nama' => 'Tambang B', 'singkatan' => 'TB', 'is_kompensasi_jr' => 0, 'ppn' => 0, 'pph' => 0, 'tagihan_dari' => 1, 'status' => 1]]);
        DB::table('rutes')->insert(['id' => 1, 'nama' => 'Pit - Port', 'jarak' => 40]);
        DB::table('customer_rute')->insert(['customer_id' => 1, 'rute_id' => 1]);
        foreach ([[1, 1, 1, 7], [2, 2, 1, 8], [3, 1, 2, 9]] as [$id, $vendor, $customer, $number]) {
            DB::table('kas_uang_jalans')->insert(['id' => $id, 'vendor_id' => $vendor, 'vehicle_id' => $vendor, 'customer_id' => $customer, 'rute_id' => 1,
                'nomor_uang_jalan' => $number, 'tanggal' => '2026-11-0'.$id, 'created_at' => '2026-11-01 08:00:00', 'updated_at' => '2026-11-01 08:00:00']);
            DB::table('transaksis')->insert(['id' => $id, 'kas_uang_jalan_id' => $id, 'status' => 3, 'nota_fisik' => $id === 1 ? 1 : 0,
                'harga_customer' => 100000, 'nominal_tagihan' => 3000000 * $id, 'profit' => 777777, 'nominal_bayar' => 2000000,
                'tonase' => '30.5', 'timbangan_bongkar' => '30.0', 'nota_muat' => 'M'.$id, 'nota_bongkar' => 'B'.$id]);
        }
        // Sudah ditagih: tidak tampil.
        DB::table('kas_uang_jalans')->insert(['id' => 4, 'vendor_id' => 1, 'vehicle_id' => 1, 'customer_id' => 1, 'rute_id' => 1, 'nomor_uang_jalan' => 10, 'tanggal' => '2026-11-04']);
        DB::table('transaksis')->insert(['id' => 4, 'kas_uang_jalan_id' => 4, 'status' => 3, 'tagihan' => 1, 'nominal_tagihan' => 999999]);
        // Kompensasi JR menunggu ditagih untuk nota 2; nota 1 sudah masuk keranjang (status 1).
        DB::table('transaksi_additionals')->insert([['transaksi_id' => 2, 'customer_id' => 1, 'jenis' => 'kompensasi_jr', 'status' => 0],
            ['transaksi_id' => 1, 'customer_id' => 1, 'jenis' => 'kompensasi_jr', 'status' => 1]]);
        $this->gm = User::factory()->create(['username' => 'gm', 'password' => 'secret-pass', 'role' => 'general-manager', 'vendor_id' => null]);
    }

    /** @return array<string, string> */
    private function headers(User $user): array
    {
        $this->app['auth']->forgetGuards();

        return ['Accept' => 'application/json', 'X-Workspace-ID' => self::WORKSPACE,
            'Authorization' => 'Bearer '.$user->createToken('test', ['workspace:'.self::WORKSPACE])->plainTextToken];
    }

    public function test_web_shows_the_admin_table_without_any_action(): void
    {
        $this->actingAs($this->gm)->get('/home')->assertOk()->assertSee('Nota Tagihan')->assertSee('gm-nota-tagihan-customer')->assertSee('Tambang A (TA)');
        $page = $this->get('/transaksi/nota-tagihan/1')->assertOk()->assertSee('Tambang A')->assertSee('UJ07')->assertSee('UJ08')->assertDontSee('999.999')
            ->assertSee('Export')->assertDontSee('Lanjutkan Pilihan')->assertDontSee('Keranjang')->assertDontSee('id="checkAll"', false)->assertDontSee('Total Tagihan Dipilih');
        $dom = new \DOMDocument;
        @$dom->loadHTML($page->getContent());
        $xpath = new \DOMXPath($dom);
        $this->assertSame(0, $xpath->query('//table[@id="notaTable"]//input[@type="checkbox"]|//table[@id="notaTable"]//form|//table[@id="notaTable"]//button')->length);
        $headers = array_map(fn ($node): string => trim($node->textContent), iterator_to_array($xpath->query('//table[@id="notaTable"]/thead/tr[1]/th')));
        $this->assertSame('No', $headers[0]);
        $this->assertSame('DO Fisik', end($headers));
        // Setiap baris footer punya jumlah kolom yang sama dengan header.
        $width = fn (\DOMElement $row): int => array_sum(array_map(fn ($cell): int => max(1, (int) $cell->getAttribute('colspan')), iterator_to_array($xpath->query('./td|./th', $row))));
        foreach ($xpath->query('//table[@id="notaTable"]/tfoot/tr') as $row) {
            $this->assertSame(count($headers), $width($row));
        }
        // Aksi admin tetap tertutup.
        $this->get('/transaksi/nota-tagihan/1/check')->assertForbidden();
        $this->get('/transaksi/nota-tagihan/keranjang/1')->assertForbidden();
        $this->post('/transaksi/nota-tagihan/edit/1')->assertForbidden();
    }

    public function test_mobile_lists_bills_per_customer_with_the_billing_weight_and_no_profit(): void
    {
        $headers = $this->headers($this->gm);
        $this->getJson('/api/v1/me', $headers)->assertJsonPath('data.permissions.customer_bills', true);
        $this->getJson('/api/v1/customer-options', $headers)->assertOk()->assertJsonPath('data.0.name', 'Tambang A')->assertJsonPath('data.0.short', 'TA');
        $response = $this->getJson('/api/v1/customers/1/bills', $headers)->assertOk()
            ->assertJsonPath('meta.weight_basis', 'bongkar')->assertJsonPath('meta.total', 2)
            ->assertJsonPath('meta.total_bill', 9000000)->assertJsonPath('meta.ppn', 990000)->assertJsonPath('meta.pph', 180000)->assertJsonPath('meta.final_bill', 9810000)
            ->assertJsonPath('data.0.money_out_number', 'UJ07')->assertJsonPath('data.0.vendor_name', 'VA')->assertJsonPath('data.0.weight', 30)
            ->assertJsonPath('data.1.vendor_name', 'Vendor B')->assertJsonPath('data.1.bill', 6000000);
        $this->assertSame(['number', 'id', 'vehicle_number', 'money_out_number', 'route_name', 'vendor_name', 'weight', 'price', 'bill'], array_keys($response->json('data.0')));
        $this->assertStringNotContainsString('777777', $response->getContent());
        $this->getJson('/api/v1/customers/2/bills', $headers)->assertOk()->assertJsonPath('meta.weight_basis', 'muat')->assertJsonPath('data.0.weight', 30.5)->assertJsonPath('meta.ppn', 0);
        $this->getJson('/api/v1/customers/1/bills?search=512', $headers)->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.vehicle_number', '512');
        $this->getJson('/api/v1/customers/999/bills', $headers)->assertNotFound();
    }

    public function test_web_category_menu_and_additional_category_are_read_only(): void
    {
        $this->actingAs($this->gm)->get('/home')->assertOk()->assertSee(url('billing/nota-tagihan/__ID__'), false);
        $this->get('/billing/nota-tagihan/1')->assertOk()->assertSee('HPP')->assertSee('KOMPENSASI')->assertDontSee('PENYESUAIAN<br>BBM', false)
            ->assertSee(route('billing.nota-tagihan.detail-jenis', ['customer' => 1, 'jenis' => 'kompensasi_jr']));
        $this->get('/billing/nota-tagihan/1/kompensasi_jr')->assertOk()->assertSee('Kompensasi Jalan Rusak')->assertSee('UJ08')->assertDontSee('UJ07')
            ->assertDontSee('lanjutForm"', false)->assertDontSee('Nominal DPP')->assertDontSee('Keranjang')->assertDontSee('images/billing.svg');
        $this->get('/billing/nota-tagihan/1/unknown')->assertStatus(405);
        $this->post('/billing/nota-tagihan/1/kompensasi_jr', ['dpp' => 1000])->assertForbidden();
        $this->get('/billing/nota-tagihan/1/kompensasi_jr/keranjang')->assertForbidden();
        // HPP: GM kembali ke menu kategori.
        $this->get('/transaksi/nota-tagihan/1')->assertOk()->assertSee(route('billing.nota-tagihan', 1));
    }

    public function test_mobile_additional_category_lists_tonnage_and_physical_note_without_money(): void
    {
        $headers = $this->headers($this->gm);
        $this->getJson('/api/v1/customers/1/bills', $headers)->assertOk()
            ->assertJsonPath('meta.kind', 'utama')->assertJsonPath('meta.categories', ['utama' => 'HPP', 'kompensasi_jr' => 'Kompensasi Jalan Rusak']);
        $response = $this->getJson('/api/v1/customers/1/bills?kind=kompensasi_jr', $headers)->assertOk()
            ->assertJsonPath('meta.total', 1)->assertJsonPath('meta.total_load_weight', 30.5)->assertJsonPath('meta.total_unload_weight', 30)
            ->assertJsonPath('data.0.money_out_number', 'UJ08')->assertJsonPath('data.0.physical_note', false)->assertJsonMissingPath('meta.total_bill');
        $this->assertSame(['number', 'id', 'vehicle_number', 'money_out_number', 'route_name', 'vendor_name', 'load_weight', 'unload_weight', 'physical_note'], array_keys($response->json('data.0')));
        $this->getJson('/api/v1/customers/1/bills?kind=achievement', $headers)->assertUnprocessable();
        $this->getJson('/api/v1/customers/2/bills', $headers)->assertOk()->assertJsonPath('meta.categories', ['utama' => 'HPP']);
    }

    public function test_other_roles_cannot_read_customer_bills(): void
    {
        $ops = User::factory()->create(['username' => 'ops', 'password' => 'secret-pass', 'role' => 'operasional', 'vendor_id' => null]);
        $this->getJson('/api/v1/customer-options', $this->headers($ops))->assertForbidden();
        $this->getJson('/api/v1/customers/1/bills', $this->headers($ops))->assertForbidden();
        $this->getJson('/api/v1/me', $this->headers($ops))->assertJsonPath('data.permissions.customer_bills', false);
        $this->app['auth']->forgetGuards();
        $this->actingAs($ops)->get('/transaksi/nota-tagihan/1')->assertForbidden();
        $this->get('/billing/nota-tagihan/1')->assertForbidden();
        $this->get('/billing/nota-tagihan/1/kompensasi_jr')->assertForbidden();
    }
}
