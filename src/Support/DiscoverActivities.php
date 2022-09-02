<?php

namespace Keepsuit\LaravelTemporal\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;
use Temporal\Activity\ActivityInterface;

class DiscoverActivities
{
    /**
     * Get all of the workflows by searching the given workflow directory.
     */
    public static function within(string $activitiesPath, string $basePath): array
    {
        /**
         * @var Collection<class-string,class-string|null>
         */
        $activities = Collection::make([]);

        $files = (new Finder)->files()->in($activitiesPath);

        foreach ($files as $file) {
            try {
                $activity = new \ReflectionClass(
                    static::classFromFile($file, $basePath)
                );
            } catch (\ReflectionException) {
                continue;
            }

            /** @var \ReflectionClass[] $interfaces */
            $interfaces = array_merge(
                $activity->getInterfaces(),
                $activity->isInterface() ? [$activity->getName() => $activity] : []
            );

            foreach ($interfaces as $interface) {
                foreach ($interface->getAttributes() as $attribute) {
                    if ($attribute->newInstance() instanceof ActivityInterface) {
                        if (! $activity->isInterface() || ! $activities->has($interface->getName())) {
                            $activities->put($interface->getName(), $activity->isInterface() ? null : $activity->getName());
                        }
                        break 2;
                    }
                }
            }
        }

        return $activities->map(fn ($value, $key) => $value === null ? $key : $value)->values()->all();
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
