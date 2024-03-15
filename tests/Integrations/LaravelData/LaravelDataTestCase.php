<?php

namespace Keepsuit\LaravelTemporal\Tests\Integrations\LaravelData;

use Illuminate\Config\Repository;
use Keepsuit\LaravelTemporal\Contracts\TemporalSerializable;
use Keepsuit\LaravelTemporal\Integrations\LaravelData\TemporalSerializableCastAndTransformer;
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

    public function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        tap($app['config'], function (Repository $config) {
            $config->set('data.features.cast_and_transform_iterables', true);
            $config->set('data.casts', [
                ...$config->get('data.casts', []),
                TemporalSerializable::class => TemporalSerializableCastAndTransformer::class,
            ]);
            $config->set('data.transformers', [
                ...$config->get('data.transformers', []),
                TemporalSerializable::class => TemporalSerializableCastAndTransformer::class,
            ]);
        });
    }
}
