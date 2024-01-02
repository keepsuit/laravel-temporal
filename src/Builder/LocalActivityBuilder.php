<?php

namespace Keepsuit\LaravelTemporal\Builder;

use InvalidArgumentException;
use Keepsuit\LaravelTemporal\Facade\Temporal;
use Temporal\Activity\LocalActivityOptions;
use Temporal\Internal\Workflow\ActivityProxy;
use Temporal\Workflow\ActivityStubInterface;

/**
 * @mixin LocalActivityOptions
 */
class LocalActivityBuilder
{
    use DefaultRetryPolicy;

    protected LocalActivityOptions $activityOptions;

    public function __construct()
    {
        $this->activityOptions = LocalActivityOptions::new()
            ->withRetryOptions($this->getDefaultRetryOptions(config('temporal.retry.activity')));
    }

    public static function new(): LocalActivityBuilder
    {
        return new LocalActivityBuilder();
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
