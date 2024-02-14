<?php

declare(strict_types=1);

namespace Keepsuit\LaravelTemporal\Integrations\LaravelData;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use Illuminate\Container\Container;
use Keepsuit\LaravelTemporal\Support\TemporalSerializer;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Transformers\Transformer;

if (InstalledVersions::satisfies(new VersionParser(), 'spatie/laravel-data', '^4.0')) {
    class TemporalSerializableTransformer implements Transformer
    {
        public function transform(DataProperty $property, mixed $value, TransformationContext $context): mixed
        {
            $serializer = Container::getInstance()->make(TemporalSerializer::class);

            return $serializer->serialize($value);
        }
    }
} else {
    class TemporalSerializableTransformer implements Transformer
    {
        public function transform(DataProperty $property, mixed $value): mixed
        {
            $serializer = Container::getInstance()->make(TemporalSerializer::class);

            return $serializer->serialize($value);
        }
    }
}
