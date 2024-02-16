<?php

namespace Keepsuit\LaravelTemporal\Integrations\LaravelData;

use Composer\InstalledVersions;
use Illuminate\Support\Str;

class LaravelDataHelpers
{
    protected static int $version;

    public static function version(): int
    {
        if (isset(static::$version)) {
            return static::$version;
        }

        return static::$version = (int) Str::of(InstalledVersions::getPrettyVersion('spatie/laravel-data'))
            ->explode('.')
            ->first();
    }
}
