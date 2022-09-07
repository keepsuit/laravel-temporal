<?php

namespace Keepsuit\LaravelTemporal\Testing\Fakes;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Temporal\Client\WorkflowClient;
use Temporal\Internal\Client\WorkflowProxy;
use Temporal\Workflow\WorkflowExecution;
use Temporal\Workflow\WorkflowRunInterface;
use Temporal\Workflow\WorkflowStub as WorkflowStubConverter;

class FakeWorkflowClient extends WorkflowClient
{
    protected array $workflowMocks = [];

    protected ?\Closure $onWorkflowStartCallback = null;

    public function mockWorkflow(string $name, mixed $result): static
    {
        $this->workflowMocks[$name] = $result instanceof \Closure ? $result : fn () => $result;

        return $this;
    }

    /**
     * @param  WorkflowProxy  $workflow
     */
    public function start($workflow, ...$args): WorkflowRunInterface
    {
        $workflowStub = WorkflowStubConverter::fromWorkflow($workflow);

        if (! Arr::has($this->workflowMocks, $workflowStub->getWorkflowType())) {
            return parent::start($workflow, ...$args);
        }

        $this->onWorkflowStartCallback?->__invoke($workflowStub->getWorkflowType(), ...$args);

        $execution = new WorkflowExecution(
            Str::uuid(),
            Str::uuid()
        );

        $workflowStub->setExecution($execution);

        $result = $this->workflowMocks[$workflowStub->getWorkflowType()](...$args);

        return new FakeWorkflowRun($workflowStub, $result);
    }

    /**
     * @internal
     */
    public function onWorkflowStart(\Closure $onWorkflowStartCallback): void
    {
        $this->onWorkflowStartCallback = $onWorkflowStartCallback;
    }
}
