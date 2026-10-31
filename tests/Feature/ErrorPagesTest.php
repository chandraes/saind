<?php

namespace Tests\Feature;

use Illuminate\Contracts\Foundation\MaintenanceMode;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\TestWith;
use RuntimeException;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.debug' => false]);
        Route::get('/__test-error/{status}', function (int $status): never {
            abort($status, 'Internal sensitive diagnostic', ['Retry-After' => '60']);
        });
    }

    #[TestWith([401, 'Perlu login untuk melanjutkan', 'checkpoint'])]
    #[TestWith([402, 'Pembayaran diperlukan', 'payment'])]
    #[TestWith([403, 'Akses halaman dibatasi', 'barrier'])]
    #[TestWith([404, 'Halaman tidak ditemukan', 'missing'])]
    #[TestWith([419, 'Sesi Anda telah berakhir', 'expired'])]
    #[TestWith([429, 'Terlalu banyak permintaan', 'queue'])]
    #[TestWith([500, 'Terjadi gangguan sistem', 'breakdown'])]
    #[TestWith([503, 'Sistem sedang dalam pemeliharaan', 'service'])]
    public function test_http_errors_use_the_matching_page_and_keep_their_status(int $status, string $title, string $scene): void
    {
        $this->get('/__test-error/'.$status)
            ->assertStatus($status)
            ->assertHeader('Retry-After', '60')
            ->assertSee($title)
            ->assertSee('data-error-scene="'.$scene.'"', false)
            ->assertDontSee('Internal sensitive diagnostic')
            ->assertDontSee('Kontrol mockup');
    }

    public function test_maintenance_middleware_uses_the_custom_503_without_changing_live_maintenance_state(): void
    {
        $maintenance = $this->createMock(MaintenanceMode::class);
        $maintenance->method('active')->willReturn(true);
        $maintenance->method('data')->willReturn(['status' => 503, 'retry' => 120]);
        $this->app->instance(MaintenanceMode::class, $maintenance);

        $this->get('/')->assertStatus(503)->assertHeader('Retry-After', '120')
            ->assertSee('Sistem sedang dalam pemeliharaan')
            ->assertSee('data-error-scene="service"', false)
            ->assertSee('class="repair-arm"', false);
    }

    public function test_json_errors_keep_the_existing_json_response(): void
    {
        $this->getJson('/__test-error/503')
            ->assertStatus(503)
            ->assertJsonPath('message', 'Internal sensitive diagnostic')
            ->assertDontSee('<!doctype html>', false);
    }

    public function test_unhandled_exception_uses_safe_500_page_without_database_access(): void
    {
        config(['database.default' => 'unavailable']);
        Route::get('/__test-server-failure', function (): never {
            throw new RuntimeException('Private diagnostic details');
        });
        $this->get('/__test-server-failure')->assertStatus(500)
            ->assertSee('Terjadi gangguan sistem')
            ->assertSee('data-error-scene="breakdown"', false)
            ->assertDontSee('Private diagnostic details');
    }

    public function test_unknown_url_renders_custom_404_without_authentication(): void
    {
        $this->get('/__unknown-error-page-test__')->assertNotFound()
            ->assertSee('Halaman tidak ditemukan')
            ->assertSee('data-error-scene="missing"', false);
    }
}
