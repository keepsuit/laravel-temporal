<?php

namespace Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Activities;

use Temporal\Activity\ActivityInterface;
use Temporal\Activity\ActivityMethod;

#[ActivityInterface(prefix: 'demo')]
class DemoActivity
{
    #[ActivityMethod(name: 'greet')]
    public function greet(string $name): string
    {
        return sprintf('Hello %s', $name);
    }
}
