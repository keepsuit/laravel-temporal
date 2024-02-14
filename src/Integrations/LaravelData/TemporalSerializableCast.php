<?php

declare(strict_types=1);

namespace Keepsuit\LaravelTemporal\Integrations\LaravelData;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use Illuminate\Container\Container;
use Keepsuit\LaravelTemporal\Contracts\TemporalSerializable;
use Keepsuit\LaravelTemporal\Support\TemporalSerializer;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Uncastable;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

if (InstalledVersions::satisfies(new VersionParser(), 'spatie/laravel-data', '^4.0')) {
    class TemporalSerializableCast implements Cast
    {
        public function __construct(
            protected ?string $type = null
        ) {
        }

        public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
        {
            $serializer = Container::getInstance()->make(TemporalSerializer::class);
            assert($serializer instanceof TemporalSerializer);

            $acceptedType = $property->type->findAcceptedTypeForBaseType(TemporalSerializable::class);
            $type = $this->type ?? $acceptedType;

            try {
                return $serializer->deserialize($value, $type, isArray: $property->type->acceptsType('array'));
            } catch (\Throwable) {
                return Uncastable::create();
            }
        }
    }
} else {
    class TemporalSerializableCast implements Cast
    {
        public function __construct(
            protected ?string $type = null
        ) {
        }

        public function cast(DataProperty $property, mixed $value, array $context): mixed
        {
            $serializer = Container::getInstance()->make(TemporalSerializer::class);
            assert($serializer instanceof TemporalSerializer);

            $acceptedType = $property->type->findAcceptedTypeForBaseType(TemporalSerializable::class);
            $type = $this->type ?? $acceptedType;

            try {
                return $serializer->deserialize($value, $type, isArray: $property->type->acceptsType('array'));
            } catch (\Throwable) {
                return Uncastable::create();
            }
        }
    }
}
