<?php

namespace Keepsuit\LaravelTemporal\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Keepsuit\LaravelTemporal\LaravelTemporalServiceProvider;
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

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_laravel-temporal_table.php.stub';
        $migration->up();
        */
    }
}
