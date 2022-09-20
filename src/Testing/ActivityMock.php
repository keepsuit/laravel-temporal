<?php

namespace Keepsuit\LaravelTemporal\Testing;

use Illuminate\Support\Arr;
use Keepsuit\LaravelTemporal\Facade\Temporal;

class ActivityMock
{
    public function __construct(
        public readonly string $activityName,
        public readonly ?string $taskQueue
    ) {
    }

    public function assertDispatched(\Closure|int|null $callback = null): void
    {
        if (is_int($callback)) {
            $this->assertDispatchedTimes($callback);

            return;
        }

        Temporal::assertActivityDispatched($this->activityName, function (...$args) use ($callback) {
            $taskQueue = Arr::last($args);

            if ($this->taskQueue !== null && $this->taskQueue !== $taskQueue) {
                return false;
            }

            if ($callback !== null) {
                return $callback(...$args);
            }

            return true;
        });
    }

    public function assertDispatchedTimes(int $times = 1, \Closure|int|null $callback = null): void
    {
        Temporal::assertActivityDispatchedTimes($this->activityName, $times, function (...$args) use ($callback) {
            $taskQueue = Arr::last($args);

            if ($this->taskQueue !== null && $this->taskQueue !== $taskQueue) {
                return false;
            }

            if ($callback !== null) {
                return $callback(...$args);
            }

            return true;
        });
    }

    public function assertNotDispatched(\Closure|null $callback = null): void
    {
        Temporal::assertActivityNotDispatched($this->activityName, function (...$args) use ($callback) {
            $taskQueue = Arr::last($args);

            if ($this->taskQueue !== null && $this->taskQueue !== $taskQueue) {
                return false;
            }

            if ($callback !== null) {
                return $callback(...$args);
            }

            return true;
        });
    }
}
