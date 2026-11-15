<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        $this->clearGuardableColumns();
        parent::setUp();
    }

    /** Role eksekutif yang berbagi fitur (lihat User::EXECUTIVE_ROLES). */
    public static function executiveRoles(): array
    {
        return ['general manager' => ['general-manager'], 'direksi' => ['direksi']];
    }

    protected function tearDown(): void
    {
        $this->clearGuardableColumns();
        parent::tearDown();
    }

    private function clearGuardableColumns(): void
    {
        // Fixtures replace partial SQLite schemas; Eloquent retains this cache across app resets.
        (new \ReflectionProperty(Model::class, 'guardableColumns'))->setValue(null, []);
    }
}
