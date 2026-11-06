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
