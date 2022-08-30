<?php

use Keepsuit\LaravelTemporal\Builder\ActivityBuilder;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Activities\DemoActivityInterface;
use Temporal\Internal\Workflow\ActivityProxy;

it('can build activity with default options', function () {
    config()->set('temporal.queue', 'test-queue');

    $activity = ActivityBuilder::new()
        ->build(DemoActivityInterface::class);

    expect($activity)
        ->toBeInstanceOf(ActivityProxy::class);

    /** @var \Temporal\Activity\ActivityOptions $activityOptions */
    $activityOptions = invade(invade($activity)->stub)->options;

    expect($activityOptions)
        ->taskQueue->toBe('test-queue');
})->skip('Worker context is required');
