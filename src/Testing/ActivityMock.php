<?php

namespace Keepsuit\LaravelTemporal\Testing;

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

        Temporal::assertActivityDispatched($this->activityName, function (mixed $result, string $taskQueue) use ($callback) {
            if ($this->taskQueue !== null && $this->taskQueue !== $taskQueue) {
                return false;
            }

            if ($callback !== null) {
                return $callback($result, $taskQueue);
            }

            return true;
        });
    }

    public function assertDispatchedTimes(int $times = 1, \Closure|int|null $callback = null): void
    {
        Temporal::assertActivityDispatchedTimes($this->activityName, $times, function (mixed $result, string $taskQueue) use ($callback) {
            if ($this->taskQueue !== null && $this->taskQueue !== $taskQueue) {
                return false;
            }

            if ($callback !== null) {
                return $callback($result, $taskQueue);
            }

            return true;
        });
    }

    public function assertNotDispatched(\Closure|null $callback = null): void
    {
        Temporal::assertActivityDispatched($this->activityName, function (mixed $result, string $taskQueue) use ($callback) {
            if ($this->taskQueue !== null && $this->taskQueue !== $taskQueue) {
                return false;
            }

            if ($callback !== null) {
                return $callback($result, $taskQueue);
            }

            return true;
        });
    }
}
