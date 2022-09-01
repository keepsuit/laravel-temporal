<?php

namespace Keepsuit\LaravelTemporal\Testing;

trait WithTemporal
{
    protected static ?TemporalTestingEnvironment $temporalEnvironment = null;

    protected function setUpWithTemporal()
    {
        if (static::$temporalEnvironment !== null) {
            return;
        }

        static::$temporalEnvironment = TemporalTestingEnvironment::create();

        static::$temporalEnvironment->start();

        register_shutdown_function(fn () => static::$temporalEnvironment->stop());
    }
}
