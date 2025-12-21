<?php

namespace Keepsuit\LaravelTemporal\Testing;

use Carbon\Carbon;
use Illuminate\Container\Container;
use Temporal\Testing\TestService;

final class TemporalTestTime
{
    protected TestService $service;

    public function __construct(string $temporalServerAddress)
    {
        $this->service = TestService::create($temporalServerAddress);
    }

    protected static function instance(): TemporalTestTime
    {
        return Container::getInstance()->make(TemporalTestTime::class);
    }

    public static function timeSkippingIsEnabled(): bool
    {
        return config()->boolean('temporal.testing.time_skipping', false);
    }

    public static function testService(): TestService
    {
        return self::instance()->service;
    }

    public static function lock(): void
    {
        TemporalTestTime::instance()->service->lockTimeSkipping();
    }

    public static function unlock(): void
    {
        TemporalTestTime::instance()->service->unlockTimeSkipping();
    }

    public static function sleep(int $seconds): void
    {
        TemporalTestTime::instance()->service->unlockTimeSkippingWithSleep($seconds);
    }

    public static function now(): Carbon
    {
        return TemporalTestTime::instance()->service->getCurrentTime();
    }
}
