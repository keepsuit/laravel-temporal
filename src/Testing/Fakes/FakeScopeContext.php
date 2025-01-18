<?php

namespace Keepsuit\LaravelTemporal\Testing\Fakes;

use Temporal\Workflow\CancellationScopeInterface;
use Temporal\Workflow\ScopedContextInterface;

class FakeScopeContext extends FakeWorkflowContext implements ScopedContextInterface
{
    public function __construct(ScopedContextInterface $context)
    {
        parent::__construct($context);
    }

    public function async(callable $handler): CancellationScopeInterface
    {
        // @phpstan-ignore-next-line
        return $this->context->async($handler);
    }

    public function asyncDetached(callable $handler): CancellationScopeInterface
    {
        // @phpstan-ignore-next-line
        return $this->context->asyncDetached($handler);
    }

    public function registerUpdate(string $name, callable $handler, ?callable $validator): static
    {
        $this->context->registerUpdate($name, $handler, $validator);

        return $this;
    }

    public function allHandlersFinished(): bool
    {
        return $this->context->allHandlersFinished();
    }
}
