<?php

namespace Keepsuit\LaravelTemporal\Testing;

trait WithTemporalWorker
{
    protected function setUpWithTemporalWorker(): void
    {
        /** @var TemporalTestingEnvironment|null $temporalEnvironment */
        $temporalEnvironment = $GLOBALS['_temporal_environment'] ?? null;

        if ($temporalEnvironment !== null) {
            return;
        }

        $temporalEnvironment = TemporalTestingEnvironment::create();

        $temporalEnvironment->setDebugOutput(config('temporal.testing.debug', false));

        $temporalEnvironment->start(onlyWorker: true);

        $GLOBALS['_temporal_environment'] = $temporalEnvironment;

        register_shutdown_function(function () use ($temporalEnvironment): void {
            $temporalEnvironment->stop();
            $GLOBALS['_temporal_environment'] = null;
        });
    }
}
