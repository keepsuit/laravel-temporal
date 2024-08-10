<?php

namespace Keepsuit\LaravelTemporal\Testing;

use Keepsuit\LaravelTemporal\Facade\Temporal;

class WorkflowMockBuilder
{
    protected ?string $taskQueue = null;

    public function __construct(protected string $workflowName) {}

    public function onTaskQueue(string $taskQueue): self
    {
        $this->taskQueue = $taskQueue;

        return $this;
    }

    public function andReturn(mixed $returnValue): WorkflowMock
    {
        Temporal::mockWorkflows([
            $this->workflowName => $returnValue,
        ], $this->taskQueue);

        return new WorkflowMock($this->workflowName, $this->taskQueue);
    }
}
