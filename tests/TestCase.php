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

    /** Admin & SU: di aplikasi mobile membaca menu eksekutif (lihat User::isMobileAdministrator()). */
    public static function administratorRoles(): array
    {
        return ['admin' => ['admin'], 'su' => ['su']];
    }

    /** Pembaca menu eksekutif di aplikasi mobile: eksekutif serta Admin & SU. */
    public static function mobileExecutiveMenuRoles(): array
    {
        return [...self::executiveRoles(), ...self::administratorRoles()];
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
