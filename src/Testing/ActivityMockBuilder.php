<?php

namespace Keepsuit\LaravelTemporal\Testing;

use Keepsuit\LaravelTemporal\Facade\Temporal;

class ActivityMockBuilder
{
    protected ?string $taskQueue = null;

    public function __construct(protected string $activityName) {}

    public function onTaskQueue(string $taskQueue): self
    {
        $this->taskQueue = $taskQueue;

        return $this;
    }

    public function andReturn(mixed $returnValue): ActivityMock
    {
        Temporal::mockActivities([
            $this->activityName => $returnValue,
        ], $this->taskQueue);

        return new ActivityMock($this->activityName, $this->taskQueue);
    }
}
