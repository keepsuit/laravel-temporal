<?php

it('can resolve the temporal facade', function () {
    expect(\Keepsuit\LaravelTemporal\Facade\Temporal::getFacadeRoot())
        ->toBeInstanceOf(\Keepsuit\LaravelTemporal\Temporal::class);
});

it('can fake the temporal facade', function () {
    \Keepsuit\LaravelTemporal\Facade\Temporal::fake();

    expect(\Keepsuit\LaravelTemporal\Facade\Temporal::getFacadeRoot())
        ->toBeInstanceOf(\Keepsuit\LaravelTemporal\Testing\Fakes\TemporalFake::class);
});

it('can resolve temporal instance from interface', function () {
    expect(app(\Keepsuit\LaravelTemporal\Contracts\Temporal::class))
        ->toBeInstanceOf(\Keepsuit\LaravelTemporal\Temporal::class);
});

it('can provide builders', function () {
    expect(\Keepsuit\LaravelTemporal\Facade\Temporal::newWorkflow())
        ->toBeInstanceOf(\Keepsuit\LaravelTemporal\Builder\WorkflowBuilder::class);

    expect(\Keepsuit\LaravelTemporal\Facade\Temporal::newChildWorkflow())
        ->toBeInstanceOf(\Keepsuit\LaravelTemporal\Builder\ChildWorkflowBuilder::class);

    expect(\Keepsuit\LaravelTemporal\Facade\Temporal::newActivity())
        ->toBeInstanceOf(\Keepsuit\LaravelTemporal\Builder\ActivityBuilder::class);

    expect(\Keepsuit\LaravelTemporal\Facade\Temporal::newLocalActivity())
        ->toBeInstanceOf(\Keepsuit\LaravelTemporal\Builder\LocalActivityBuilder::class);
});
