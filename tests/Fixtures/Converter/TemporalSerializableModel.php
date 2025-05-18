<?php

namespace Keepsuit\LaravelTemporal\Tests\Fixtures\Converter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Keepsuit\LaravelTemporal\Contracts\TemporalSerializable;
use Keepsuit\LaravelTemporal\Integrations\Eloquent\TemporalEloquentSerialize;

class TemporalSerializableModel extends Model implements TemporalSerializable
{
    use TemporalEloquentSerialize;

    protected $guarded = [];

    public function related(): HasMany
    {
        return $this->hasMany(TemporalSerializableRelatedModel::class);
    }
}
