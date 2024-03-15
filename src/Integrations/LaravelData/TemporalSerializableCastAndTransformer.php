<?php

namespace Keepsuit\LaravelTemporal\Integrations\LaravelData;

use Keepsuit\LaravelTemporal\Contracts\TemporalSerializable;
use Keepsuit\LaravelTemporal\Exceptions\TemporalSerializerException;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\IterableItemCast;
use Spatie\LaravelData\Casts\Uncastable;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Transformers\Transformer;

class TemporalSerializableCastAndTransformer implements Cast, IterableItemCast, Transformer
{
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): TemporalSerializable|Uncastable
    {
        return $this->castValue($property->type->type->findAcceptedTypeForBaseType(TemporalSerializable::class), $value);
    }

    public function castIterableItem(DataProperty $property, mixed $value, array $properties, CreationContext $context): TemporalSerializable|Uncastable
    {
        return $this->castValue($property->type->iterableItemType, $value);
    }

    public function transform(DataProperty $property, mixed $value, TransformationContext $context): mixed
    {
        if ($value instanceof TemporalSerializable) {
            return $value->toTemporalPayload();
        }

        return $value;
    }

    /**
     * @throws TemporalSerializerException
     */
    protected function castValue(string $className, mixed $value): TemporalSerializable|Uncastable
    {
        if (! class_exists($className)) {
            return Uncastable::create();
        }

        if (! is_array($value)) {
            return Uncastable::create();
        }

        /** @var class-string<TemporalSerializable> $className */
        return $className::fromTemporalPayload($value);
    }
}
