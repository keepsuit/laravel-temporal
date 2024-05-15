<?php

namespace Keepsuit\LaravelTemporal;

use Closure;
use Keepsuit\LaravelTemporal\Builder\ActivityBuilder;
use Keepsuit\LaravelTemporal\Builder\ChildWorkflowBuilder;
use Keepsuit\LaravelTemporal\Builder\LocalActivityBuilder;
use Keepsuit\LaravelTemporal\Builder\WorkflowBuilder;
use Temporal\Worker\WorkerOptions;

class Temporal implements Contracts\Temporal
{
    /**
     * @var Closure(string):(WorkerOptions|null)|null
     */
    protected static ?Closure $buildWorkerOptionsCallback = null;

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

    public function buildWorkerOptions(string $taskQueue): WorkerOptions
    {
        if (static::$buildWorkerOptionsCallback) {
            $workerOptions = call_user_func(static::$buildWorkerOptionsCallback, $taskQueue);

            if ($workerOptions instanceof WorkerOptions) {
                return $workerOptions;
            }
        }

        return WorkerOptions::new();
    }

    /**
     * @param  Closure(string):(WorkerOptions|null)  $callback
     */
    public function buildWorkerOptionsUsing(Closure $callback): void
    {
        static::$buildWorkerOptionsCallback = $callback;
    }
}
