<?php

namespace Keepsuit\LaravelTemporal\Builder;

use DateInterval;
use InvalidArgumentException;
use Keepsuit\LaravelTemporal\Facade\Temporal;
use Temporal\Activity\ActivityOptions;
use Temporal\Common\Priority;
use Temporal\Common\RetryOptions;
use Temporal\Internal\Workflow\ActivityProxy;
use Temporal\Workflow\ActivityStubInterface;

/**
 * @property string|null $taskQueue
 * @property DateInterval $scheduleToCloseTimeout
 * @property DateInterval $scheduleToStartTimeout
 * @property DateInterval $startToCloseTimeout
 * @property DateInterval $heartbeatTimeout
 * @property int $cancellationType
 * @property string $activityId
 * @property ?RetryOptions $retryOptions
 * @property Priority $priority
 * @property string $summary
 *
 * @method self withTaskQueue(?string $taskQueue)
 * @method self withScheduleToCloseTimeout(DateInterval $timeout)
 * @method self withScheduleToStartTimeout(DateInterval $timeout)
 * @method self withStartToCloseTimeout(DateInterval $timeout)
 * @method self withHeartbeatTimeout(DateInterval $timeout)
 * @method self withCancellationType(int $type)
 * @method self withActivityId(string $activityId)
 * @method self withRetryOptions(?RetryOptions $options)
 * @method self withPriority(Priority $priority)
 * @method self withSummary(string $summary)
 */
class ActivityBuilder
{
    use DefaultRetryPolicy;

    protected ActivityOptions $activityOptions;

    public function __construct()
    {
        $this->activityOptions = ActivityOptions::new()
            ->withTaskQueue(config('temporal.queue'))
            ->withRetryOptions($this->getDefaultRetryOptions(config('temporal.retry.activity')));
    }

    public static function new(): ActivityBuilder
    {
        return new ActivityBuilder;
    }

    public static function newLocal(): LocalActivityBuilder
    {
        return LocalActivityBuilder::new();
    }

    /**
     * @template T of object
     *
     * @param  class-string<T>  $class
     * @return ActivityProxy<T>
     */
    public function build(string $class): ActivityProxy
    {
        return Temporal::getTemporalContext()->newActivityStub($class, $this->activityOptions);
    }

    public function buildUntyped(): ActivityStubInterface
    {
        return Temporal::getTemporalContext()->newUntypedActivityStub($this->activityOptions);
    }

    public function __call(string $name, array $arguments): self
    {
        if (method_exists($this->activityOptions, $name)) {
            $self = clone $this;

            $self->activityOptions = $self->activityOptions->{$name}(...$arguments);

            return $self;
        }

        throw new InvalidArgumentException(sprintf('Method %s does not exists', $name));
    }

    public function __get(string $name): mixed
    {
        if (property_exists($this->activityOptions, $name)) {
            return $this->activityOptions->{$name};
        }

        throw new InvalidArgumentException(sprintf('Property %s does not exists', $name));
    }
}
