<?php

namespace Keepsuit\LaravelTemporal\Tests\Fixtures\Converter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Keepsuit\LaravelTemporal\Contracts\TemporalSerializable;
use Keepsuit\LaravelTemporal\Integrations\Eloquent\TemporalEloquentSerialize;

class SampleRelatedModel extends Model implements TemporalSerializable
{
    use TemporalEloquentSerialize;

    protected $guarded = [];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(SampleModel::class);
    }
}
