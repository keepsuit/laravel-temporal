<?php

namespace Keepsuit\LaravelTemporal\Contracts;

interface TemporalSerializable
{
    public function toTemporalPayload(): array;

    public static function fromTemporalPayload(array $payload);
}
