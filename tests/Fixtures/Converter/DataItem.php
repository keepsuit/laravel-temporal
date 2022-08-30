<?php

namespace Keepsuit\LaravelTemporal\Tests\Fixtures\Converter;

use Spatie\LaravelData\Data;

class DataItem extends Data
{
    public function __construct(
        public int $id
    ) {
    }
}
