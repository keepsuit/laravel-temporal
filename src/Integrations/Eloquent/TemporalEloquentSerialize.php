<?php

namespace Keepsuit\LaravelTemporal\Integrations\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Keepsuit\LaravelTemporal\Contracts\TemporalSerializable;

/**
 * @deprecated Eloquent model are now handled without this trait.
 * If you want to use default Eloquent serialization, remove this trait and TemporalSerializable interface from your models.
 *
 * @mixin Model
 */
trait TemporalEloquentSerialize
{
    public function toTemporalPayload(): array
    {
        return TemporalEloquentSerializer::toPayload($this);
    }

    public static function fromTemporalPayload(array $payload): static
    {
        return TemporalEloquentSerializer::fromPayload(static::class, $payload);
    }
}
