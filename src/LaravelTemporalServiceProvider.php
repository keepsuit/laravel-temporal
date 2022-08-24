<?php

namespace Keepsuit\LaravelTemporal;

use Keepsuit\LaravelTemporal\Commands\LaravelTemporalCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelTemporalServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-temporal')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-temporal_table')
            ->hasCommand(LaravelTemporalCommand::class);
    }
}
