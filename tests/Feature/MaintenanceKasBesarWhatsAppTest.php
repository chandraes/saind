<?php

namespace Tests\Feature;

use App\Models\GroupWa;
use App\Models\KasBesar;
use App\Models\Vendor;
use App\Services\MaintenanceKasBesarWhatsAppService;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class MaintenanceKasBesarWhatsAppTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        (require database_path('migrations/2023_09_06_160726_create_group_was_table.php'))->up();
        GroupWa::create(['untuk' => 'kas-besar', 'nama_group' => 'kas-test']);
        $messenger = new class extends GroupWa
        {
            public array $sent = [];

            public ?string $failure = null;

            public function sendWa($to, $text)
            {
                $this->sent[] = ['to' => $to, 'text' => $text];

                return $to !== $this->failure;
            }
        };
        $this->app->instance(GroupWa::class, $messenger);
    }

    private function cash(): KasBesar
    {
        return new KasBesar(['uraian' => 'Penggantian unit 101', 'nominal_transaksi' => 500000, 'saldo' => 7654321, 'modal_investor_terakhir' => 9876543, 'bank' => 'BCA', 'transfer_ke' => 'Bengkel', 'no_rekening' => '123456']);
    }

    #[TestWith(['BAN', '081234567890', '081234567890'])]
    #[TestWith(['AKI', '+62 812-3456-7890', '6281234567890'])]
    #[TestWith(['FILTER & OLI MESIN', '6281234567890', '6281234567890'])]
    public function test_vendor_gets_transaction_without_private_balances(string $maintenance, string $phone, string $expected): void
    {
        app(MaintenanceKasBesarWhatsAppService::class)->notify($this->cash(), $maintenance, new Vendor(['no_hp' => $phone]));
        $messages = app(GroupWa::class)->sent;
        $this->assertCount(2, $messages);
        $this->assertSame('kas-test', $messages[0]['to']);
        $this->assertSame($expected, $messages[1]['to']);
        foreach (['FORM PENGGANTIAN '.$maintenance, 'Penggantian unit 101', 'Rp. 500.000', 'BCA', '123456', 'Bengkel'] as $part) {
            $this->assertStringContainsString($part, $messages[1]['text']);
        }
        foreach (['Sisa Saldo Kas Besar', 'Total Modal Investor', '7.654.321', '9.876.543'] as $part) {
            $this->assertStringNotContainsString($part, $messages[1]['text']);
        }
        $this->assertStringContainsString('7.654.321', $messages[0]['text']);
        $this->assertStringContainsString('9.876.543', $messages[0]['text']);
    }

    #[TestWith([null])]
    #[TestWith([''])]
    #[TestWith(['-'])]
    #[TestWith(['0'])]
    #[TestWith(['0812ABC7890'])]
    #[TestWith(['081234567890999999'])]
    #[TestWith(['0812/34567890'])]
    public function test_invalid_vendor_phone_is_skipped(?string $phone): void
    {
        app(MaintenanceKasBesarWhatsAppService::class)->notify($this->cash(), 'AKI', new Vendor(['no_hp' => $phone]));
        $this->assertCount(1, app(GroupWa::class)->sent);
        $this->assertSame('kas-test', app(GroupWa::class)->sent[0]['to']);
    }

    public function test_group_failure_does_not_prevent_vendor_delivery(): void
    {
        app(GroupWa::class)->failure = 'kas-test';
        try {
            app(MaintenanceKasBesarWhatsAppService::class)->notify($this->cash(), 'BAN', new Vendor(['no_hp' => '081234567890']));
            $this->fail('Must report partial delivery failure');
        } catch (\RuntimeException $exception) {
            $this->assertCount(2, app(GroupWa::class)->sent);
            $this->assertSame('081234567890', app(GroupWa::class)->sent[1]['to']);
        }
    }

    public function test_missing_group_still_attempts_valid_vendor(): void
    {
        DB::table('group_was')->delete();
        try {
            app(MaintenanceKasBesarWhatsAppService::class)->notify($this->cash(), 'BAN', new Vendor(['no_hp' => '081234567890']));
            $this->fail('Must report missing group');
        } catch (\RuntimeException $exception) {
            $this->assertCount(1, app(GroupWa::class)->sent);
            $this->assertSame('081234567890', app(GroupWa::class)->sent[0]['to']);
        }
    }

    public function test_vendor_failure_does_not_prevent_group_delivery(): void
    {
        app(GroupWa::class)->failure = '081234567890';
        try {
            app(MaintenanceKasBesarWhatsAppService::class)->notify($this->cash(), 'AKI', new Vendor(['no_hp' => '081234567890']));
            $this->fail('Must report vendor delivery failure');
        } catch (\RuntimeException $exception) {
            $this->assertCount(2, app(GroupWa::class)->sent);
            $this->assertSame('kas-test', app(GroupWa::class)->sent[0]['to']);
        }
    }
}
