<?php

namespace Keepsuit\LaravelTemporal\Testing\Fakes;

use Keepsuit\LaravelTemporal\Testing\TemporalMocker;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use Temporal\Activity\ActivityOptions;
use Temporal\Activity\ActivityOptionsInterface;
use Temporal\Activity\LocalActivityOptions;
use Temporal\DataConverter\EncodedValues;
use Temporal\DataConverter\Type;
use Temporal\Workflow\ActivityStubInterface;

class FakeActivityStub implements ActivityStubInterface
{
    private mixed $result = null;

    public function __construct(protected ActivityStubInterface $stub) {}

    public function getOptions(): ActivityOptionsInterface
    {
        return $this->stub->getOptions();
    }

    public function execute(string $name, array $args = [], Type|string|\ReflectionClass|\ReflectionType|null $returnType = null, bool $isLocalActivity = false): PromiseInterface
    {
        /** @var ActivityOptions|LocalActivityOptions $options */
        $options = $this->getOptions();

        $taskQueue = $options instanceof ActivityOptions ? $options->taskQueue : null;

        $mock = $this->getTemporalMocker()->getActivityResult($name, $taskQueue);

        if (! $mock instanceof \Closure) {
            return $this->stub->execute($name, $args, $returnType, $isLocalActivity);
        }

        $this->getTemporalMocker()->recordActivityDispatch($name, $taskQueue, $args);

        $this->result = $mock->__invoke(...$args);

        $request = new Promise(function (callable $resolve): void {
            $resolve($this->result);
        });

        return EncodedValues::decodePromise($request);
    }

    protected function getTemporalMocker(): TemporalMocker
    {
        return app(TemporalMocker::class);
    }
}
