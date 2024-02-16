<?php

use Carbon\CarbonInterval;
use Keepsuit\LaravelTemporal\Facade\Temporal;
use Keepsuit\LaravelTemporal\Testing\WithTemporal;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows\InterceptorWorkflow;

uses(WithTemporal::class);

beforeEach(function () {
    Temporal::fake();
});

it('register interceptors in the worker', function () {
    $workflow = Temporal::newWorkflow()
        ->withWorkflowRunTimeout(CarbonInterval::seconds(2))
        ->build(InterceptorWorkflow::class);

    $result = $workflow->interceptors();

    expect($result)->toBe(['inbound_calls' => true]);
});
