<?php

namespace Keepsuit\LaravelTemporal\PHPStan;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Type;
use Temporal\Client\WorkflowClientInterface;
use Temporal\Internal\Client\WorkflowProxy;

class TemporalWorkflowClientInterfaceExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return WorkflowClientInterface::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return match ($methodReflection->getName()) {
            'newWorkflowStub', 'newRunningWorkflowStub' => true,
            default => false,
        };
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $className = $methodCall->getArgs()[0]->value;
        $classNameType = $scope->getType($className);

        return new GenericObjectType(WorkflowProxy::class, [$classNameType->getClassStringObjectType()]);
    }
}
