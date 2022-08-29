<?php

namespace Keepsuit\LaravelTemporal;

use Illuminate\Foundation\Application;
use Keepsuit\LaravelTemporal\Commands\ActivityInterfaceMakeCommand;
use Keepsuit\LaravelTemporal\Commands\ActivityMakeCommand;
use Keepsuit\LaravelTemporal\Commands\WorkCommand;
use Keepsuit\LaravelTemporal\Commands\WorkflowInterfaceMakeCommand;
use Keepsuit\LaravelTemporal\Commands\WorkflowMakeCommand;
use Keepsuit\LaravelTemporal\Support\ServerStateFile;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Temporal\Client\GRPC\ServiceClient;
use Temporal\Client\GRPC\ServiceClientInterface;

class LaravelTemporalServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-temporal')
            ->hasConfigFile()
            ->hasCommands([
                WorkCommand::class,
                WorkflowInterfaceMakeCommand::class,
                WorkflowMakeCommand::class,
                ActivityInterfaceMakeCommand::class,
                ActivityMakeCommand::class,
            ]);
    }

    public function registeringPackage(): void
    {
        $this->app->bind(ServerStateFile::class, fn (Application $app) => new ServerStateFile(
            $app['config']->get('temporal.state_file', storage_path('logs/temporal-worker-state.json'))
        ));

        $this->app->bind(ServiceClientInterface::class, fn (Application $app) => ServiceClient::create(config('temporal.address')));
    }
}
