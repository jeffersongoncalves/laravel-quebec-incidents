<?php

namespace JeffersonGoncalves\QuebecIncidents\Tests;

use JeffersonGoncalves\QuebecIncidents\QuebecIncidentsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            QuebecIncidentsServiceProvider::class,
        ];
    }
}
