<?php

namespace Keepsuit\LaravelTemporal\Tests\Fixtures\Converter;

use Keepsuit\LaravelTemporal\Integrations\LaravelData\TemporalSerializableCastAndTransformer;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;

class AdvancedDataItemV3 extends Data
{
    public function __construct(
        #[WithCast(TemporalSerializableCastAndTransformer::class)]
        #[WithTransformer(TemporalSerializableCastAndTransformer::class)]
        public TemporalSerializableItem $item,
        /**
         * @var TemporalSerializableItem[]|null
         */
        #[WithCast(TemporalSerializableCastAndTransformer::class, type: TemporalSerializableItem::class)]
        #[WithTransformer(TemporalSerializableCastAndTransformer::class)]
        public ?array $collection = null
    ) {
    }
}
