<?php

namespace Keepsuit\LaravelTemporal\Support;

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
        $activities = [];

        $files = (new Finder)->files()->in($activitiesPath);

        foreach ($files as $file) {
            try {
                $activity = new \ReflectionClass(
                    static::classFromFile($file, $basePath)
                );
            } catch (\ReflectionException) {
                continue;
            }

            if (! $activity->isInstantiable()) {
                continue;
            }

            foreach ($activity->getInterfaces() as $interface) {
                foreach ($interface->getAttributes() as $attribute) {
                    if ($attribute->newInstance() instanceof ActivityInterface) {
                        $activities[] = $activity->getName();
                        break 2;
                    }
                }
            }
        }

        return $activities;
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
