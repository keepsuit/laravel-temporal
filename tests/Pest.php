<?php

use Keepsuit\LaravelTemporal\Tests\Integrations\LaravelData\LaravelDataTestCase;
use Keepsuit\LaravelTemporal\Tests\TestCase;
use Spiral\Attributes\AttributeReader;
use Temporal\Activity\ActivityOptionsInterface;
use Temporal\Internal\Declaration\Reader\WorkflowReader;
use Temporal\Internal\Interceptor\Pipeline;
use Temporal\Internal\Marshaller\MarshallerInterface;
use Temporal\Internal\Workflow\ActivityProxy;
use Temporal\Internal\Workflow\ActivityStub;
use Temporal\Internal\Workflow\ChildWorkflowProxy;
use Temporal\Internal\Workflow\ChildWorkflowStub;
use Temporal\Workflow\ChildWorkflowOptions;
use Temporal\Workflow\ScopedContextInterface;

uses(TestCase::class)->in(__DIR__.'/Unit');
uses(TestCase::class)->in(__DIR__.'/Integrations/Temporal');
uses(LaravelDataTestCase::class)->in(__DIR__.'/Integrations/LaravelData');

/**
 * @param  (Closure(ActivityOptionsInterface $activityOptions): bool)|null  $validateActivityOptions
 * @param  (Closure(ChildWorkflowOptions $workflowOptions): bool)|null  $validateWorkflowOptions
 */
function mockTemporalContext(?Closure $validateActivityOptions = null, ?Closure $validateWorkflowOptions = null): ScopedContextInterface
{
    $validateActivityOptions ??= fn (ActivityOptionsInterface $activityOptions) => true;
    $validateWorkflowOptions ??= fn (ChildWorkflowOptions $workflowOptions) => true;

    $contextMock = mock(ScopedContextInterface::class);

    $contextMock->shouldReceive('newActivityStub')
        ->withArgs(fn (string $activity, ActivityOptionsInterface $activityOptions) => $validateActivityOptions($activityOptions))
        ->andReturnUsing(fn (string $activity, ActivityOptionsInterface $activityOptions) => new ActivityProxy($activity, [], $activityOptions, $contextMock, Pipeline::prepare([])));
    $contextMock->shouldReceive('newUntypedActivityStub')
        ->withArgs(fn (ActivityOptionsInterface $activityOptions) => $validateActivityOptions($activityOptions))
        ->andReturnUsing(fn (ActivityOptionsInterface $activityOptions) => new ActivityStub(mock(MarshallerInterface::class), $activityOptions, []));

    $contextMock->shouldReceive('newChildWorkflowStub')
        ->withArgs(fn (string $workflowClass, ChildWorkflowOptions $workflowOptions) => $validateWorkflowOptions($workflowOptions))
        ->andReturnUsing(fn (string $workflowClass, ChildWorkflowOptions $workflowOptions) => new ChildWorkflowProxy($workflowClass, (new WorkflowReader(new AttributeReader))->fromClass($workflowClass), $workflowOptions, $contextMock));
    $contextMock->shouldReceive('newUntypedChildWorkflowStub')
        ->withArgs(fn (string $workflowType, ChildWorkflowOptions $workflowOptions) => $validateWorkflowOptions($workflowOptions))
        ->andReturnUsing(fn (string $workflowType, ChildWorkflowOptions $workflowOptions) => new ChildWorkflowStub(mock(MarshallerInterface::class), $workflowType, $workflowOptions, []));

    return $contextMock;
}
