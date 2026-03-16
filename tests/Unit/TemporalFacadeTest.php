<?php

use Keepsuit\LaravelTemporal\Builder\ActivityBuilder;
use Keepsuit\LaravelTemporal\Builder\ChildWorkflowBuilder;
use Keepsuit\LaravelTemporal\Builder\LocalActivityBuilder;
use Keepsuit\LaravelTemporal\Builder\WorkflowBuilder;
use Keepsuit\LaravelTemporal\Temporal;
use Keepsuit\LaravelTemporal\Testing\Fakes\TemporalFake;

it('can resolve the temporal facade', function () {
    expect(Keepsuit\LaravelTemporal\Facade\Temporal::getFacadeRoot())
        ->toBeInstanceOf(Temporal::class);
});

it('can fake the temporal facade', function () {
    Keepsuit\LaravelTemporal\Facade\Temporal::fake();

    expect(Keepsuit\LaravelTemporal\Facade\Temporal::getFacadeRoot())
        ->toBeInstanceOf(TemporalFake::class);
});

it('can resolve temporal instance from interface', function () {
    expect(app(Keepsuit\LaravelTemporal\Contracts\Temporal::class))
        ->toBeInstanceOf(Temporal::class);
});

it('can provide builders', function () {
    expect(Keepsuit\LaravelTemporal\Facade\Temporal::newWorkflow())
        ->toBeInstanceOf(WorkflowBuilder::class);

    expect(Keepsuit\LaravelTemporal\Facade\Temporal::newChildWorkflow())
        ->toBeInstanceOf(ChildWorkflowBuilder::class);

    expect(Keepsuit\LaravelTemporal\Facade\Temporal::newActivity())
        ->toBeInstanceOf(ActivityBuilder::class);

    expect(Keepsuit\LaravelTemporal\Facade\Temporal::newLocalActivity())
        ->toBeInstanceOf(LocalActivityBuilder::class);
});
