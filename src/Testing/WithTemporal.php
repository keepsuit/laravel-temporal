<?php

namespace Keepsuit\LaravelTemporal\Testing;

trait WithTemporal
{
    protected function setUpWithTemporal()
    {
        /** @var TemporalTestingEnvironment|null $temporalEnvironment */
        $temporalEnvironment = $GLOBALS['_temporal_environment'] ?? null;

        if ($temporalEnvironment !== null) {
            return;
        }

        $temporalEnvironment = TemporalTestingEnvironment::create();

        $temporalEnvironment->start(onlyWorker: ! env('TEMPORAL_TESTING_SERVER', true));

        $GLOBALS['_temporal_environment'] = $temporalEnvironment;

        register_shutdown_function(function () use ($temporalEnvironment): void {
            $temporalEnvironment->stop();
            $GLOBALS['_temporal_environment'] = null;
        });
    }
}
