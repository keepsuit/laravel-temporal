<?php

use Carbon\CarbonInterval;
use Keepsuit\LaravelTemporal\Facade\Temporal;
use Keepsuit\LaravelTemporal\Testing\WithTemporal;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows\TesterWorkflow;
use Temporal\Common\RetryOptions;

uses(WithTemporal::class);

beforeEach(function () {
    Temporal::fake();
});

it('execute workflow with activity', function () {
    $workflow = Temporal::newWorkflow()
        ->withWorkflowRunTimeout(CarbonInterval::seconds(2))
        ->build(TesterWorkflow::class);

    $result = $workflow->tester('activity');

    expect($result)->toBe(['activity' => 'Hello John']);
});

it('execute workflow with local activity', function () {
    $workflow = Temporal::newWorkflow()
        ->withWorkflowRunTimeout(CarbonInterval::seconds(2))
        ->build(TesterWorkflow::class);

    $result = $workflow->tester('local_activity');

    expect($result)->toBe(['local_activity' => '(local) Hello John']);
});

it('execute workflow with child', function () {
    $workflow = Temporal::newWorkflow()
        ->withWorkflowRunTimeout(CarbonInterval::seconds(2))
        ->withRetryOptions(RetryOptions::new()->withMaximumAttempts(2))
        ->build(TesterWorkflow::class);

    $result = $workflow->tester('child');

    expect($result)->toBe(['child' => '(child) Hello John']);
});

it('execute workflow full', function () {
    $workflow = Temporal::newWorkflow()
        ->withWorkflowRunTimeout(CarbonInterval::seconds(2))
        ->withRetryOptions(RetryOptions::new()->withMaximumAttempts(2))
        ->build(TesterWorkflow::class);

    $result = $workflow->tester('all');

    expect($result)->toBe([
        'activity' => 'Hello John',
        'local_activity' => '(local) Hello John',
        'child' => '(child) Hello John',
    ]);
});
