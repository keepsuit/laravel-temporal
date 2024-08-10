<?php

namespace Keepsuit\LaravelTemporal\Tests\Fixtures\Converter;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class AdvancedDataItem extends Data
{
    public function __construct(
        public TemporalSerializableItem $item,
        /**
         * @var Collection<array-key,TemporalSerializableItem>|null
         */
        public ?Collection $collection = null,
    ) {}
}
