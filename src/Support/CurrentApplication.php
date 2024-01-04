<?php

namespace Keepsuit\LaravelTemporal\Support;

use Illuminate\Container\Container;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use RuntimeException;

class CurrentApplication
{
    protected static ?Application $root = null;

    public static function setRootApp(?Application $application): void
    {
        static::$root = $application;
    }

    public static function createSandbox(): Application
    {
        self::ensureRootAppIsSet();

        $sandbox = clone static::$root;

        static::set($sandbox);

        return $sandbox;
    }

    public static function reset(): void
    {
        self::ensureRootAppIsSet();

        static::set(static::$root);
    }

    protected static function set(Application $app): void
    {
        $app->instance('app', $app);
        $app->instance(Container::class, $app);

        Container::setInstance($app);

        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($app);
    }

    protected static function ensureRootAppIsSet(): void
    {
        if (static::$root === null) {
            throw new RuntimeException('Root application not set');
        }
    }
}
