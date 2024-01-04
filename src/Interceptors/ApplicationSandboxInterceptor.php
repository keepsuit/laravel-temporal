<?php

namespace Keepsuit\LaravelTemporal\Interceptors;

use Keepsuit\LaravelTemporal\Support\CurrentApplication;
use Temporal\Interceptor\ActivityInbound\ActivityInput;
use Temporal\Interceptor\ActivityInboundInterceptor;
use Temporal\Interceptor\Trait\ActivityInboundInterceptorTrait;
use Temporal\Interceptor\Trait\WorkflowInboundCallsInterceptorTrait;
use Temporal\Interceptor\WorkflowInbound\WorkflowInput;
use Temporal\Interceptor\WorkflowInboundCallsInterceptor;
use Throwable;

class ApplicationSandboxInterceptor implements ActivityInboundInterceptor, WorkflowInboundCallsInterceptor
{
    use ActivityInboundInterceptorTrait;
    use WorkflowInboundCallsInterceptorTrait;

    public function handleActivityInbound(ActivityInput $input, callable $next): mixed
    {
        return $this->withFreshApplication(fn () => $next($input));
    }

    public function execute(WorkflowInput $input, callable $next): void
    {
        $this->withFreshApplication(fn () => $next($input));
    }

    protected function withFreshApplication(\Closure $closure): mixed
    {
        $sandbox = CurrentApplication::createSandbox();

        try {
            $response = $closure();

            $sandbox->terminate();
        } catch (Throwable $throwable) {
            report($throwable);

            $sandbox->terminate();

            throw $throwable;
        } finally {
            $sandbox->flush();

            unset($sandbox);

            CurrentApplication::reset();
        }

        return $response;
    }
}
