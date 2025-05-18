<?php

namespace Keepsuit\LaravelTemporal\Tests\Fixtures\Converter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BaseModel extends Model
{
    protected $guarded = [];

    public function related(): HasMany
    {
        return $this->hasMany(BaseRelatedModel::class);
    }
}
