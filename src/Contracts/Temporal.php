<?php

declare(strict_types=1);

namespace Keepsuit\LaravelTemporal\Contracts;

use Keepsuit\LaravelTemporal\Builder\ActivityBuilder;
use Keepsuit\LaravelTemporal\Builder\ChildWorkflowBuilder;
use Keepsuit\LaravelTemporal\Builder\LocalActivityBuilder;
use Keepsuit\LaravelTemporal\Builder\WorkflowBuilder;

interface Temporal
{
    public function newActivity(): ActivityBuilder;

    public function newLocalActivity(): LocalActivityBuilder;

    public function newWorkflow(): WorkflowBuilder;

    public function newChildWorkflow(): ChildWorkflowBuilder;
}
