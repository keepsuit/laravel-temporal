<?php

namespace Keepsuit\LaravelTemporal\Tests\Fixtures\Converter;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class DataItemV3 extends Data
{
    public function __construct(
        public int $id,
        public ?array $values = null,
        /**
         * @var DataCollection<array-key,DataItemV3>|null
         */
        public ?DataCollection $collection = null,
    ) {
    }
}
