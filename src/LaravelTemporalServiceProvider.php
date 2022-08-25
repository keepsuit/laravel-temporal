<?php

namespace Keepsuit\LaravelTemporal;

use Illuminate\Foundation\Application;
use Keepsuit\LaravelTemporal\Commands\WorkCommand;
use Keepsuit\LaravelTemporal\Support\ServerStateFile;
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
            ->hasCommands([
                WorkCommand::class,
            ]);
    }

    public function registeringPackage(): void
    {
        $this->app->bind(ServerStateFile::class, fn (Application $app) => new ServerStateFile(
            $app['config']->get('temporal.state_file', storage_path('logs/temporal-server-state.json'))
        ));
    }
}
