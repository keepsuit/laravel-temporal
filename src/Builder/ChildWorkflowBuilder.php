<?php

namespace Keepsuit\LaravelTemporal\Builder;

use DateInterval;
use InvalidArgumentException;
use Keepsuit\LaravelTemporal\Facade\Temporal;
use Temporal\Common\RetryOptions;
use Temporal\Internal\Workflow\ChildWorkflowProxy;
use Temporal\Workflow\ChildWorkflowOptions;
use Temporal\Workflow\ChildWorkflowStubInterface;

/**
 * @property string $namespace
 * @property ?string $workflowId
 * @property string $taskQueue
 * @property DateInterval $workflowExecutionTimeout
 * @property DateInterval $workflowRunTimeout
 * @property DateInterval $workflowTaskTimeout
 * @property int $childWorkflowCancellationType
 * @property int $workflowIdReusePolicy
 * @property ?RetryOptions $retryOptions
 * @property ?string $cronSchedule
 * @property int $parentClosePolicy
 * @property ?array $memo
 * @property ?array $searchAttributes
 *
 * @method ChildWorkflowBuilder withNamespace(string $namespace)
 * @method ChildWorkflowBuilder withWorkflowId(string $workflowId)
 * @method ChildWorkflowBuilder withTaskQueue(string $taskQueue)
 * @method ChildWorkflowBuilder withWorkflowExecutionTimeout(DateInterval $timeout)
 * @method ChildWorkflowBuilder withWorkflowRunTimeout(DateInterval $timeout)
 * @method ChildWorkflowBuilder withWorkflowTaskTimeout(DateInterval $timeout)
 * @method ChildWorkflowBuilder withChildWorkflowCancellationType(int $type)
 * @method ChildWorkflowBuilder withWorkflowIdReusePolicy(int $policy)
 * @method ChildWorkflowBuilder withRetryOptions(?RetryOptions $options)
 * @method ChildWorkflowBuilder withCronSchedule(?string $expression)
 * @method ChildWorkflowBuilder withMemo(?array $memo)
 * @method ChildWorkflowBuilder withSearchAttributes(?array $searchAttributes)
 * @method ChildWorkflowBuilder withParentClosePolicy(int $policy)
 */
class ChildWorkflowBuilder
{
    use DefaultRetryPolicy;

    protected ChildWorkflowOptions $workflowOptions;

    public function __construct()
    {
        $this->workflowOptions = ChildWorkflowOptions::new()
            ->withNamespace(config('temporal.namespace'))
            ->withTaskQueue(config('temporal.queue'))
            ->withRetryOptions($this->getDefaultRetryOptions(config('temporal.retry.workflow')));
    }

    public static function new(): ChildWorkflowBuilder
    {
        return new ChildWorkflowBuilder();
    }

    /**
     * @template T of object
     *
     * @param  class-string<T>  $class
     * @return ChildWorkflowProxy<T>
     */
    public function build(string $class): ChildWorkflowProxy
    {
        return Temporal::getTemporalContext()->newChildWorkflowStub($class, $this->workflowOptions);
    }

    public function buildUntyped(string $workflowType): ChildWorkflowStubInterface
    {
        return Temporal::getTemporalContext()->newUntypedChildWorkflowStub($workflowType, $this->workflowOptions);
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
}
