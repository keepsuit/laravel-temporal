<?php

namespace Keepsuit\LaravelTemporal\Builder;

use DateInterval;
use InvalidArgumentException;
use Temporal\Client\WorkflowClientInterface;
use Temporal\Client\WorkflowOptions;
use Temporal\Client\WorkflowStubInterface;
use Temporal\Common\IdReusePolicy;
use Temporal\Common\Priority;
use Temporal\Common\RetryOptions;
use Temporal\Common\TypedSearchAttributes;
use Temporal\Common\WorkflowIdConflictPolicy;
use Temporal\Internal\Client\WorkflowProxy;
use Temporal\Worker\WorkerFactoryInterface;

/**
 * @property string|null $runId
 * @property string $workflowId
 * @property string $taskQueue
 * @property bool $eagerStart
 * @property DateInterval $workflowExecutionTimeout
 * @property DateInterval $workflowRunTimeout
 * @property DateInterval $workflowStartDelay
 * @property DateInterval $workflowTaskTimeout
 * @property int $workflowIdReusePolicy
 * @property WorkflowIdConflictPolicy $workflowIdConflictPolicy
 * @property ?RetryOptions $retryOptions
 * @property ?string $cronSchedule
 * @property ?array $memo
 * @property ?array $searchAttributes
 * @property ?TypedSearchAttributes $typedSearchAttributes
 * @property string $staticDetails
 * @property string $staticSummary
 * @property Priority $priority
 *
 * @method self withWorkflowId(string $workflowId)
 * @method self withTaskQueue(string $taskQueue)
 * @method self withEagerStart(bool $value = true)
 * @method self withWorkflowExecutionTimeout(DateInterval $timeout)
 * @method self withWorkflowRunTimeout(DateInterval $timeout)
 * @method self withWorkflowTaskTimeout(DateInterval $timeout)
 * @method self withWorkflowStartDelay(DateInterval $delay)
 * @method self withWorkflowIdReusePolicy(IdReusePolicy|int $policy)
 * @method self withWorkflowIdConflictPolicy(WorkflowIdConflictPolicy $policy)
 * @method self withRetryOptions(?RetryOptions $options)
 * @method self withCronSchedule(?string $expression)
 * @method self withMemo(?array $memo)
 * @method self withSearchAttributes(?array $searchAttributes)
 * @method self withTypedSearchAttributes(TypedSearchAttributes $attributes)
 * @method self withStaticSummary(string $summary)
 * @method self withStaticDetails(string $details)
 * @method self withPriority(Priority $priority)
 */
class WorkflowBuilder
{
    use DefaultRetryPolicy;

    protected WorkflowOptions $workflowOptions;

    protected ?string $runId = null;

    public function __construct()
    {
        $this->workflowOptions = WorkflowOptions::new()
            ->withTaskQueue(config('temporal.queue') ?? WorkerFactoryInterface::DEFAULT_TASK_QUEUE)
            ->withRetryOptions($this->getDefaultRetryOptions(config('temporal.retry.workflow') ?? []));
    }

    public static function new(): WorkflowBuilder
    {
        return new WorkflowBuilder;
    }

    public static function newChild(): ChildWorkflowBuilder
    {
        return new ChildWorkflowBuilder;
    }

    /**
     * @deprecated 2.1.0 Use WorkflowBuilder::buildRunning($runId) instead
     * @see WorkflowBuilder::buildRunning()
     */
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
            return $this->buildRunning($class, $this->runId ?: null);
        }

        return $this->getWorkflowClient()
            ->newWorkflowStub($class, $this->workflowOptions);
    }

    /**
     * @param  non-empty-string  $workflowType
     */
    public function buildUntyped(string $workflowType): WorkflowStubInterface
    {
        if ($this->runId !== null) {
            return $this->buildRunningUntyped($workflowType, $this->runId ?: null);
        }

        return $this->getWorkflowClient()
            ->newUntypedWorkflowStub($workflowType, $this->workflowOptions);
    }

    /**
     * @template T of object
     *
     * @param  class-string<T>  $class
     * @param  non-empty-string|null  $runId
     * @return WorkflowProxy<T>
     */
    public function buildRunning(string $class, ?string $runId = null): WorkflowProxy
    {
        assert($this->workflowOptions->workflowId !== '', 'Workflow ID is required to build a running workflow.');

        return $this->getWorkflowClient()
            ->newRunningWorkflowStub($class, $this->workflowOptions->workflowId, $runId);
    }

    /**
     * @param  non-empty-string  $workflowType
     * @param  non-empty-string|null  $runId
     */
    public function buildRunningUntyped(string $workflowType, ?string $runId = null): WorkflowStubInterface
    {
        assert($this->workflowOptions->workflowId !== '', 'Workflow ID is required to build a running workflow.');

        return $this->getWorkflowClient()
            ->newUntypedRunningWorkflowStub($this->workflowOptions->workflowId, $runId, $workflowType);
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
