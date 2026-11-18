<?php

namespace JeffersonGoncalves\QuebecIncidents;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class QuebecIncidentsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-quebec-incidents')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations();
    }
}
