<?php

namespace JeffersonGoncalves\QuebecIncidents;

use JeffersonGoncalves\QuebecIncidents\Commands\OverdueCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class QuebecIncidentsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('quebec-incidents')
            ->hasConfigFile()
            ->hasMigrations(['create_quebec_incidents_table'])
            ->hasCommand(OverdueCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(IncidentManager::class);
    }
}
