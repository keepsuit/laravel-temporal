<?php

namespace Keepsuit\LaravelTemporal\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Keepsuit\LaravelTemporal\Temporal
 */
class Temporal extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Keepsuit\LaravelTemporal\Temporal::class;
    }
}
