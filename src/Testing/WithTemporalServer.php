<?php

namespace Keepsuit\LaravelTemporal\Testing;

trait WithTemporalServer
{
    protected static ?TemporalTestingEnvironment $temporalEnvironment = null;

    protected function setUpWithTemporalServer()
    {
        if (static::$temporalEnvironment !== null) {
            return;
        }

        static::$temporalEnvironment = TemporalTestingEnvironment::create();

        static::$temporalEnvironment->start(onlyWorker: ! env('TEMPORAL_TESTING_SERVER', true));

        register_shutdown_function(fn () => static::$temporalEnvironment->stop());
    }
}
