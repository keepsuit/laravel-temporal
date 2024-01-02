<?php

use Keepsuit\LaravelTemporal\Support\DiscoverActivities;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Activities\DemoActivity;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Activities\DemoActivityInterface;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Activities\DemoActivityInterfaceOnly;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Activities\DemoActivityWithInterface;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Activities\DemoLocalActivity;

it('can discovery activities', function () {
    class_alias(DemoActivityWithInterface::class, 'Tests\Fixtures\WorkflowDiscovery\Activities\DemoActivity');
    class_alias(DemoActivityInterface::class, 'Tests\Fixtures\WorkflowDiscovery\Activities\DemoActivityInterface');
    class_alias(DemoActivityInterfaceOnly::class, 'Tests\Fixtures\WorkflowDiscovery\Activities\DemoActivityInterfaceOnly');
    class_alias(DemoActivity::class, 'Tests\Fixtures\WorkflowDiscovery\Activities\DemoActivityWithoutInterface');

    $activities = DiscoverActivities::within(
        __DIR__.'/../../Fixtures/WorkflowDiscovery/Activities',
    );

    expect($activities)->toBe([
        DemoActivity::class,
        DemoActivityInterfaceOnly::class,
        DemoActivityWithInterface::class,
        DemoLocalActivity::class,
    ]);
});
