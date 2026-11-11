<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class VendorOperationalPerformUnitTest extends MobileApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        View::share(['global_app_name' => 'SAIND', 'global_app_favicon' => '', 'global_app_nav_bg' => '#212529', 'global_app_logo' => '']);
        Schema::create('rutes', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->integer('jarak');
        });
        Schema::create('kas_uang_jalans', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('rute_id');
            $table->date('tanggal');
        });
        Schema::create('transaksis', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('kas_uang_jalan_id');
            $table->boolean('void')->default(false);
            $table->decimal('timbangan_bongkar', 10, 3);
        });
        DB::table('vendors')->insert([
            ['id' => 1, 'nama' => 'OWN VENDOR', 'nickname' => 'OWN VENDOR', 'pembayaran' => 'mingguan'],
            ['id' => 2, 'nama' => 'FOREIGN VENDOR', 'nickname' => 'FOREIGN VENDOR', 'pembayaran' => 'mingguan'],
        ]);
        DB::table('vehicles')->insert([
            ['id' => 1, 'vendor_id' => 1, 'nomor_lambung' => 'OWN-UNIT', 'nopol' => 'BG OWN'],
            ['id' => 2, 'vendor_id' => 2, 'nomor_lambung' => 'FOREIGN-UNIT', 'nopol' => 'BG FOREIGN'],
            ['id' => 3, 'vendor_id' => 1, 'nomor_lambung' => 'OWN-IDLE-UNIT', 'nopol' => 'BG IDLE'],
        ]);
        DB::table('rutes')->insert([
            ['id' => 1, 'nama' => 'OWN ROUTE', 'jarak' => 75],
            ['id' => 2, 'nama' => 'FOREIGN ROUTE', 'jarak' => 40],
        ]);
        foreach ([1, 2] as $vendor) {
            DB::table('kas_uang_jalans')->insert(['id' => $vendor, 'vendor_id' => $vendor, 'vehicle_id' => $vendor, 'rute_id' => $vendor, 'tanggal' => '2026-11-01']);
            DB::table('transaksis')->insert(['id' => $vendor, 'kas_uang_jalan_id' => $vendor, 'timbangan_bongkar' => $vendor === 1 ? 30.125 : 77.777]);
        }
        $this->actingAs(User::factory()->create(['username' => 'perform-reader', 'password' => 'secret-pass', 'role' => 'vendor-operational', 'vendor_id' => 1]));
    }

    public function test_web_and_print_force_own_vendor_and_keep_all_own_unit_columns(): void
    {
        $this->travelTo('2026-12-05 09:00:00');
        $before = DB::table('transaksis')->get()->toArray();
        foreach (['/statistik/perform-unit?bulan=11&tahun=2026', '/statistik/perform-unit/print?start_date=2026-11-01&end_date=2026-11-30'] as $path) {
            $response = $this->get($path.'&vendor=2')
                ->assertOk()->assertSee('OWN-UNIT')->assertSee('OWN-IDLE-UNIT')->assertSee('OWN ROUTE')
                ->assertDontSee('FOREIGN-UNIT')->assertDontSee('FOREIGN VENDOR')->assertDontSee('FOREIGN ROUTE');
            $response->assertViewHas('vendor', 1)->assertViewHas('jumlah_vehicle', 2)->assertViewHas('grand_total_tonase', 30.125)
                ->assertViewHas('vehicle', fn ($units): bool => $units->pluck('vendor_id')->unique()->all() === [1]);
            $this->assertCount(30, $response->viewData('dates_array'));
            $this->assertEqualsCanonicalizing(['OWN-UNIT', 'OWN-IDLE-UNIT'], array_keys($response->viewData('statistics')));
            $html = new \DOMDocument;
            @$html->loadHTML($response->getContent());
            $xpath = new \DOMXPath($html);
            $this->assertSame(0, $xpath->query('//form[translate(@method,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz")="post" and not(contains(@action,"/logout"))]')->length);
            $this->assertSame(0, $xpath->query('//select[@name="vendor"]')->length);
            $this->assertSame(0, $xpath->query('//input[@name="_method"]')->length);
        }
        $this->assertEquals($before, DB::table('transaksis')->get()->toArray());
        $this->travelBack();
    }

    public function test_web_filters_by_month_and_year_instead_of_a_date_range(): void
    {
        $this->travelTo('2026-11-15 10:00:00');
        $response = $this->get('/statistik/perform-unit?bulan=10&tahun=2026&start_date=2026-11-01&end_date=2026-11-03')->assertOk()
            ->assertViewHas('start_date', '2026-10-01')->assertViewHas('end_date', '2026-10-31')->assertViewHas('total_days', 31)
            ->assertViewHas('bulan', 10)->assertViewHas('tahun', 2026)->assertSee('Oktober 2026');
        $html = new \DOMDocument;
        @$html->loadHTML($response->getContent());
        $xpath = new \DOMXPath($html);
        $this->assertSame(1, $xpath->query('//select[@name="bulan"]')->length);
        $this->assertSame(1, $xpath->query('//select[@name="tahun"]')->length);
        $this->assertSame(0, $xpath->query('//input[@name="start_date" and @type="date"]')->length);
        $this->assertSame('10', $xpath->query('//select[@name="bulan"]/option[@selected]')->item(0)?->getAttribute('value'));

        // Bulan berjalan berhenti di hari ini, seperti tampilan sebelumnya.
        $this->get('/statistik/perform-unit')->assertOk()->assertViewHas('start_date', '2026-11-01')->assertViewHas('end_date', '2026-11-15')
            ->assertViewHas('daftar_tahun', [2026]);
        $this->get('/statistik/perform-unit?bulan=13&tahun=2026')->assertSessionHasErrors('bulan');
        $this->travelBack();
    }

    public function test_print_caps_the_date_matrix_at_32_days_and_normalizes_reversed_ranges(): void
    {
        foreach (['/statistik/perform-unit/print'] as $path) {
            $response = $this->get($path.'?vendor=2&start_date=2026-11-01&end_date=2027-01-31')->assertOk()
                ->assertViewHas('end_date', '2026-12-02')->assertViewHas('total_days', 32);
            $this->assertCount(32, $response->viewData('dates_array'));
            foreach ($response->viewData('statistics') as $statistic) {
                $this->assertCount(32, $statistic['data']);
            }
            $reverse = $this->get($path.'?start_date=2026-11-03&end_date=2026-11-01')->assertOk()
                ->assertViewHas('end_date', '2026-11-03')->assertViewHas('total_days', 1);
            $this->assertSame(['2026-11-03'], $reverse->viewData('dates_array'));
        }
    }
}
