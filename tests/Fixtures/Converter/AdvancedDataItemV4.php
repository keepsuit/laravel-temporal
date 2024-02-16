<?php

namespace Keepsuit\LaravelTemporal\Tests\Fixtures\Converter;

use Illuminate\Support\Collection;
use Keepsuit\LaravelTemporal\Integrations\LaravelData\TemporalSerializableCastAndTransformer;
use Spatie\LaravelData\Attributes\WithCastAndTransformer;
use Spatie\LaravelData\Data;

class AdvancedDataItemV4 extends Data
{
    public function __construct(
        #[WithCastAndTransformer(TemporalSerializableCastAndTransformer::class)]
        public TemporalSerializableItem $item,
        /**
         * @var Collection<array-key,TemporalSerializableItem>|null
         */
        #[WithCastAndTransformer(TemporalSerializableCastAndTransformer::class, type: TemporalSerializableItem::class)]
        public ?Collection $collection = null,
    ) {
    }
}
