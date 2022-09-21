<?php

namespace Keepsuit\LaravelTemporal\Builder;

use DateInterval;
use InvalidArgumentException;
use Keepsuit\LaravelTemporal\Facade\Temporal;
use Temporal\Activity\ActivityOptions;
use Temporal\Common\RetryOptions;
use Temporal\Internal\Workflow\ActivityProxy;
use Temporal\Workflow\ActivityStubInterface;

/**
 * @method self withTaskQueue(?string $taskQueue)
 * @method self withScheduleToCloseTimeout(DateInterval $timeout)
 * @method self withScheduleToStartTimeout(DateInterval $timeout)
 * @method self withStartToCloseTimeout(DateInterval $timeout)
 * @method self withHeartbeatTimeout(DateInterval $timeout)
 * @method self withCancellationType(int $type)
 * @method self withActivityId(string $activityId)
 * @method self withRetryOptions(?RetryOptions $options)
 */
final class ActivityBuilder
{
    use DefaultRetryPolicy;

    private ActivityOptions $activityOptions;

    public function __construct()
    {
        $this->activityOptions = ActivityOptions::new()
            ->withTaskQueue(config('temporal.queue'))
            ->withRetryOptions($this->getDefaultRetryOptions(config('temporal.retry.activity')));
    }

    public static function new(): ActivityBuilder
    {
        return new ActivityBuilder();
    }

    public static function newLocal(): LocalActivityBuilder
    {
        return LocalActivityBuilder::new();
    }

    /**
     * @template T of object
     *
     * @param  class-string<T>  $class
     * @return ActivityProxy|T
     */
    public function build(string $class)
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
