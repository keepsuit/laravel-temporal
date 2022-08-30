<?php

namespace Keepsuit\LaravelTemporal\Tests\Fixtures\Converter;

use Illuminate\Contracts\Support\Arrayable;

class ArrayableItem implements Arrayable
{
    public function __construct(public int $id)
    {
    }

    public function toArray()
    {
        return [
            'type' => Arrayable::class,
            'id' => $this->id,
        ];
    }
}
