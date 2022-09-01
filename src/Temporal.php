<?php

namespace Keepsuit\LaravelTemporal;

use Keepsuit\LaravelTemporal\Builder\ActivityBuilder;
use Keepsuit\LaravelTemporal\Builder\ChildWorkflowBuilder;
use Keepsuit\LaravelTemporal\Builder\LocalActivityBuilder;
use Keepsuit\LaravelTemporal\Builder\WorkflowBuilder;

class Temporal
{
    public function newActivity(): ActivityBuilder
    {
        return ActivityBuilder::new();
    }

    public function newLocalActivity(): LocalActivityBuilder
    {
        return LocalActivityBuilder::new();
    }

    public function newWorkflow(): WorkflowBuilder
    {
        return WorkflowBuilder::new();
    }

    public function newChildWorkflow(): ChildWorkflowBuilder
    {
        return ChildWorkflowBuilder::new();
    }
}
