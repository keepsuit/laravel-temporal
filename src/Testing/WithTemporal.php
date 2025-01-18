<?php

namespace Keepsuit\LaravelTemporal\Testing;

trait WithTemporal
{
    protected function setUpWithTemporal(): void
    {
        /** @var TemporalTestingEnvironment|null $temporalEnvironment */
        $temporalEnvironment = $GLOBALS['_temporal_environment'] ?? null;

        if ($temporalEnvironment !== null) {
            return;
        }

        $temporalEnvironment = TemporalTestingEnvironment::create(config('temporal.testing.time_skipping', false));

        $temporalEnvironment->setDebugOutput(config('temporal.testing.debug', false));

        $temporalEnvironment->start(onlyWorker: ! config('temporal.testing.server', true));

        $GLOBALS['_temporal_environment'] = $temporalEnvironment;

        register_shutdown_function(function () use ($temporalEnvironment): void {
            $temporalEnvironment->stop();
            $GLOBALS['_temporal_environment'] = null;
        });
    }
}
