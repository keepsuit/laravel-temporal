<?php

namespace Keepsuit\LaravelTemporal\Builder;

use DateInterval;
use InvalidArgumentException;
use Temporal\Client\WorkflowClientInterface;
use Temporal\Client\WorkflowOptions;
use Temporal\Client\WorkflowStubInterface;
use Temporal\Common\RetryOptions;
use Temporal\Internal\Client\WorkflowProxy;

/**
 * @method self withWorkflowId(string $workflowId)
 * @method self withTaskQueue(string $taskQueue)
 * @method self withWorkflowExecutionTimeout(DateInterval $timeout)
 * @method self withWorkflowRunTimeout(DateInterval $timeout)
 * @method self withWorkflowTaskTimeout(DateInterval $timeout)
 * @method self withWorkflowIdReusePolicy(int $policy)
 * @method self withRetryOptions(?RetryOptions $options)
 * @method self withCronSchedule(?string $expression)
 * @method self withMemo(?array $memo)
 * @method self withSearchAttributes(?array $searchAttributes)
 */
final class WorkflowBuilder
{
    private WorkflowOptions $workflowOptions;

    private ?string $runId = null;

    public function __construct()
    {
        $this->workflowOptions = WorkflowOptions::new()->withTaskQueue(config('temporal.queue'));
    }

    public static function new(): WorkflowBuilder
    {
        return new WorkflowBuilder();
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
     * @return WorkflowProxy<T>|T
     */
    public function build(string $class)
    {
        if ($this->runId !== null) {
            return $this->getWorkflowClient()
                ->newRunningWorkflowStub($class, $this->workflowOptions->workflowId, $this->runId);
        }

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
        if (property_exists($this->workflowOptions, $name)) {
            return $this->workflowOptions->{$name};
        }

        throw new InvalidArgumentException(sprintf('Property %s does not exists', $name));
    }

    private function getWorkflowClient(): WorkflowClientInterface
    {
        return app(WorkflowClientInterface::class);
    }
}
