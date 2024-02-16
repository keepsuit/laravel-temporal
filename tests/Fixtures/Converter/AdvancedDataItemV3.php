<?php

namespace Keepsuit\LaravelTemporal\Tests\Fixtures\Converter;

use Keepsuit\LaravelTemporal\Integrations\LaravelData\TemporalSerializableData;
use Spatie\LaravelData\Attributes\WithCastAndTransformer;
use Spatie\LaravelData\Data;

class AdvancedDataItemV3 extends Data
{
    public function __construct(
        #[WithCastAndTransformer(TemporalSerializableData::class)]
        public TemporalSerializableItem $item,
    ) {
    }
}
