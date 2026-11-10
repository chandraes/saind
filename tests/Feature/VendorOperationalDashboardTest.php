<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class VendorOperationalDashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('vendors', function (Blueprint $table): void {
            $table->id();
        });
        DB::table('vendors')->insert(['id' => 1]);
        $this->withoutVite();
        View::share(['global_app_name' => 'SAIND', 'global_app_favicon' => '', 'global_app_nav_bg' => '#212529', 'global_app_logo' => '']);
    }

    public function test_dashboard_shows_two_ordered_menu_rows_without_loading_vehicle_data(): void
    {
        $this->actingAs(User::factory()->make(['id' => 1, 'password' => 'test-password', 'role' => 'vendor-operational', 'vendor_id' => 1]));
        $response = $this->get('/home')->assertOk();
        $response->assertSeeInOrder(['Nota Muat Masuk', 'Nota Bongkar', 'Nota Bayar Vendor', 'Invoice Vendor', 'Perform Unit', 'Perform Unit All', 'Statistik Upah Gendong', 'Statistik Filter &amp; Oli Mesin', 'Statistik Aki', 'Statistik Ban Luar'], false)
            ->assertSee('data-vendor-menu-row="documents"', false)
            ->assertSee('data-vendor-menu-row="statistics"', false)
            ->assertDontSee('MAINTENANCE VEHICLE')->assertDontSee('maintenaceModal');
        $html = $response->getContent();
        $dom = new \DOMDocument;
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        $this->assertSame(4, $xpath->query('//*[@data-vendor-menu-row="documents"]/a')->length);
        $this->assertSame(6, $xpath->query('//*[@data-vendor-menu-row="statistics"]/a')->length);
        $this->assertSame(1, $xpath->query('//*[@data-vendor-menu-row="statistics"]/a[@href="'.route('statistik.perform-unit.all-vendor').'"]')->length);
        $this->assertSame(0, $xpath->query('//*[@data-vendor-dashboard]//form')->length);
    }

    public function test_financial_pages_and_mutations_remain_inaccessible_via_legacy_routes(): void
    {
        $this->actingAs(User::factory()->make(['id' => 1, 'password' => 'test-password', 'role' => 'vendor-operational', 'vendor_id' => 1]));
        foreach ([
            ['GET', '/billing/nota-bayar/1'],
            ['GET', '/transaksi/nota-bayar/1'],
            ['GET', '/billing/transaksi/invoice/bayar'],
            ['GET', '/transaksi/nota-bongkar'],
            ['POST', '/transaksi/nota-bayar/1/masuk-keranjang'],
            ['POST', '/transaksi/nota-bayar/1/lanjut'],
            ['POST', '/billing/nota-bayar/1/achievement'],
        ] as [$method, $url]) {
            $this->json($method, $url)->assertForbidden();
        }
    }

    public function test_vendor_without_valid_assignment_cannot_open_dashboard(): void
    {
        foreach ([null, 99] as $vendorId) {
            $this->actingAs(User::factory()->make(['id' => 1, 'password' => 'test-password', 'role' => 'vendor-operational', 'vendor_id' => $vendorId]));
            $this->getJson('/home')->assertForbidden();
        }
    }
}
