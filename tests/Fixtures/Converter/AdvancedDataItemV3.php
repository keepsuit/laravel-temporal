<?php

namespace Keepsuit\LaravelTemporal\Tests\Fixtures\Converter;

use Keepsuit\LaravelTemporal\Integrations\LaravelData\TemporalSerializableData;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;

class AdvancedDataItemV3 extends Data
{
    public function __construct(
        #[WithCast(TemporalSerializableData::class)]
        #[WithTransformer(TemporalSerializableData::class)]
        public TemporalSerializableItem $item,
        /**
         * @var TemporalSerializableItem[]|null
         */
        #[WithCast(TemporalSerializableData::class, type: TemporalSerializableItem::class)]
        #[WithTransformer(TemporalSerializableData::class)]
        public ?array $collection = null
    ) {
    }
}
