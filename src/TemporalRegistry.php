<?php

namespace Keepsuit\LaravelTemporal;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements Arrayable<array-key,array<array-key,class-string>>
 */
class TemporalRegistry implements Arrayable
{
    /**
     * @var array<array-key,class-string>
     */
    protected array $registeredWorkflows = [];

    /**
     * @var array<array-key,class-string>
     */
    protected array $registeredActivities = [];

    /**
     * @param  class-string  ...$workflowClasses
     */
    public function registerWorkflows(string ...$workflowClasses): TemporalRegistry
    {
        array_push($this->registeredWorkflows, ...$workflowClasses);

        return $this;
    }

    /**
     * @param  class-string  ...$activityClasses
     */
    public function registerActivities(string ...$activityClasses): TemporalRegistry
    {
        array_push($this->registeredActivities, ...$activityClasses);

        return $this;
    }

    /**
     * @return array<array-key,class-string>
     */
    public function workflows(): array
    {
        return array_unique($this->registeredWorkflows);
    }

    /**
     * @return array<array-key,class-string>
     */
    public function activities(): array
    {
        return array_unique($this->registeredActivities);
    }

    /**
     * @return array{
     *     workflows:array<array-key,class-string>,
     *     activities:array<array-key,class-string>
     * }
     */
    public function toArray(): array
    {
        return [
            'workflows' => $this->workflows(),
            'activities' => $this->activities(),
        ];
    }
}
