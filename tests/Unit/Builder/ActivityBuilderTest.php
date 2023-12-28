<?php

use Carbon\CarbonInterval;
use Keepsuit\LaravelTemporal\Builder\WorkflowBuilder;
use Keepsuit\LaravelTemporal\Facade\Temporal;
use Keepsuit\LaravelTemporal\Testing\WithTemporal;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows\DebugOptionsWorkflow;

uses(WithTemporal::class);

beforeEach(function () {
    Temporal::fake();
});

it('can build activity with default options', function () {
    $workflow = WorkflowBuilder::new()
        ->withWorkflowExecutionTimeout(CarbonInterval::seconds(1))
        ->build(DebugOptionsWorkflow::class);

    $activityOptions = $workflow->activityOptions();

    expect($activityOptions)
        ->toBeArray()
        ->taskQueue->toBe('test-queue');
});
