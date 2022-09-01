<?php

namespace Keepsuit\LaravelTemporal\Facade;

use Illuminate\Support\Facades\Facade;
use Keepsuit\LaravelTemporal\Builder\ActivityBuilder;
use Keepsuit\LaravelTemporal\Builder\LocalActivityBuilder;
use Keepsuit\LaravelTemporal\Builder\WorkflowBuilder;
use Keepsuit\LaravelTemporal\Testing\ActivityMocker;

/**
 * @method static ActivityBuilder newActivity()
 * @method static LocalActivityBuilder newLocalActivity()
 * @method static WorkflowBuilder newWorkflow()
 */
class Temporal extends Facade
{
    public static function fakeActivities(array $fakedActivities): void
    {
        static::getActivityMocker()->clear();

        foreach ($fakedActivities as $activityName => $activityResult) {
            if ($activityResult instanceof \Closure || is_callable($activityResult)) {
                try {
                    static::getActivityMocker()->expectCompletion($activityName, $activityResult());
                } catch (\Exception $exception) {
                    static::getActivityMocker()->expectFailure($activityName, $exception);
                }

                continue;
            }

            if ($activityResult instanceof \Throwable) {
                static::getActivityMocker()->expectFailure($activityName, $activityResult);

                continue;
            }

            static::getActivityMocker()->expectCompletion($activityName, $activityResult);
        }
    }

    protected static function getActivityMocker(): ActivityMocker
    {
        return static::$app->make(ActivityMocker::class);
    }

    protected static function getFacadeAccessor(): string
    {
        return \Keepsuit\LaravelTemporal\Temporal::class;
    }
}
