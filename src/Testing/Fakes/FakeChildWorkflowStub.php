<?php

namespace Keepsuit\LaravelTemporal\Testing\Fakes;

use Illuminate\Support\Str;
use Keepsuit\LaravelTemporal\Testing\TemporalMocker;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use Temporal\DataConverter\EncodedValues;
use Temporal\Workflow\ChildWorkflowOptions;
use Temporal\Workflow\ChildWorkflowStubInterface;
use Temporal\Workflow\WorkflowExecution;

class FakeChildWorkflowStub implements ChildWorkflowStubInterface
{
    protected bool $hasMock = false;

    protected mixed $result = null;

    public function __construct(protected ChildWorkflowStubInterface $stub)
    {
    }

    public function getExecution(): PromiseInterface
    {
        return $this->stub->getExecution();
    }

    public function getChildWorkflowType(): string
    {
        return $this->stub->getChildWorkflowType();
    }

    public function getOptions(): ChildWorkflowOptions
    {
        return $this->stub->getOptions();
    }

    public function execute(array $args = [], $returnType = null): PromiseInterface
    {
        //@phpstan-ignore-next-line
        return $this->start(...$args)->then(fn () => $this->getResult($returnType));
    }

    public function start(...$args): PromiseInterface
    {
        $mock = $this->getTemporalMocker()->getWorkflowResult($this->stub->getChildWorkflowType(), $this->stub->getOptions()->taskQueue);

        $this->hasMock = $mock instanceof \Closure;

        if (! $this->hasMock) {
            return $this->stub->start(...$args);
        }

        $this->getTemporalMocker()->recordWorkflowDispatch($this->stub->getChildWorkflowType(), $this->stub->getOptions()->taskQueue, $args);

        $this->result = $mock->__invoke(...$args);

        $started = new Promise(function (callable $resolve): void {
            $resolve(new WorkflowExecution(Str::uuid(), Str::uuid()));
        });

        //@phpstan-ignore-next-line
        return EncodedValues::decodePromise($started);
    }

    //@phpstan-ignore-next-line
    public function getResult($returnType = null): PromiseInterface
    {
        if (! $this->hasMock) {
            return $this->stub->getResult($returnType);
        }

        return new Promise(function (callable $resolve): void {
            $resolve($this->result);
        });
    }

    public function signal(string $name, array $args = []): PromiseInterface
    {
        return $this->stub->signal($name, $args);
    }

    protected function getTemporalMocker(): TemporalMocker
    {
        return app(TemporalMocker::class);
    }
}
