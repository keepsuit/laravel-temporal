<?php

namespace Keepsuit\LaravelTemporal\Builder;

use InvalidArgumentException;
use Temporal\Client\WorkflowClientInterface;
use Temporal\Client\WorkflowOptions;
use Temporal\Client\WorkflowStubInterface;
use Temporal\Internal\Client\WorkflowProxy;

/**
 * @mixin WorkflowOptions
 *
 * @property string|null $runId
 */
class WorkflowBuilder
{
    use DefaultRetryPolicy;

    protected WorkflowOptions $workflowOptions;

    protected ?string $runId = null;

    public function __construct()
    {
        $this->workflowOptions = WorkflowOptions::new()
            ->withTaskQueue(config('temporal.queue'))
            ->withRetryOptions($this->getDefaultRetryOptions(config('temporal.retry.workflow')));
    }

    public static function new(): WorkflowBuilder
    {
        return new WorkflowBuilder();
    }

    public static function newChild(): ChildWorkflowBuilder
    {
        return new ChildWorkflowBuilder();
    }

    public function withRunId(?string $runId): self
    {
        $self = clone $this;

        $self->runId = $runId;

        return $self;
    }

    /**
     * @template T of object
     *
     * @param  class-string<T>  $class
     * @return WorkflowProxy<T>
     */
    public function build(string $class): WorkflowProxy
    {
        if ($this->runId !== null) {
            // @phpstan-ignore-next-line
            return $this->getWorkflowClient()
                ->newRunningWorkflowStub($class, $this->workflowOptions->workflowId, $this->runId);
        }

        // @phpstan-ignore-next-line
        return $this->getWorkflowClient()
            ->newWorkflowStub($class, $this->workflowOptions);
    }

    public function buildUntyped(string $workflowType): WorkflowStubInterface
    {
        if ($this->runId !== null) {
            return $this->getWorkflowClient()
                ->newUntypedRunningWorkflowStub($this->workflowOptions->workflowId, $this->runId, $workflowType);
        }

        return $this->getWorkflowClient()
            ->newUntypedWorkflowStub($workflowType, $this->workflowOptions);
    }

    public function __call(string $name, array $arguments): self
    {
        if (method_exists($this->workflowOptions, $name)) {
            $self = clone $this;

            $self->workflowOptions = $self->workflowOptions->{$name}(...$arguments);

            return $self;
        }

        throw new InvalidArgumentException(sprintf('Method %s does not exists', $name));
    }

    public function __get(string $name): mixed
    {
        if ($name === 'runId') {
            return $this->runId;
        }

        if (property_exists($this->workflowOptions, $name)) {
            return $this->workflowOptions->{$name};
        }

        throw new InvalidArgumentException(sprintf('Property %s does not exists', $name));
    }

    protected function getWorkflowClient(): WorkflowClientInterface
    {
        return app(WorkflowClientInterface::class);
    }
}
