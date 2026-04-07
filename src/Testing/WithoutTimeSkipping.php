<?php

declare(strict_types=1);

namespace Keepsuit\LaravelTemporal\Testing;

trait WithoutTimeSkipping
{
    protected function setUpWithoutTimeSkipping(): void
    {
        if (! TemporalTestTime::timeSkippingIsEnabled()) {
            return;
        }

        TemporalTestTime::lock();
    }

    protected function tearDownWithoutTimeSkipping(): void
    {
        if (! TemporalTestTime::timeSkippingIsEnabled()) {
            return;
        }

        TemporalTestTime::unlock();
    }
}
