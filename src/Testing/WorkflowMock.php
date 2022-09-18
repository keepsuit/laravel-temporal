<?php

namespace Keepsuit\LaravelTemporal\Testing;

use Keepsuit\LaravelTemporal\Facade\Temporal;

class WorkflowMock
{
    public function __construct(
        public readonly string $workflowName,
        public readonly ?string $taskQueue
    ) {
    }

    public function assertDispatched(\Closure|int|null $callback = null): void
    {
        if (is_int($callback)) {
            $this->assertDispatchedTimes($callback);

            return;
        }

        Temporal::assertWorkflowDispatched($this->workflowName, function (mixed $result, string $taskQueue) use ($callback) {
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
        Temporal::assertWorkflowDispatchedTimes($this->workflowName, $times, function (mixed $result, string $taskQueue) use ($callback) {
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
        Temporal::assertWorkflowNotDispatched($this->workflowName, function (mixed $result, string $taskQueue) use ($callback) {
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
