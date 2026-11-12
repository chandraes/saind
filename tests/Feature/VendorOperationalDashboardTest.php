<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\DataProvider;
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

    /** @return array<string, array{string, list<string>}> */
    public static function vendorAccounts(): array
    {
        $documents = ['Nota Muat Masuk', 'Nota Bongkar', 'Nota Bayar Vendor', 'Invoice Vendor'];

        return [
            'vendor operational' => ['vendor-operational', $documents],
            'vendor' => ['vendor', [...$documents, 'Kas Vendor']],
        ];
    }

    #[DataProvider('vendorAccounts')]
    public function test_dashboard_groups_the_same_menus_for_both_vendor_accounts(string $role, array $documents): void
    {
        $this->actingAs(User::factory()->make(['id' => 1, 'password' => 'test-password', 'role' => $role, 'vendor_id' => 1]));
        $response = $this->get('/home')->assertOk();
        $response->assertSeeInOrder(['Nota &amp; Invoice', ...$documents, 'Performa &amp; Upah Gendong', 'Perform Unit', 'Perform Unit All', 'Upah Gendong',
            'Maintenance', 'Ban Luar', 'Aki', 'Filter &amp; Oli Mesin'], false)
            ->assertDontSee('MAINTENANCE VEHICLE')->assertDontSee('maintenaceModal')->assertDontSee('Statistik Vendor')
            ->assertDontSee('INVOICE BAYAR VENDOR');
        $dom = new \DOMDocument;
        @$dom->loadHTML($response->getContent());
        $xpath = new \DOMXPath($dom);
        $this->assertSame(count($documents), $xpath->query('//*[@data-vendor-menu-row="documents"]/a')->length);
        $this->assertSame(3, $xpath->query('//*[@data-vendor-menu-row="statistics"]/a')->length);
        $this->assertSame(3, $xpath->query('//*[@data-vendor-menu-row="maintenance"]/a')->length);
        $this->assertSame(1, $xpath->query('//*[@data-vendor-menu-row="statistics"]/a[@href="'.route('statistik.perform-unit.all-vendor').'"]')->length);
        $this->assertSame($role === 'vendor' ? 1 : 0, $xpath->query('//a[@href="'.route('kas-per-vendor.index', 1).'"]')->length);
        $this->assertSame(0, $xpath->query('//*[@data-vendor-dashboard]//form')->length);
        // Versioned so browsers drop a cached stylesheet when the grid changes.
        $response->assertSee('vendor-operational-dashboard.css?v='.filemtime(public_path('assets/css/vendor-operational-dashboard.css')), false);
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
