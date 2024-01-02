<?php

namespace Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Activities;

use Temporal\Activity\ActivityMethod;
use Temporal\Activity\LocalActivityInterface;

#[LocalActivityInterface(prefix: 'demo_local')]
class DemoLocalActivity
{
    #[ActivityMethod(name: 'greet')]
    public function greet(string $name): string
    {
        return sprintf('(local) Hello %s', $name);
    }
}
