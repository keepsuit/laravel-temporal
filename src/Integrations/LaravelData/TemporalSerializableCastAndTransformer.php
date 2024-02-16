<?php

namespace Keepsuit\LaravelTemporal\Integrations\LaravelData;

use Illuminate\Container\Container;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Transformers\Transformer;

if (LaravelDataHelpers::version() === 4) {
    class TemporalSerializableCastAndTransformer implements Cast, Transformer
    {
        public function __construct(
            protected ?string $type = null
        ) {
        }

        public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
        {
            $serializer = Container::getInstance()->make(LaravelDataTemporalSerializer::class);
            assert($serializer instanceof LaravelDataTemporalSerializer);

            return $serializer->cast($property, $value, $this->type);
        }

        public function transform(DataProperty $property, mixed $value, TransformationContext $context): mixed
        {
            $serializer = Container::getInstance()->make(LaravelDataTemporalSerializer::class);

            return $serializer->transform($value);
        }
    }
} else {
    class TemporalSerializableCastAndTransformer implements Cast, Transformer
    {
        public function __construct(
            protected ?string $type = null
        ) {
        }

        public function cast(DataProperty $property, mixed $value, array $context): mixed
        {
            $serializer = Container::getInstance()->make(LaravelDataTemporalSerializer::class);
            assert($serializer instanceof LaravelDataTemporalSerializer);

            return $serializer->cast($property, $value, $this->type);
        }

        public function transform(DataProperty $property, mixed $value): mixed
        {
            $serializer = Container::getInstance()->make(LaravelDataTemporalSerializer::class);
            assert($serializer instanceof LaravelDataTemporalSerializer);

            return $serializer->transform($value);
        }
    }
}
