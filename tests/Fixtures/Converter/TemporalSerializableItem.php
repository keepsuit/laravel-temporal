<?php

namespace Keepsuit\LaravelTemporal\Tests\Fixtures\Converter;

use Keepsuit\LaravelTemporal\Contracts\TemporalSerializable;

class TemporalSerializableItem implements TemporalSerializable
{
    public function __construct(public int $id) {}

    public function toTemporalPayload(): array
    {
        return [
            'type' => TemporalSerializable::class,
            'id' => $this->id,
        ];
    }

    public static function fromTemporalPayload(array $payload): static
    {
        return new static($payload['id']);
    }
}
