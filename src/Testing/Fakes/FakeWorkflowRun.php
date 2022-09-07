<?php

namespace Keepsuit\LaravelTemporal\Testing\Fakes;

use Temporal\Client\WorkflowStubInterface;
use Temporal\Workflow\WorkflowExecution;
use Temporal\Workflow\WorkflowRunInterface;

class FakeWorkflowRun implements WorkflowRunInterface
{
    /**
     * @param  WorkflowStubInterface  $stub
     * @param  mixed  $returnValue
     */
    public function __construct(protected WorkflowStubInterface $stub, protected $returnValue = null)
    {
    }

    /**
     * @return WorkflowExecution
     */
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
