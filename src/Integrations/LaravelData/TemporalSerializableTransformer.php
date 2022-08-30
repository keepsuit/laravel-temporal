<?php

declare(strict_types=1);

namespace Keepsuit\LaravelTemporal\Integrations\LaravelData;

use Keepsuit\LaravelTemporal\Contracts\TemporalSerializable;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Transformers\Transformer;

class TemporalSerializableTransformer implements Transformer
{
    public function transform(DataProperty $property, mixed $value): mixed
    {
        if (! $value instanceof TemporalSerializable) {
            return $value;
        }

        return $value->toTemporalPayload();
    }
}
