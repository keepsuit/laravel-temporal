<?php

namespace Keepsuit\LaravelTemporal\PHPStan;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Type;
use Temporal\Internal\Workflow\ActivityProxy;
use Temporal\Internal\Workflow\ChildWorkflowProxy;
use Temporal\Workflow\WorkflowContextInterface;

class TemporalWorkflowContextInterfaceExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return WorkflowContextInterface::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return match ($methodReflection->getName()) {
            'newActivityStub', 'newChildWorkflowStub' => true,
            default => false,
        };
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $className = $methodCall->getArgs()[0]->value;
        $classNameType = $scope->getType($className);

        $isClassString = method_exists($classNameType, 'isClassStringType')
            ? $classNameType->isClassStringType()->yes()
            : $classNameType->isClassString()->yes();

        if ($isClassString) {
            return match ($methodReflection->getName()) {
                'newActivityStub' => new GenericObjectType(ActivityProxy::class, [$classNameType->getClassStringObjectType()]),
                'newChildWorkflowStub' => new GenericObjectType(ChildWorkflowProxy::class, [$classNameType->getClassStringObjectType()]),
                default => null,
            };
        }

        return null;
    }
}
