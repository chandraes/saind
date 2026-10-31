<?php

namespace Tests\Feature;

use App\Services\StarSender;
use App\Services\WaStatus;
use ReflectionProperty;
use Tests\TestCase;

class WhatsAppConfigurationTest extends TestCase
{
    public function test_whatsapp_services_read_the_configured_key_without_runtime_environment(): void
    {
        config(['services.starsender.key' => 'configured-test-key']);

        foreach ([new StarSender('test-target', 'test-message'), new WaStatus] as $service) {
            $key = new ReflectionProperty($service, 'apikey');
            $this->assertTrue($key->getValue($service) === 'configured-test-key', 'Service harus memakai key dari konfigurasi.');
        }
    }

    public function test_services_configuration_defines_the_starsender_key(): void
    {
        $services = require config_path('services.php');

        $this->assertArrayHasKey('starsender', $services);
        $this->assertArrayHasKey('key', $services['starsender']);
    }
}
