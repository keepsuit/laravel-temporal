<?php

namespace Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Activities;

class DemoActivityWithInterface implements DemoActivityInterface
{
    public function greet(string $name): string
    {
        return sprintf('Hello %s', $name);
    }
}
