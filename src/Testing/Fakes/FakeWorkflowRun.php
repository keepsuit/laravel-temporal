<?php

namespace Keepsuit\LaravelTemporal\Testing\Fakes;

use Temporal\Client\WorkflowStubInterface;
use Temporal\Workflow\WorkflowExecution;
use Temporal\Workflow\WorkflowRunInterface;

class FakeWorkflowRun implements WorkflowRunInterface
{
    public function __construct(
        protected WorkflowStubInterface $stub,
        protected mixed $returnValue = null
    ) {
    }

    public function getExecution(): WorkflowExecution
    {
        return $this->stub->getExecution();
    }

    /**
     * {@inheritDoc}
     */
    public function getResult($type = null, int $timeout = null)
    {
        return $this->returnValue;
    }
}
