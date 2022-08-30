<?php

declare(strict_types=1);

namespace Keepsuit\LaravelTemporal\Integrations\LaravelData;

use Keepsuit\LaravelTemporal\Contracts\TemporalSerializable;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Uncastable;
use Spatie\LaravelData\Support\DataProperty;

class TemporalSerializableCast implements Cast
{
    public function __construct(
        protected ?string $type = null
    ) {
    }

    public function cast(DataProperty $property, mixed $value, array $context): mixed
    {
        $type = $this->type ?? $property->type->findAcceptedTypeForBaseType(TemporalSerializable::class);

        if (! class_exists($type)) {
            return Uncastable::create();
        }

        if ($value instanceof $type) {
            return $value;
        }

        if (! is_array($value)) {
            return Uncastable::create();
        }

        /** @var class-string<TemporalSerializable> $type */
        return $type::fromTemporalPayload($value);
    }
}
