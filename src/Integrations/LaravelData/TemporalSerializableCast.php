<?php

declare(strict_types=1);

namespace Keepsuit\LaravelTemporal\Integrations\LaravelData;

use Illuminate\Support\Arr;
use Keepsuit\LaravelTemporal\Contracts\TemporalSerializable;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Uncastable;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class TemporalSerializableCast implements Cast
{
    public function __construct(
        protected ?string $type = null
    ) {
    }

    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        $acceptedType = $property->type->findAcceptedTypeForBaseType(TemporalSerializable::class);
        $type = $this->type ?? $acceptedType;

        if (! class_exists($type)) {
            return Uncastable::create();
        }

        if ($value instanceof $type) {
            return $value;
        }

        if (! is_array($value)) {
            return Uncastable::create();
        }

        if ($acceptedType === null && $property->type->acceptsType('array')) {
            return Arr::map($value, function ($data) use ($type) {
                if ($data instanceof $type) {
                    return $data;
                }

                return $type::fromTemporalPayload($data);
            });
        }

        /** @var class-string<TemporalSerializable> $type */
        return $type::fromTemporalPayload($value);
    }
}
