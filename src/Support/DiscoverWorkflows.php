<?php

namespace Keepsuit\LaravelTemporal\Support;

use Composer\ClassMapGenerator\ClassMapGenerator;
use Illuminate\Support\Collection;
use Temporal\Workflow\WorkflowInterface;

class DiscoverWorkflows
{
    /**
     * Get all the workflows by searching the given workflow directory.
     *
     * @return class-string[]
     */
    public static function within(string $workflowPath): array
    {
        if (! is_dir($workflowPath)) {
            return [];
        }

        /** @var Collection<class-string,class-string|null> $workflows */
        $workflows = Collection::make();

        $generator = new ClassMapGenerator;
        $generator->scanPaths($workflowPath);

        foreach (array_keys($generator->getClassMap()->getMap()) as $class) {
            $workflow = new \ReflectionClass($class);

            /** @var \ReflectionClass<object>[] $interfaces */
            $interfaces = array_merge(
                $workflow->getInterfaces(),
                [$workflow->getName() => $workflow]
            );

            foreach ($interfaces as $interface) {
                if ($interface->getAttributes(WorkflowInterface::class) !== []) {
                    if (! $workflow->isInterface() || ! $workflows->has($interface->getName())) {
                        $workflows->put($interface->getName(), $workflow->isInterface() ? null : $workflow->getName());
                    }

                    break;
                }
            }
        }

        return $workflows->map(fn ($value, $key) => $value ?? $key)->sort()->values()->all();
    }
}
