<?php

declare(strict_types=1);

namespace Keepsuit\LaravelTemporal\Integrations\LaravelData;

use Illuminate\Support\Arr;
use Keepsuit\LaravelTemporal\Contracts\TemporalSerializable;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Transformers\Transformer;

class TemporalSerializableTransformer implements Transformer
{
    public function transform(DataProperty $property, mixed $value, TransformationContext $context): mixed
    {
        if ($value instanceof TemporalSerializable) {
            return $value->toTemporalPayload();
        }

        if (is_array($value)) {
            return Arr::map($value, function ($data) {
                if ($data instanceof TemporalSerializable) {
                    return $data->toTemporalPayload();
                }

                return $data;
            });
        }

        return $value;
    }
}
