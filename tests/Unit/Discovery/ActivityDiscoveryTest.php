<?php

use Keepsuit\LaravelTemporal\Support\DiscoverActivities;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Activities\DemoActivity;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Activities\DemoActivityInterface;

it('can discovery activities', function () {
    class_alias(DemoActivity::class, 'Tests\Fixtures\WorkflowDiscovery\Activities\DemoActivity');
    class_alias(DemoActivityInterface::class, 'Tests\Fixtures\WorkflowDiscovery\Activities\DemoActivityInterface');

    $activities = DiscoverActivities::within(
        __DIR__.'/../../Fixtures/WorkflowDiscovery/Activities',
        getcwd()
    );

    expect($activities)->toBe([
        DemoActivity::class,
    ]);
});
