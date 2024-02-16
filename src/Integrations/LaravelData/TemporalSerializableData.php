<?php

namespace Keepsuit\LaravelTemporal\Integrations\LaravelData;

use Illuminate\Container\Container;
use Illuminate\Support\Arr;
use Illuminate\Support\Enumerable;
use Keepsuit\LaravelTemporal\Contracts\TemporalSerializable;
use Keepsuit\LaravelTemporal\Support\TemporalSerializer;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Uncastable;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Transformers\Transformer;

class TemporalSerializableData implements Cast, Transformer
{
    public function __construct(
        protected ?string $type = null
    ) {
    }

    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        $serializer = Container::getInstance()->make(TemporalSerializer::class);
        assert($serializer instanceof TemporalSerializer);

        $enumerableClass = $property->type->findAcceptedTypeForBaseType(\Illuminate\Support\Enumerable::class);
        $isArrayOrCollection = $enumerableClass !== null || $property->type->acceptsType('array');

        if ($isArrayOrCollection && $this->type === null) {
            return Uncastable::create();
        }

        if ($isArrayOrCollection) {
            $items = Arr::map($value, fn ($item) => $serializer->deserialize($item, $this->type));

            return $enumerableClass !== null ? $enumerableClass::make($items) : $items;
        }

        $type = $this->type ?? $property->type->findAcceptedTypeForBaseType(TemporalSerializable::class);

        try {
            return $serializer->deserialize($value, $type);
        } catch (\Throwable $e) {
            ray($e);

            return Uncastable::create();
        }
    }

    public function transform(DataProperty $property, mixed $value, TransformationContext $context): mixed
    {
        $serializer = Container::getInstance()->make(TemporalSerializer::class);

        if ($value instanceof Enumerable) {
            return $value->map(fn ($item) => $serializer->serialize($item))->toArray();
        }

        if (is_array($value)) {
            return Arr::map($value, fn ($item) => $serializer->serialize($item));
        }

        if ($value instanceof TemporalSerializable) {
            return $serializer->serialize($value);
        }

        return $value;
    }
}
