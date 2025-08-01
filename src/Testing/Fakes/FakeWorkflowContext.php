<?php

namespace Keepsuit\LaravelTemporal\Testing\Fakes;

use DateTimeInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use React\Promise\PromiseInterface;
use Temporal\Activity\ActivityOptionsInterface;
use Temporal\Common\SearchAttributes\SearchAttributeUpdate;
use Temporal\DataConverter\Type;
use Temporal\DataConverter\ValuesInterface;
use Temporal\Internal\Workflow\ActivityProxy;
use Temporal\Internal\Workflow\ChildWorkflowProxy;
use Temporal\Worker\Transport\Command\RequestInterface;
use Temporal\Workflow\ActivityStubInterface;
use Temporal\Workflow\ChildWorkflowOptions;
use Temporal\Workflow\ChildWorkflowStubInterface;
use Temporal\Workflow\ContinueAsNewOptions;
use Temporal\Workflow\ExternalWorkflowStubInterface;
use Temporal\Workflow\TimerOptions;
use Temporal\Workflow\WorkflowContextInterface;
use Temporal\Workflow\WorkflowExecution;
use Temporal\Workflow\WorkflowInfo;

class FakeWorkflowContext implements WorkflowContextInterface
{
    public function __construct(protected WorkflowContextInterface $context) {}

    public function now(): DateTimeInterface
    {
        return $this->context->now();
    }

    public function isReplaying(): bool
    {
        return $this->context->isReplaying();
    }

    public function getInfo(): WorkflowInfo
    {
        return $this->context->getInfo();
    }

    public function getInput(): ValuesInterface
    {
        return $this->context->getInput();
    }

    public function getLastCompletionResult($type = null): mixed
    {
        return $this->context->getLastCompletionResult($type);
    }

    public function registerQuery(string $queryType, callable $handler, string $description): WorkflowContextInterface
    {
        return $this->context->registerQuery($queryType, $handler, $description);
    }

    public function registerSignal(string $queryType, callable $handler, string $description): WorkflowContextInterface
    {
        return $this->context->registerSignal($queryType, $handler, $description);
    }

    public function request(RequestInterface $request, bool $cancellable = true, bool $waitResponse = true): PromiseInterface
    {
        return $this->context->request($request, $cancellable, $waitResponse);
    }

    public function getVersion(string $changeId, int $minSupported, int $maxSupported): PromiseInterface
    {
        return $this->context->getVersion($changeId, $minSupported, $maxSupported);
    }

    public function sideEffect(callable $context): PromiseInterface
    {
        return $this->context->sideEffect($context);
    }

    public function complete(?array $result = null, ?\Throwable $failure = null): PromiseInterface
    {
        return $this->context->complete($result, $failure);
    }

    public function panic(?\Throwable $failure = null): PromiseInterface
    {
        return $this->context->panic($failure);
    }

    public function timer($interval, ?TimerOptions $options = null): PromiseInterface
    {
        return $this->context->timer($interval, $options);
    }

    public function continueAsNew(string $type, array $args = [], ?ContinueAsNewOptions $options = null): PromiseInterface
    {
        return $this->context->continueAsNew($type, $args, $options);
    }

    public function newContinueAsNewStub(string $class, ?ContinueAsNewOptions $options = null): object
    {
        return $this->context->newContinueAsNewStub($class, $options);
    }

    public function executeChildWorkflow(string $type, array $args = [], ?ChildWorkflowOptions $options = null, $returnType = null): PromiseInterface
    {
        return $this->context->executeChildWorkflow($type, $args, $options, $returnType);
    }

    public function newChildWorkflowStub(string $class, ?ChildWorkflowOptions $options = null): object
    {
        /** @var ChildWorkflowProxy $workflowProxy */
        $workflowProxy = $this->context->newChildWorkflowStub($class, $options);

        $reflection = new \ReflectionClass($workflowProxy);
        $properties = collect($reflection->getProperties())
            ->each(fn (\ReflectionProperty $property) => $property->setAccessible(true))
            ->mapWithKeys(fn (\ReflectionProperty $property) => [$property->getName() => $property->getValue($workflowProxy)]);

        return new ChildWorkflowProxy(
            $properties->get('class'),
            $properties->get('workflow'),
            $properties->get('options'),
            $this
        );
    }

