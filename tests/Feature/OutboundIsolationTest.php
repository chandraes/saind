<?php

namespace Tests\Feature;

use Illuminate\Http\Client\StrayRequestException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OutboundIsolationTest extends TestCase
{
    public function test_test_environment_blocks_unfaked_http_and_has_no_live_attendance_credentials(): void
    {
        $this->assertTrue(Http::preventingStrayRequests());
        $this->assertFalse((bool) config('attendance.enabled'));
        $this->assertSame('https://attendance.test', config('attendance.url'));
        $this->assertSame('', config('attendance.token'));
        $this->expectException(StrayRequestException::class);
        Http::get('https://unfaked-request.invalid/isolation-check');
    }
}
