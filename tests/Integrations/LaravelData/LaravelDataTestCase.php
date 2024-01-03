<?php

namespace Keepsuit\LaravelTemporal\Tests\Integrations\LaravelData;

use Keepsuit\LaravelTemporal\Tests\TestCase;
use Spatie\LaravelData\LaravelDataServiceProvider;

class LaravelDataTestCase extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return array_merge([
            LaravelDataServiceProvider::class,
        ], parent::getPackageProviders($app));
    }
}
