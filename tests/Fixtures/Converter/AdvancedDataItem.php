<?php

namespace Keepsuit\LaravelTemporal\Tests\Fixtures\Converter;

use Keepsuit\LaravelTemporal\Integrations\LaravelData\TemporalSerializableCast;
use Keepsuit\LaravelTemporal\Integrations\LaravelData\TemporalSerializableTransformer;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;

class AdvancedDataItem extends Data
{
    public function __construct(
        #[WithCast(TemporalSerializableCast::class)]
        #[WithTransformer(TemporalSerializableTransformer::class)]
        public TemporalSerializableItem $item,
    ) {
    }
}
