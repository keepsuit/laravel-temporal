<?php

namespace Keepsuit\LaravelTemporal\Support;

use Composer\ClassMapGenerator\ClassMapGenerator;
use Illuminate\Support\Collection;
use Temporal\Activity\ActivityInterface;

class DiscoverActivities
{
    /**
     * Get all the activities by searching the given activities directory.
     */
    public static function within(string $activitiesPath): array
    {
        if (! is_dir($activitiesPath)) {
            return [];
        }

        /** @var Collection<class-string,class-string|null> $activities */
        $activities = Collection::make();

        $generator = new ClassMapGenerator();
        $generator->scanPaths($activitiesPath);

        foreach (array_keys($generator->getClassMap()->getMap()) as $class) {
            $activity = new \ReflectionClass($class);

            /** @var \ReflectionClass[] $interfaces */
            $interfaces = array_merge(
                $activity->getInterfaces(),
                [$activity->getName() => $activity],
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

        return $activities->map(fn ($value, $key) => $value ?? $key)->sort()->values()->all();
    }
}
