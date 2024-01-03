<?php

namespace Keepsuit\LaravelTemporal\Tests;

use Illuminate\Config\Repository;
use Illuminate\Database\Eloquent\Factories\Factory;
use Keepsuit\LaravelTemporal\LaravelTemporalServiceProvider;
use Keepsuit\LaravelTemporal\Support\DiscoverActivities;
use Keepsuit\LaravelTemporal\Support\DiscoverWorkflows;
use Keepsuit\LaravelTemporal\TemporalRegistry;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Keepsuit\\LaravelTemporal\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelTemporalServiceProvider::class,
        ];
    }

    public function defineEnvironment($app): void
    {
        tap($app['config'], function (Repository $config) {
            $config->set('database.default', 'testing');
        });

        tap($app->make(TemporalRegistry::class), function (TemporalRegistry $registry) {
            $registry->registerWorkflows(...DiscoverWorkflows::within(__DIR__.'/Fixtures/WorkflowDiscovery/Workflows'))
                ->registerActivities(...DiscoverActivities::within(__DIR__.'/Fixtures/WorkflowDiscovery/Activities'));
        });
    }
}
