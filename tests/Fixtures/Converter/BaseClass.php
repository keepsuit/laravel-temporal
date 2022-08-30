<?php

namespace Keepsuit\LaravelTemporal\Tests\Fixtures\Converter;

class BaseClass implements \JsonSerializable
{
    protected mixed $id;

    public function __construct($payload)
    {
        $this->id = $payload['id'];
    }

    public function jsonSerialize(): mixed
    {
        return ['id' => $this->id];
    }
}
