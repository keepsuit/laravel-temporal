<?php

namespace Keepsuit\LaravelTemporal\PHPStan;

use PHPStan\Analyser\OutOfClassScope;
use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariant;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\TrinaryLogic;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use PHPStan\Type\VoidType;
use Temporal\Internal\Client\WorkflowProxy;
use Temporal\Workflow\ReturnType;
use Temporal\Workflow\WorkflowMethod;

class TemporalWorkflowProxyExtension implements MethodsClassReflectionExtension
{
    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        if ($classReflection->getName() !== WorkflowProxy::class) {
            return false;
        }

        $activeTemplateTypeMap = $classReflection->getActiveTemplateTypeMap();

        if ($activeTemplateTypeMap->count() !== 1) {
            return false;
        }

        $objectType = $activeTemplateTypeMap->getType('T');

        if ($objectType->isObject()->no()) {
            return false;
        }

        if (! $objectType->hasMethod($methodName)->yes()) {
            return false;
        }

        $methodReflection = $objectType->getMethod($methodName, new OutOfClassScope);

        if (! $methodReflection->isPublic()) {
            return false;
        }

        // @phpstan-ignore-next-line
        $objectClassReflection = new \ReflectionClass($objectType->getClassName());
        $objectMethodReflection = $objectClassReflection->getMethod($methodName);
        $objectMethodAttributes = $objectMethodReflection->getAttributes(WorkflowMethod::class);

        return $objectMethodAttributes !== [];
    }

    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        $activeTemplateTypeMap = $classReflection->getActiveTemplateTypeMap();

        $objectType = $activeTemplateTypeMap->getType('T');
        assert($objectType->isObject()->yes());

        $methodReflection = $objectType->getMethod($methodName, new OutOfClassScope);

        // @phpstan-ignore-next-line
        $objectClassReflection = new \ReflectionClass($objectType->getClassName());
        $objectMethodReflection = $objectClassReflection->getMethod($methodName);
        $objectMethodAttributes = $objectMethodReflection->getAttributes(ReturnType::class);
        $objectMethodReturnType = $objectMethodAttributes === [] ? \Temporal\DataConverter\Type::TYPE_VOID : $objectMethodAttributes[0]->getArguments()[0];
        assert(is_string($objectMethodReturnType));
        $objectMethodReturnTypeNullable = $objectMethodAttributes === [] ? false : ($objectMethodAttributes[0]->getArguments()[1] ?? false);
        assert(is_bool($objectMethodReturnTypeNullable));

        $returnType = match (true) {
            $objectMethodReturnType === \Temporal\DataConverter\Type::TYPE_VOID => new VoidType,
            $objectMethodReturnType === \Temporal\DataConverter\Type::TYPE_BOOL => new BooleanType,
            $objectMethodReturnType === \Temporal\DataConverter\Type::TYPE_STRING => new StringType,
            $objectMethodReturnType === \Temporal\DataConverter\Type::TYPE_INT => new IntegerType,
            $objectMethodReturnType === \Temporal\DataConverter\Type::TYPE_FLOAT => new FloatType,
            class_exists($objectMethodReturnType) => new ObjectType($objectMethodReturnType),
            default => new MixedType,
        };

        $returnType = $objectMethodReturnTypeNullable
            ? new UnionType([$returnType, new NullType])
            : $returnType;

        return new class($classReflection, $methodName, $methodReflection, $returnType) implements MethodReflection
        {
            public function __construct(
                protected ClassReflection $classReflection,
                protected string $methodName,
                protected MethodReflection $methodReflection,
                protected Type $returnType
            ) {}

            public function isStatic(): bool
            {
                return false;
            }

            public function isPrivate(): bool
            {
                return false;
            }

            public function isPublic(): bool
            {
                return true;
            }

            public function getDocComment(): ?string
            {
                return null;
            }

            public function getName(): string
            {
                return $this->methodName;
            }

            public function getPrototype(): ClassMemberReflection
            {
                return $this;
            }

            public function getVariants(): array
            {
                $parameterAcceptor = ParametersAcceptorSelector::selectSingle($this->methodReflection->getVariants());

                return [
                    new FunctionVariant(
                        $parameterAcceptor->getTemplateTypeMap(),
                        $parameterAcceptor->getResolvedTemplateTypeMap(),
                        $parameterAcceptor->getParameters(),
                        $parameterAcceptor->isVariadic(),
                        $this->returnType
                    ),
                ];
            }

            public function isDeprecated(): TrinaryLogic
            {
                return TrinaryLogic::createNo();
            }

            public function getDeprecatedDescription(): ?string
            {
                return null;
            }

            public function isFinal(): TrinaryLogic
            {
                return TrinaryLogic::createNo();
            }

            public function isInternal(): TrinaryLogic
            {
                return TrinaryLogic::createNo();
            }

            public function getThrowType(): ?Type
            {
                return null;
            }

            public function hasSideEffects(): TrinaryLogic
            {
                return TrinaryLogic::createMaybe();
            }

            public function getDeclaringClass(): ClassReflection
            {
                return $this->classReflection;
            }
        };
    }
}
