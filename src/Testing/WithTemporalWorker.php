<?php

namespace Keepsuit\LaravelTemporal\Testing;

trait WithTemporalWorker
{
    protected static ?TemporalTestingEnvironment $temporalEnvironment = null;

    protected function setUpWithTemporalWorker()
    {
        if (static::$temporalEnvironment !== null) {
            return;
        }

        static::$temporalEnvironment = TemporalTestingEnvironment::create();

        static::$temporalEnvironment->start(onlyWorker: true);

        register_shutdown_function(fn () => static::$temporalEnvironment->stop());
    }
}
