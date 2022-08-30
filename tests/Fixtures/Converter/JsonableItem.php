<?php

namespace Keepsuit\LaravelTemporal\Tests\Fixtures\Converter;

use Illuminate\Contracts\Support\Jsonable;

class JsonableItem implements Jsonable
{
    public function __construct(public int $id)
    {
    }

    public function toJson($options = 0)
    {
        return json_encode([
            'type' => Jsonable::class,
            'id' => $this->id,
        ], $options);
    }
}
