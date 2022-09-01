<?php

namespace Keepsuit\LaravelTemporal\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;
use Temporal\Workflow\WorkflowInterface;

class DiscoverWorkflows
{
    /**
     * Get all of the workflows by searching the given workflow directory.
     */
    public static function within(string $workflowPath, string $basePath): array
    {
        $workflows = Collection::make([]);

        $files = (new Finder)->files()->in($workflowPath);

        foreach ($files as $file) {
            try {
                $workflow = new \ReflectionClass(
                    static::classFromFile($file, $basePath)
                );
            } catch (\ReflectionException) {
                continue;
            }

            /** @var \ReflectionClass[] $interfaces */
            $interfaces = array_merge(
                $workflow->getInterfaces(),
                $workflow->isInterface() ? [$workflow->getName() => $workflow] : []
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

        return $workflows->map(fn($value, $key) => $value === null ? $key : $value)->values()->all();
    }

    /**
     * Extract the class name from the given file path.
     */
    protected static function classFromFile(\SplFileInfo $file, string $basePath): string
    {
        $class = trim(Str::replaceFirst($basePath, '', $file->getRealPath()), DIRECTORY_SEPARATOR);

        return str_replace(
            [DIRECTORY_SEPARATOR, ucfirst(basename(app()->path())).'\\'],
            ['\\', app()->getNamespace()],
            ucfirst(Str::replaceLast('.php', '', $class))
        );
    }
}
