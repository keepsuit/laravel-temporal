<?php

namespace Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Activities;

use Temporal\Activity\ActivityMethod;
use Temporal\Activity\LocalActivityInterface;

#[LocalActivityInterface(prefix: 'demo')]
interface DemoActivityInterface
{
    #[ActivityMethod(name: 'greet')]
    public function greet(string $name): string;
}
