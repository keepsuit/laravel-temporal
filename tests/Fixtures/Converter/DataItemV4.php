<?php

namespace Keepsuit\LaravelTemporal\Tests\Fixtures\Converter;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class DataItemV4 extends Data
{
    public function __construct(
        public int $id,
        public ?array $values = null,
        /**
         * @var Collection<array-key,DataItemV4>|null
         */
        public ?Collection $collection = null,
    ) {
    }
}