    public function newUntypedChildWorkflowStub(string $type, ?ChildWorkflowOptions $options = null): ChildWorkflowStubInterface
    {
        return new FakeChildWorkflowStub($this->context->newUntypedChildWorkflowStub($type, $options));
    }

    public function newExternalWorkflowStub(string $class, WorkflowExecution $execution): object
    {
        return $this->context->newExternalWorkflowStub($class, $execution);
    }

    public function newUntypedExternalWorkflowStub(WorkflowExecution $execution): ExternalWorkflowStubInterface
    {
        return $this->context->newUntypedExternalWorkflowStub($execution);
    }

    public function executeActivity(
        string $type,
        array $args = [],
        ?ActivityOptionsInterface $options = null,
        Type|string|\ReflectionClass|\ReflectionType|null $returnType = null
    ): PromiseInterface {
        return $this->context->executeActivity($type, $args, $options, $returnType);
    }

    public function newActivityStub(string $class, ?ActivityOptionsInterface $options = null): object
    {
        /** @var ActivityProxy $activityProxy */
        $activityProxy = $this->context->newActivityStub($class, $options);

        $reflection = new \ReflectionClass($activityProxy);
        $properties = collect($reflection->getProperties())
            ->each(fn (\ReflectionProperty $property) => $property->setAccessible(true))
            ->mapWithKeys(fn (\ReflectionProperty $property) => [$property->getName() => $property->getValue($activityProxy)]);

        return new ActivityProxy(
            $properties->get('class'),
            $properties->get('activities'),
            $properties->get('options'),
            $this,
            $properties->get('callsInterceptor')
        );
    }

    public function newUntypedActivityStub(?ActivityOptionsInterface $options = null): ActivityStubInterface
    {
        return new FakeActivityStub($this->context->newUntypedActivityStub($options));
    }

    public function await(...$conditions): PromiseInterface
    {
        return $this->context->await(...$conditions);
    }

    public function awaitWithTimeout($interval, ...$conditions): PromiseInterface
    {
        return $this->context->awaitWithTimeout($interval, ...$conditions);
    }

    public function getStackTrace(): string
    {
        return $this->context->getStackTrace();
    }

    public function upsertSearchAttributes(array $searchAttributes): void
    {
        $this->context->upsertSearchAttributes($searchAttributes);
    }

    public function uuid(): PromiseInterface
    {
        return $this->sideEffect(static fn (): UuidInterface => Uuid::uuid4());
    }

    public function uuid4(): PromiseInterface
    {
        return $this->sideEffect(static fn (): UuidInterface => Uuid::uuid4());
    }

    public function uuid7(?DateTimeInterface $dateTime = null): PromiseInterface
    {
        return $this->sideEffect(static fn (): UuidInterface => Uuid::uuid7($dateTime));
    }

    public function registerUpdate(string $name, callable $handler, ?callable $validator, string $description): static
    {
        $this->context->registerUpdate($name, $handler, $validator, $description);

        return $this;
    }

    public function allHandlersFinished(): bool
    {
        return $this->context->allHandlersFinished();
    }

    public function upsertMemo(array $values): void
    {
        $this->context->upsertMemo($values);
    }

    public function upsertTypedSearchAttributes(SearchAttributeUpdate ...$updates): void
    {
        $this->context->upsertTypedSearchAttributes(...$updates);
    }

    public function registerDynamicSignal(callable $handler): WorkflowContextInterface
    {
        $this->context->registerDynamicSignal($handler);

        return $this;
    }

    public function registerDynamicQuery(callable $handler): WorkflowContextInterface
    {
        $this->context->registerDynamicQuery($handler);

        return $this;
    }

    public function registerDynamicUpdate(callable $handler, ?callable $validator = null): WorkflowContextInterface
    {
        $this->context->registerDynamicUpdate($handler);

        return $this;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->context->getLogger();
    }
}
