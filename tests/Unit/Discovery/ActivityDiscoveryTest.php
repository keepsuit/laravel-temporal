<?php

use Keepsuit\LaravelTemporal\Support\DiscoverActivities;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Activities\DemoActivity;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Activities\DemoActivityInterface;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Activities\DemoActivityInterfaceOnly;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Activities\DemoActivityWithoutInterface;

it('can discovery activities', function () {
    class_alias(DemoActivity::class, 'Tests\Fixtures\WorkflowDiscovery\Activities\DemoActivity');
    class_alias(DemoActivityInterface::class, 'Tests\Fixtures\WorkflowDiscovery\Activities\DemoActivityInterface');
    class_alias(DemoActivityInterfaceOnly::class, 'Tests\Fixtures\WorkflowDiscovery\Activities\DemoActivityInterfaceOnly');
    class_alias(DemoActivityWithoutInterface::class, 'Tests\Fixtures\WorkflowDiscovery\Activities\DemoActivityWithoutInterface');

    $activities = DiscoverActivities::within(
        __DIR__.'/../../Fixtures/WorkflowDiscovery/Activities',
        getcwd()
    );

    expect($activities)->toBe([
        DemoActivity::class,
        DemoActivityInterfaceOnly::class,
        DemoActivityWithoutInterface::class,
    ]);
});
