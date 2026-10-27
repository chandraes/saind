<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Http;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        config(['attendance.enabled' => false, 'attendance.url' => 'https://attendance.test',
            'attendance.token' => '', 'attendance.send_immediately' => false]);
        Http::preventStrayRequests();

        return $app;
    }
}
