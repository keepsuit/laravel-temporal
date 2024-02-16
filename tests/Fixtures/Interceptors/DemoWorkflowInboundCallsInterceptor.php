<?php

namespace Keepsuit\LaravelTemporal\Tests\Fixtures\Interceptors;

use Temporal\Interceptor\Trait\WorkflowInboundCallsInterceptorTrait;
use Temporal\Interceptor\WorkflowInbound\WorkflowInput;
use Temporal\Interceptor\WorkflowInboundCallsInterceptor;

class DemoWorkflowInboundCallsInterceptor implements WorkflowInboundCallsInterceptor
{
    use WorkflowInboundCallsInterceptorTrait;

    public function execute(WorkflowInput $input, callable $next): void
    {
        InterceptorSingleton::getInstance()->set('inbound_calls', true);

        $next($input);
    }
}
