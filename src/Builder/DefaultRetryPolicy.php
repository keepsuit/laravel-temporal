<?php

namespace Keepsuit\LaravelTemporal\Builder;

use Carbon\CarbonInterval;
use Illuminate\Support\Arr;
use Temporal\Common\RetryOptions;

trait DefaultRetryPolicy
{
    protected function getDefaultRetryOptions(array $config): RetryOptions
    {
        $retryOptions = RetryOptions::new();

        if (is_numeric($initialInterval = Arr::get($config, 'initial_interval'))) {
            $retryOptions = $retryOptions->withInitialInterval(CarbonInterval::seconds($initialInterval));
        }

        if (is_numeric($backoffCoefficient = Arr::get($config, 'backoff_coefficient'))) {
            $retryOptions = $retryOptions->withBackoffCoefficient($backoffCoefficient);
        }

        if (is_numeric($maximumInterval = Arr::get($config, 'maximum_interval'))) {
            $retryOptions = $retryOptions->withMaximumInterval(CarbonInterval::seconds($maximumInterval));
        }

        if (is_numeric($maximumAttempts = Arr::get($config, 'maximum_attempts'))) {
            $retryOptions = $retryOptions->withMaximumAttempts($maximumAttempts);
        }

        return $retryOptions;
    }
}