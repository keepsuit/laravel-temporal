<?php

namespace Keepsuit\LaravelTemporal\Tests;

use Illuminate\Config\Repository;
use Illuminate\Database\Eloquent\Factories\Factory;
use Keepsuit\LaravelTemporal\LaravelTemporalServiceProvider;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Activities\DemoActivityWithInterface;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Activities\DemoLocalActivity;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows\ActivityOptionsWorkflow;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows\DemoWorkflow;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows\TesterWorkflow;
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

    protected function getPackageProviders($app)
    {
        return [
            LaravelTemporalServiceProvider::class,
        ];
    }

    public function defineEnvironment($app)
    {
        tap($app['config'], function (Repository $config) {
            $config->set('database.default', 'testing');
            $config->set('temporal.workflows', [
                ActivityOptionsWorkflow::class,
                DemoWorkflow::class,
                TesterWorkflow::class,
            ]);
            $config->set('temporal.activities', [
                DemoActivityWithInterface::class,
                DemoLocalActivity::class,
            ]);
        });
    }
}
