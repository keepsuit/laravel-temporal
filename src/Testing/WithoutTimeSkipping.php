<?php

namespace Keepsuit\LaravelTemporal\Testing;

trait WithoutTimeSkipping
{
    protected function setUpWithoutTimeSkipping(): void
    {
        if (! config('temporal.testing.time_skipping')) {
            return;
        }

        TemporalTestTime::lock();
    }

    protected function tearDownWithoutTimeSkipping(): void
    {
        if (! config('temporal.testing.time_skipping')) {
            return;
        }

        TemporalTestTime::unlock();
    }
}
