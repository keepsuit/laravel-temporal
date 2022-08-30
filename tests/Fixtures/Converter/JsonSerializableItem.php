<?php

namespace Keepsuit\LaravelTemporal\Tests\Fixtures\Converter;

class JsonSerializableItem implements \JsonSerializable
{
    public function __construct(public int $id)
    {
    }

    public function jsonSerialize(): mixed
    {
        return [
            'type' => \JsonSerializable::class,
            'id' => $this->id,
        ];
    }
}
