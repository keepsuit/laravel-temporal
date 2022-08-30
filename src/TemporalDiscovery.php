<?php

namespace Keepsuit\LaravelTemporal;

use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Keepsuit\LaravelTemporal\Support\DiscoverActivities;
use Keepsuit\LaravelTemporal\Support\DiscoverWorkflows;

class TemporalDiscovery
{
    public function __construct(protected readonly Application $app)
    {
    }

    /**
     * @return Collection<array-key,class-string>
     */
    protected function discoverWorkflows(): Collection
    {
        return Collection::make([
            $this->app->path('Workflows'),
        ])
            ->reject(fn ($directory) => ! is_dir($directory))
            ->reduce(fn (Collection $discovered, string $directory) => $discovered->mergeRecursive(
                DiscoverWorkflows::within($directory, base_path())
            ), Collection::make());
    }

    /**
     * @return Collection<array-key,class-string>
     */
    protected function discoverActivities(): Collection
    {
        return Collection::make([
            $this->app->path('Workflows'),
            $this->app->path('Activities'),
        ])
            ->reject(fn ($directory) => ! is_dir($directory))
            ->reduce(fn (Collection $discovered, string $directory) => $discovered->mergeRecursive(
                DiscoverActivities::within($directory, base_path())
            ), Collection::make());
    }

    /**
     * @return Collection<array-key,class-string>
     */
    public function getWorkflows(): Collection
    {
        return $this->discoverWorkflows()
            ->mergeRecursive($this->app->make('config')->get('temporal.workflows', []));
    }

    /**
     * @return Collection<array-key,class-string>
     */
    public function getActivities(): Collection
    {
        return $this->discoverActivities()
            ->mergeRecursive($this->app->make('config')->get('temporal.activities', []));
    }
}
