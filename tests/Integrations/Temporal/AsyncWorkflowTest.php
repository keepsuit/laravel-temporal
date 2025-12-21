<?php

use Carbon\CarbonInterval;
use Keepsuit\LaravelTemporal\Facade\Temporal;
use Keepsuit\LaravelTemporal\Testing\TemporalTestTime;
use Keepsuit\LaravelTemporal\Testing\WithoutTimeSkipping;
use Keepsuit\LaravelTemporal\Testing\WithTemporal;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows\AsyncWorkflow;
use Temporal\Common\RetryOptions;

uses(WithTemporal::class);
uses(WithoutTimeSkipping::class);

test('time skipping', function () {
    $workflow = Temporal::newWorkflow()
        ->withWorkflowRunTimeout(CarbonInterval::seconds(5))
        ->withRetryOptions(RetryOptions::new()->withMaximumAttempts(2))
        ->build(AsyncWorkflow::class);

    $run = Temporal::workflowClient()->start($workflow);

    TemporalTestTime::sleep(3);

    expect($run->getResult())->toBe('ok');
})->skip(fn () => ! config('temporal.testing.time_skipping'));
